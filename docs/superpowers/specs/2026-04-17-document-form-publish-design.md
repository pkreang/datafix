# Document Form Publish — Design

- **Status:** Draft
- **Date:** 2026-04-17
- **Author:** pkreang (with Claude brainstorming)

## Context

`DocumentForm` builder ที่ `/settings/document-forms` ปัจจุบันทำงานแบบ **auto-sync**: ทุกครั้งที่กด Save, controller เรียก `FormSchemaService::createTable()` หรือ `syncTable()` ทันที — สร้าง/แก้ `ALTER TABLE` บน dedicated submission table โดยไม่มีขั้น review

พฤติกรรมนี้มีปัญหา:

1. **Accidental schema change** — แก้ field ทีไรตารางเปลี่ยนทันที ไม่มีจังหวะยืนยัน
2. **สถานะไม่ชัด** — แยก "ฟอร์มที่กำลังออกแบบ" ออกจาก "ฟอร์มที่ใช้งานจริง" ไม่ได้ (`is_active` เป็น visibility toggle, ไม่ใช่ lifecycle state)
3. **ไม่มี audit trail** — ไม่รู้ว่าใคร publish ตอนไหน ด้วย diff อะไร
4. **Data safety** — `syncTable()` drop column ได้โดยไม่เตือน → ข้อมูลใน submissions หาย
5. **Bug ที่มีอยู่แล้ว** — `FormSchemaService::syncTable()` ใช้ `getTableName($form->form_key)` (คืน `fdata_*`) แทน `$form->submission_table` → ฟอร์มที่ตั้ง custom table ชื่อ (เช่น `nteq_maintenance`) sync ไม่ทำงาน

## Goals

- แยก lifecycle ของฟอร์มเป็น **Draft / Published** อย่างชัดเจน
- ย้ายการสร้าง/แก้ schema ไปเป็น **explicit action** ("Publish") แทน auto-sync
- แสดง **diff preview + DDL** ก่อน apply เพื่อให้ admin ยืนยัน
- **ป้องกันการทำลายข้อมูล** — drop/rename column ที่ยังมีข้อมูลถูก block
- เก็บ **audit log** ของทุก publish/unpublish action
- รองรับ **preview submit** ระหว่าง Draft (ทดสอบฟอร์มโดยไม่ต้องมีตาราง)
- Fix bug `syncTable` ให้รองรับ `submission_table` แบบ custom

## Non-goals

- Field-change staging (เก็บ draft ของ field แยกจาก field จริง) — Approach 3 ที่ตัดออกด้วย YAGNI
- Migration wizard สำหรับการย้ายข้อมูลระหว่าง column — ขอให้ admin ทำนอกแอป
- Archived table snapshots (rename แทน drop)
- Draft isolation แบบเต็ม (schema preview mode, branch-like) — V2+

## Design

### 1. Data Model

**Migration ใหม่**: `add_publish_lifecycle_to_document_forms`

```php
Schema::table('document_forms', function (Blueprint $t) {
    $t->enum('status', ['draft', 'published'])->default('draft')->after('is_active')->index();
    $t->timestamp('published_at')->nullable()->after('status');
    $t->foreignId('published_by_user_id')->nullable()->after('published_at')
        ->constrained('users')->nullOnDelete();
});

// Backfill: ฟอร์มเก่าทั้งหมดที่มี submission_table อยู่แล้ว → published
DB::table('document_forms')
    ->whereNotNull('submission_table')
    ->update([
        'status' => 'published',
        'published_at' => DB::raw('COALESCE(updated_at, NOW())'),
    ]);
```

**Migration ใหม่**: `create_document_form_publish_logs_table`

```php
Schema::create('document_form_publish_logs', function (Blueprint $t) {
    $t->id();
    $t->foreignId('form_id')->constrained('document_forms')->cascadeOnDelete();
    $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $t->enum('action', ['publish', 'republish', 'unpublish']);
    $t->json('diff');             // { added: [...], dropped: [...], changed: [...] }
    $t->json('fields_snapshot');  // field state ณ ตอน apply (สำหรับ audit replay)
    $t->timestamp('applied_at');
    $t->timestamps();
    $t->index(['form_id', 'applied_at']);
});
```

**Model changes** — `app/Models/DocumentForm.php`:

- เพิ่ม `status`, `published_at`, `published_by_user_id` ใน `$fillable`
- `$casts`: `'status' => 'string'` (enum จะใช้ PHP enum class ภายหลังถ้าต้องการ type safety)
- method:
  - `isPublished(): bool`
  - `isDraft(): bool`
  - `hasUnpublishedChanges(): bool` — `status === published && fields.max(updated_at) > published_at`
- relation:
  - `publishedBy(): BelongsTo` (User)
  - `publishLogs(): HasMany` (DocumentFormPublishLog)

**New model**: `app/Models/DocumentFormPublishLog.php` — standard, belongs to form + user

### 2. State Machine

```
          create form                    publish (first)               unpublish
(nothing) ───────────> [draft]  ─────────────────────────────> [published] ──────> [draft]
                         │                                         │
                         │ edit fields: metadata only              │ edit fields: metadata only
                         │ ไม่แตะ DDL เลย                           │ ไม่แตะ DDL จนกว่าจะ Re-publish
                         │                                         │
                         └──── block destructive ──────────────────┘
                              (drop column ที่มี data)
```

**Invariants:**

- Draft → ไม่มี dedicated table ใน DB
- Published → dedicated table มีอยู่ + schema ตรงกับ fields ณ `published_at`
- `has_unpublished_changes` = true เมื่อ field มีการแก้หลัง `published_at` (badge แสดงให้ admin รู้)
- End users submit ฟอร์มได้เฉพาะ `status=published AND is_active=true`

### 3. Service Layer

**New class**: `app/Services/FormPublishService.php`

```php
class FormPublishService
{
    public function __construct(private FormSchemaService $schema) {}

    public function computeDiff(DocumentForm $form): FormSchemaDiff;
    public function publish(DocumentForm $form, User $actor, string $confirmedDiffHash): void;
    public function unpublish(DocumentForm $form, User $actor): void;
}
```

**New DTO**: `app/Services/DTO/FormSchemaDiff.php`

```php
final class FormSchemaDiff
{
    public function __construct(
        public readonly bool $isFirstPublish,
        public readonly array $added,     // [{ field_key, field_type, sql_type }]
        public readonly array $dropped,   // [{ column, row_count }]
        public readonly array $changed,   // [{ column, from_type, to_type, row_count }]
        public readonly array $blocked,   // subset of dropped+changed where row_count > 0
        public readonly string $ddlPreview,
        public readonly string $hash,     // sha1((form.updated_at, fields state))
    ) {}

    public function isDestructive(): bool { return count($this->blocked) > 0; }
}
```

**Logic ของ `computeDiff`**:

1. `table = $form->submission_table`
2. ถ้า `!Schema::hasTable($table)` → `isFirstPublish = true`; added = ทุก field ยกเว้น `section`, `auto_number`
3. มีอยู่แล้ว:
   - `existingCols = Schema::getColumnListing(table) - RESERVED_COLUMNS`
   - `desiredCols = fields ปัจจุบัน (ยกเว้น skip types)`
   - `added = desiredCols.keys() - existingCols`
   - `dropped = existingCols - desiredCols.keys()` → count rows per column: `SELECT COUNT(*) FROM {table} WHERE {col} IS NOT NULL`
   - `changed = intersection ที่ field_type ต่างจาก column type` → count rows
4. `blocked = [c in dropped ∪ changed where row_count > 0]`
5. `hash = sha1(form.updated_at . '|' . fields.pluck('field_key', 'field_type', 'is_required').toJson())`

**Logic ของ `publish`**:

1. Recompute diff ใหม่จาก state ปัจจุบัน (ไม่เชื่อ hash ที่ client ส่งมาอย่างเดียว) → ถ้า `diff.hash !== $confirmedDiffHash` → `throw DiffStaleException` (มีคนแก้ระหว่างที่ admin review modal)
2. ถ้า `diff.isDestructive()` → `throw DestructiveChangeException($blocked)` — ตรวจซ้ำแม้ client ส่งมา เพราะ data row อาจเปลี่ยนระหว่างนั้น
3. ใน transaction:
   - ถ้า `isFirstPublish` → `$schema->createTable($form)`
   - ไม่งั้น → `$schema->syncTable($form, $form->fields)` (**ต้อง fix bug ก่อน** — ดู §6)
   - `$form->update(['status' => 'published', 'published_at' => now(), 'published_by_user_id' => $actor->id])`
   - Insert `DocumentFormPublishLog` with action (`publish` ถ้า isFirstPublish, `republish` ถ้าไม่ใช่) + diff + fields_snapshot

**Logic ของ `unpublish`**:

1. `$form->update(['status' => 'draft', 'published_at' => null, 'published_by_user_id' => null])`
2. **ไม่ลบ table** — ข้อมูลคงอยู่, schema คงเดิมใน DB (admin จะ re-publish แล้วค่อย sync ถ้าต้องการแก้)
3. Insert log with action=`unpublish`, diff=`{}`, fields_snapshot = current fields

### 4. Endpoints & Controller

**Controller changes** — `app/Http/Controllers/Web/DocumentFormController.php`:

- `update()`: **[BREAKING behavior change]** เอา DDL calls ออก (`createTable` / `syncTable` ใน lines 294, 296) — เหลือแค่บันทึก metadata เท่านั้น Schema sync ย้ายไปเกิดเฉพาะตอน `publish()` action — admin ต้องกด Publish เพิ่มหลัง Save
- `store()`: เช่นเดียวกัน ลบ `createTable` call (line 255) — ฟอร์มใหม่ถูกสร้างเป็น `status=draft` เท่านั้น
- New: `publishPreview(DocumentForm $form): JsonResponse` — calls `FormPublishService::computeDiff()`, returns diff JSON
- New: `publish(Request $r, DocumentForm $form): RedirectResponse` — validates `diff_hash`, calls service, catches exceptions → flash error with blocked columns
- New: `unpublish(DocumentForm $form): RedirectResponse`
- New: `preview(DocumentForm $form): View` — preview submit UI
- New: `previewValidate(Request $r, DocumentForm $form): JsonResponse` — validates payload against fields, returns `{ valid, errors, payload }` ไม่เขียน DB

**Routes** — `backend/routes/web.php`:

```php
Route::middleware(['auth.web', 'super-admin'])->prefix('settings/document-forms')->group(function () {
    // ...existing...
    Route::post('{documentForm}/publish/preview', [DocumentFormController::class, 'publishPreview'])->name('...publish.preview');
    Route::post('{documentForm}/publish',         [DocumentFormController::class, 'publish'])->name('...publish');
    Route::post('{documentForm}/unpublish',       [DocumentFormController::class, 'unpublish'])->name('...unpublish');
    Route::get ('{documentForm}/preview',         [DocumentFormController::class, 'preview'])->name('...preview');
    Route::post('{documentForm}/preview/validate',[DocumentFormController::class, 'previewValidate'])->name('...preview.validate');
});
```

### 5. UX

**Edit page** (`resources/views/settings/document-forms/edit.blade.php`):

Header เพิ่มแถบสถานะ:

```
┌──────────────────────────────────────────────────────────────┐
│ ใบแจ้งซ่อมเครื่องจักร NTEQ                                     │
│ [Draft]          หรือ  [Published ✓]  [⚠ Has unpublished     │
│                                          changes]            │
├──────────────────────────────────────────────────────────────┤
│ ...form metadata + fields editor... (เหมือนเดิม)              │
├──────────────────────────────────────────────────────────────┤
│ [Save]  [Preview…]  [Publish…]        (when status=draft)    │
│ [Save]  [Preview…]  [Re-publish…]  [Unpublish]  (published) │
├──────────────────────────────────────────────────────────────┤
│ ▸ Publish history (collapsible, 20 รายการล่าสุด)              │
└──────────────────────────────────────────────────────────────┘
```

**Publish modal** (Alpine + fetch):

```
╔══════════════════════════════════════════════════════╗
║  Publish form: ใบแจ้งซ่อมเครื่องจักร NTEQ              ║
╠══════════════════════════════════════════════════════╣
║  Schema changes for `nteq_maintenance`:              ║
║                                                      ║
║  ✚ Added (2)                                         ║
║     • document_date       DATE                       ║
║     • equipment_serial    VARCHAR(255)               ║
║                                                      ║
║  ✖ Dropped (1)                                       ║
║     • old_notes           TEXT    — empty, safe      ║
║                                                      ║
║  <details>DDL preview (SQL)</details>                ║
║                                                      ║
║  [ Cancel ]                  [ Confirm & Publish ]   ║
╚══════════════════════════════════════════════════════╝
```

ถ้ามี blocked columns:

```
║  ✖ Dropped (1)                                       ║
║     • customer_email   VARCHAR  ⚠ 327 rows — DATA LOSS ║
║                                                      ║
║  ⚠ Destructive change blocked.                       ║
║    Unpublish and migrate data before removing        ║
║    this column.                                      ║
║                                                      ║
║  [ Cancel ]                                          ║
```

**Preview submit page** — route `/settings/document-forms/{id}/preview`:

- Header: "Preview mode — ไม่บันทึกลง DB"
- เรียก `components/dynamic-field.blade.php` วน loop ของ `$form->fields`
- Submit → AJAX ไป `POST .../preview/validate` → แสดง validation error หรือ "Would save: {payload JSON}"
- Accessible ทั้ง Draft และ Published

### 6. Bug Fix (prerequisite)

`app/Services/FormSchemaService.php:66` — method `syncTable()`:

```diff
-$table = $this->getTableName($form->form_key);
+$table = $form->submission_table ?: $this->getTableName($form->form_key);
```

เพิ่ม test `FormSchemaServiceTest::sync_table_respects_custom_submission_table` — ใช้ sqlite :memory:, สร้างฟอร์มที่มี `submission_table='foo_custom'`, เพิ่ม field ใหม่, เรียก syncTable, assert Schema::hasColumn('foo_custom', new_field_key)

### 7. Query Gating สำหรับ End Users

ฟิลเตอร์ฟอร์มให้เห็นเฉพาะที่ `published + active` สำหรับ path ที่ผู้ใช้ปลายทางใช้งาน

**Approach:** เพิ่ม scope ใหม่ `scopePublished(Builder $q): Builder` บน `DocumentForm` + chain เข้า `scopeVisibleToUser` ที่มีอยู่แล้ว (`app/Models/DocumentForm.php:45`) เพื่อให้ controller ที่เรียก `visibleToUser()` อยู่แล้วไม่ต้องแก้

```php
public function scopePublished(Builder $q): Builder {
    return $q->where('status', 'published');
}

public function scopeVisibleToUser(Builder $q, ?int $departmentId): Builder {
    // ...existing department logic...
    return $q->published();  // chain เข้าไปท้าย
}
```

**Controllers ที่ต้องตรวจ/แก้** (ทั้งหมด query `DocumentForm::query()->where('is_active', true)`):

| Controller | จุด | Action |
|------------|-----|--------|
| `RepairRequestController` | `:45, :74` | ใช้ `visibleToUser()` อยู่แล้ว — auto-fixed |
| `PurchaseOrderController` | `:70, :165` | เพิ่ม `->published()` explicit |
| `PurchaseRequestController` | `:52, :129` | เพิ่ม `->published()` explicit |
| `MaintenanceController` | `:52, :123` | เพิ่ม `->published()` explicit |
| `SparePartsController` | `:93, :207` | เพิ่ม `->published()` explicit |
| `ApprovalController` | `:123` | เพิ่ม `->published()` explicit |
| `DocumentFormSubmissionController` | `:26` | เพิ่ม `->published()` explicit |
| `ApprovalFlowService` | `:137` | **ไม่แก้** — service นี้ resolve form จาก submission ที่ submit ไปแล้ว ต้องรองรับกรณีฟอร์มถูก unpublish ภายหลังด้วย |
| `DocumentFormController::index` | `:42` | **ไม่แก้** — admin list ต้องเห็นทั้ง draft + published พร้อม status badge |
| `ClearDocumentModuleCommand`, `DocumentFormsPurgeCommand` | purge commands | **ไม่แก้** — ต้อง purge ทุกสถานะ |

### 8. Permissions

- Publish / Unpublish / Preview ทุก endpoint ผ่าน `super-admin` middleware (เหมือน `/settings/*` อื่น ๆ)
- ไม่สร้าง Spatie permission ใหม่ — ใช้ `is_super_admin=true` ที่มีอยู่
- Banner "Has unpublished changes" แสดงให้ทุก admin ที่เข้าหน้า edit ได้, แต่ปุ่ม Publish disabled สำหรับ non-super-admin (guard ที่ view layer + middleware)

### 9. i18n

เพิ่ม translation keys ใน `lang/en/common.php` + `lang/th/common.php`:

- `document_form.status.draft` / `.published`
- `document_form.has_unpublished_changes`
- `document_form.publish` / `.unpublish` / `.re_publish` / `.preview_submit`
- `document_form.publish_confirm_title`
- `document_form.diff.added` / `.dropped` / `.changed` / `.blocked_data_loss`
- `document_form.publish_log.publish` / `.republish` / `.unpublish`
- `document_form.preview_mode_notice`

## Testing

### Unit

`tests/Unit/FormPublishServiceTest.php`:

- `compute_diff_first_publish_puts_all_fields_in_added`
- `compute_diff_detects_added_dropped_changed_for_existing_table`
- `compute_diff_blocks_drop_with_nonzero_rows`
- `compute_diff_allows_drop_of_empty_column`
- `diff_hash_changes_when_fields_change`
- `publish_with_stale_hash_throws_DiffStaleException`
- `publish_with_blocked_columns_throws_DestructiveChangeException_and_no_DDL_runs`
- `publish_success_creates_table_updates_status_and_writes_log`
- `republish_success_calls_syncTable_and_writes_log_with_action_republish`
- `unpublish_sets_draft_preserves_table_writes_log`

`tests/Unit/FormSchemaServiceTest.php` เพิ่ม:

- `sync_table_respects_custom_submission_table`

### Feature

`tests/Feature/DocumentFormPublishTest.php`:

- `super_admin_can_publish_draft_form_creates_table`
- `super_admin_can_unpublish_published_form_preserves_table`
- `editing_published_form_shows_has_unpublished_changes_banner`
- `publish_blocked_when_dropping_column_with_data`
- `non_super_admin_cannot_publish` → 403
- `draft_form_not_shown_in_repair_requests_form_picker`
- `preview_submit_validates_but_does_not_write_db`
- `publish_preview_returns_diff_json_without_applying`

Existing tests ที่ต้อง update: `RepairRequestWebTest` อาจมี assertion ที่ dependency กับ `is_active` เท่านั้น → ตรวจว่า form ที่ seed ใน test ต้อง `status=published` ด้วย

## Migration / Rollout

1. **Migration order:**
   - `add_publish_lifecycle_to_document_forms` (with backfill)
   - `create_document_form_publish_logs_table`
   - Bug fix `syncTable` (no migration, just code)

2. **Existing deployments:**
   - ฟอร์มเก่าทั้งหมดกลายเป็น `status=published` อัตโนมัติ (ไม่ break users)
   - `published_by_user_id = null` (ไม่รู้ประวัติ)
   - หน้า form list แสดง `[Published ✓]` ทุกฟอร์มเดิม

3. **Seeders:**
   - `NteqPolymerDemoSeeder`, `IndustryTemplateSeeder`, `DocumentFormSeeder` — เพิ่ม `'status' => 'published', 'published_at' => now()` ใน `DocumentForm::updateOrCreate()`
   - `DocumentFormSeeder` อาจ seed ตัวอย่าง draft ไว้หนึ่งตัว เพื่อทดสอบ flow

4. **Docs update:**
   - `CLAUDE.md` §7 "ฟอร์มเอกสาร" — เพิ่มหมายเหตุว่า submit ต้องผ่าน `status=published`
   - `Summary.md` — เพิ่มบรรทัด "DocumentForm มี lifecycle Draft/Published"

## Files to Modify

| File | Change |
|------|--------|
| `database/migrations/*_add_publish_lifecycle_to_document_forms.php` | NEW |
| `database/migrations/*_create_document_form_publish_logs_table.php` | NEW |
| `app/Models/DocumentForm.php` | fillable, casts, methods, relations, scope |
| `app/Models/DocumentFormPublishLog.php` | NEW |
| `app/Services/FormPublishService.php` | NEW |
| `app/Services/DTO/FormSchemaDiff.php` | NEW |
| `app/Services/FormSchemaService.php` | bug fix `syncTable` (line 66) |
| `app/Http/Controllers/Web/DocumentFormController.php` | remove DDL from update(), add publish/unpublish/preview actions |
| `app/Http/Controllers/Web/RepairRequestController.php` | query gating (line 48, 77) |
| `routes/web.php` | 5 new routes |
| `resources/views/settings/document-forms/edit.blade.php` | status badge, publish buttons, history section |
| `resources/views/settings/document-forms/_publish_modal.blade.php` | NEW (Alpine + fetch) |
| `resources/views/settings/document-forms/preview.blade.php` | NEW |
| `lang/en/common.php`, `lang/th/common.php` | new keys (§9) |
| `database/seeders/*` | add status=published to seeded forms |
| `tests/Unit/FormPublishServiceTest.php` | NEW |
| `tests/Unit/FormSchemaServiceTest.php` | add sync_table test |
| `tests/Feature/DocumentFormPublishTest.php` | NEW |

## Open Questions / Future Work

- **Schema diff for nested table columns** — ถ้า `parts_list` (field_type=table) แก้ columns ภายใน → ตอนนี้เก็บใน JSON `options` ไม่กระทบ DDL — ยังไม่รองรับ diff ภายใน (V2+)
- **Form versioning** — เก็บ snapshot field set ต่อเวอร์ชัน เพื่อ replay submission เก่าบน field set ปัจจุบันได้ยาก (V2+)
- **Dual-write to dedicated table** — `RepairRequestController` ยังไม่ dual-write (ตารางจะว่าง) → งานแยก, ไม่อยู่ใน scope design นี้
- **Archive instead of drop** — rename column → `_archived_<timestamp>` แทน drop (V2+)
