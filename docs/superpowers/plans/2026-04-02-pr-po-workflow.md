# PR/PO Workflow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Purchase Request + Purchase Order modules with amount-based approval workflows, following the exact patterns of SparePartsController and existing document infrastructure.

**Architecture:** Each document type (purchase_request, purchase_order) uses the existing ApprovalFlowService, ApprovalInstance, and DocumentForm infrastructure. Line items are stored in two new tables (purchase_request_items, purchase_order_items). PO can only be created from an approved PR via `?from_pr={id}` query param. No new services — reuse everything.

**Tech Stack:** Laravel 12, Blade + Alpine.js, Spatie Permission, SQLite (dev), existing ApprovalFlowService

**Spec:** `docs/superpowers/specs/2026-04-01-pr-po-workflow-design.md`

**Key pattern files to read before implementing:**
- `app/Http/Controllers/Web/SparePartsController.php` — primary pattern
- `database/seeders/ApprovalWorkflowDemoSeeder.php` — workflow seeder pattern
- `database/seeders/DocumentFormSeeder.php` — form seeder pattern
- `resources/views/spare-parts/requisition-create.blade.php` — line items UI pattern
- `resources/views/spare-parts/requisition-show.blade.php` — show page pattern

---

## File Map

| Action | File |
|---|---|
| Create | `database/migrations/2026_04_02_000001_create_purchase_request_items_table.php` |
| Create | `database/migrations/2026_04_02_000002_create_purchase_order_items_table.php` |
| Create | `app/Models/PurchaseRequestItem.php` |
| Create | `app/Models/PurchaseOrderItem.php` |
| Modify | `database/seeders/PermissionSeeder.php` — add 3 permissions to `$exactPermissions` |
| Modify | `database/seeders/RolePermissionSeeder.php` — grant view perms to approver |
| Modify | `database/seeders/DocumentTypeSeeder.php` — add purchase_request + purchase_order |
| Modify | `database/seeders/DocumentFormSeeder.php` — add purchase_request_default + purchase_order_default |
| Create | `database/seeders/PurchaseWorkflowSeeder.php` — workflows + amount policies |
| Modify | `database/seeders/DatabaseSeeder.php` — add PurchaseWorkflowSeeder |
| Modify | `database/seeders/NavigationMenuSeeder.php` — add Purchasing parent + 4 children |
| Modify | `routes/web.php` — add 8 PR/PO routes |
| Modify | `lang/en/common.php` + `lang/th/common.php` — add PR/PO translation keys |
| Modify | `resources/lang/en/common.php` + `resources/lang/th/common.php` — same keys |
| Create | `app/Http/Controllers/Web/PurchaseRequestController.php` |
| Create | `app/Http/Controllers/Web/PurchaseOrderController.php` |
| Create | `resources/views/purchase-requests/index.blade.php` |
| Create | `resources/views/purchase-requests/create.blade.php` |
| Create | `resources/views/purchase-requests/show.blade.php` |
| Create | `resources/views/purchase-orders/index.blade.php` |
| Create | `resources/views/purchase-orders/create.blade.php` |
| Create | `resources/views/purchase-orders/show.blade.php` |
| Create | `tests/Feature/PurchaseWorkflowTest.php` |

---

### Task 1: Migrations + Models

**Files:**
- Create: `database/migrations/2026_04_02_000001_create_purchase_request_items_table.php`
- Create: `database/migrations/2026_04_02_000002_create_purchase_order_items_table.php`
- Create: `app/Models/PurchaseRequestItem.php`
- Create: `app/Models/PurchaseOrderItem.php`

- [ ] **Step 1: Create purchase_request_items migration**

```php
<?php
// database/migrations/2026_04_02_000001_create_purchase_request_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_instance_id')->constrained('approval_instances')->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('qty', 12, 2);
            $table->string('unit');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('approval_instance_id');
        });
    }
    public function down(): void { Schema::dropIfExists('purchase_request_items'); }
};
```

- [ ] **Step 2: Create purchase_order_items migration**

```php
<?php
// database/migrations/2026_04_02_000002_create_purchase_order_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_instance_id')->constrained('approval_instances')->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('qty', 12, 2);
            $table->string('unit');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('approval_instance_id');
        });
    }
    public function down(): void { Schema::dropIfExists('purchase_order_items'); }
};
```

- [ ] **Step 3: Run migrations**

```bash
cd backend && php artisan migrate
```
Expected: both tables created, no errors.

- [ ] **Step 4: Create PurchaseRequestItem model**

```php
<?php
// app/Models/PurchaseRequestItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'approval_instance_id', 'item_name', 'qty', 'unit',
        'unit_price', 'total_price', 'notes',
    ];

    public function approvalInstance(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstance::class);
    }
}
```

- [ ] **Step 5: Create PurchaseOrderItem model**

```php
<?php
// app/Models/PurchaseOrderItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'approval_instance_id', 'item_name', 'qty', 'unit',
        'unit_price', 'total_price', 'notes',
    ];

    public function approvalInstance(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstance::class);
    }
}
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_04_02_000001_create_purchase_request_items_table.php \
        database/migrations/2026_04_02_000002_create_purchase_order_items_table.php \
        app/Models/PurchaseRequestItem.php \
        app/Models/PurchaseOrderItem.php
git commit -m "feat: add purchase_request_items and purchase_order_items tables and models"
```

---

### Task 2: Permissions + Document Types + Forms

**Files:**
- Modify: `database/seeders/PermissionSeeder.php`
- Modify: `database/seeders/RolePermissionSeeder.php`
- Modify: `database/seeders/DocumentTypeSeeder.php`
- Modify: `database/seeders/DocumentFormSeeder.php`

- [ ] **Step 1: Add 3 permissions to PermissionSeeder `$exactPermissions` array**

Find the `$exactPermissions` array in `database/seeders/PermissionSeeder.php` (around line 41) and append:

```php
['name' => 'view_purchase_requests', 'module' => 'purchase_requests', 'action' => 'read'],
['name' => 'view_purchase_orders',   'module' => 'purchase_orders',   'action' => 'read'],
['name' => 'purchase_order.create',  'module' => 'purchase_orders',   'action' => 'create'],
```

- [ ] **Step 2: Grant view permissions to approver role in RolePermissionSeeder**

Find the `$approver->syncPermissions(...)` call in `database/seeders/RolePermissionSeeder.php` and add the two view permissions:

```php
$approver->syncPermissions(
    \Spatie\Permission\Models\Permission::whereIn('name', [
        'approval.approve',
        'manage_own_dashboard',
        'view_purchase_requests',
        'view_purchase_orders',
    ])->pluck('name')
);
```

- [ ] **Step 3: Add purchase_request and purchase_order to DocumentTypeSeeder**

Append to the `$types` array in `database/seeders/DocumentTypeSeeder.php`:

```php
['code' => 'purchase_request', 'label_en' => 'Purchase Request', 'label_th' => 'ใบขอซื้อ',   'icon' => 'shopping-cart',   'sort_order' => 4, 'routing_mode' => 'hybrid'],
['code' => 'purchase_order',   'label_en' => 'Purchase Order',   'label_th' => 'ใบสั่งซื้อ', 'icon' => 'document-check', 'sort_order' => 5, 'routing_mode' => 'organization_wide'],
```

- [ ] **Step 4: Add PR and PO forms to DocumentFormSeeder**

Append to the end of `database/seeders/DocumentFormSeeder.php` `run()` method (before the closing `}`):

```php
// ─── Purchase Request Form ──────────────────────────────
$prForm = DocumentForm::updateOrCreate(
    ['form_key' => 'purchase_request_default'],
    [
        'name'          => 'ฟอร์มใบขอซื้อ (ค่าเริ่มต้น)',
        'document_type' => 'purchase_request',
        'description'   => 'ฟอร์มมาตรฐานสำหรับใบขอซื้อ',
        'is_active'     => true,
    ]
);
$prForm->departments()->sync([]);

foreach ([
    ['field_key' => 'title',          'label' => 'หัวข้อ',              'field_type' => 'text',     'is_required' => true,  'sort_order' => 1, 'placeholder' => 'ระบุหัวข้อใบขอซื้อ',    'options' => null, 'editable_by' => ['requester']],
    ['field_key' => 'vendor_name',    'label' => 'ชื่อผู้ขาย',          'field_type' => 'text',     'is_required' => false, 'sort_order' => 2, 'placeholder' => 'ชื่อบริษัท/ร้านค้า',     'options' => null, 'editable_by' => ['requester']],
    ['field_key' => 'required_date',  'label' => 'วันที่ต้องการสินค้า', 'field_type' => 'date',     'is_required' => true,  'sort_order' => 3, 'placeholder' => null,                    'options' => null, 'editable_by' => ['requester']],
    ['field_key' => 'budget_code',    'label' => 'รหัสงบประมาณ',       'field_type' => 'text',     'is_required' => false, 'sort_order' => 4, 'placeholder' => 'รหัสงบประมาณ (ถ้ามี)', 'options' => null, 'editable_by' => ['requester']],
    ['field_key' => 'reason',         'label' => 'เหตุผลการขอซื้อ',    'field_type' => 'textarea', 'is_required' => true,  'sort_order' => 5, 'placeholder' => 'ระบุเหตุผลและความจำเป็น', 'options' => null, 'editable_by' => ['requester']],
    ['field_key' => 'amount',         'label' => 'มูลค่ารวม (บาท)',     'field_type' => 'number',   'is_required' => true,  'sort_order' => 6, 'placeholder' => '0.00',                 'options' => null, 'editable_by' => ['requester']],
    ['field_key' => 'approver_note',  'label' => 'หมายเหตุผู้อนุมัติ', 'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 7, 'placeholder' => null,                    'options' => null, 'editable_by' => ['step_1']],
] as $field) {
    $prForm->fields()->updateOrCreate(['field_key' => $field['field_key']], array_merge($field, ['visible_to_departments' => null]));
}

// ─── Purchase Order Form ────────────────────────────────
$poForm = DocumentForm::updateOrCreate(
    ['form_key' => 'purchase_order_default'],
    [
        'name'          => 'ฟอร์มใบสั่งซื้อ (ค่าเริ่มต้น)',
        'document_type' => 'purchase_order',
        'description'   => 'ฟอร์มมาตรฐานสำหรับใบสั่งซื้อ',
        'is_active'     => true,
    ]
);
$poForm->departments()->sync([]);

foreach ([
    ['field_key' => 'title',          'label' => 'หัวข้อ',              'field_type' => 'text',     'is_required' => true,  'sort_order' => 1, 'placeholder' => 'ระบุหัวข้อใบสั่งซื้อ', 'options' => null,                          'editable_by' => ['requester']],
    ['field_key' => 'vendor_name',    'label' => 'ชื่อผู้ขาย',          'field_type' => 'text',     'is_required' => true,  'sort_order' => 2, 'placeholder' => 'ชื่อบริษัท/ร้านค้า',   'options' => null,                          'editable_by' => ['requester']],
    ['field_key' => 'vendor_address', 'label' => 'ที่อยู่ผู้ขาย',       'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 3, 'placeholder' => 'ที่อยู่สำหรับออกใบสั่งซื้อ', 'options' => null,                   'editable_by' => ['requester']],
    ['field_key' => 'delivery_date',  'label' => 'วันที่ต้องการส่งของ', 'field_type' => 'date',     'is_required' => true,  'sort_order' => 4, 'placeholder' => null,                  'options' => null,                          'editable_by' => ['requester']],
    ['field_key' => 'payment_terms',  'label' => 'เงื่อนไขการชำระเงิน', 'field_type' => 'select',   'is_required' => true,  'sort_order' => 5, 'placeholder' => null,                  'options' => ['cash','net_30','net_60'],     'editable_by' => ['requester']],
    ['field_key' => 'amount',         'label' => 'มูลค่ารวม (บาท)',     'field_type' => 'number',   'is_required' => true,  'sort_order' => 6, 'placeholder' => '0.00',               'options' => null,                          'editable_by' => ['requester']],
    ['field_key' => 'approver_note',  'label' => 'หมายเหตุผู้อนุมัติ', 'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 7, 'placeholder' => null,                  'options' => null,                          'editable_by' => ['step_1']],
] as $field) {
    $poForm->fields()->updateOrCreate(['field_key' => $field['field_key']], array_merge($field, ['visible_to_departments' => null]));
}
```

- [ ] **Step 5: Run seeders to verify**

```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=DocumentTypeSeeder
php artisan db:seed --class=DocumentFormSeeder
```
Expected: no errors, `php artisan tinker --execute="echo App\Models\DocumentForm::where('form_key','purchase_request_default')->count();"` → `1`

- [ ] **Step 6: Commit**

```bash
git add database/seeders/PermissionSeeder.php \
        database/seeders/RolePermissionSeeder.php \
        database/seeders/DocumentTypeSeeder.php \
        database/seeders/DocumentFormSeeder.php
git commit -m "feat: add PR/PO permissions, document types, and form definitions"
```

---

### Task 3: PurchaseWorkflowSeeder + Navigation + DatabaseSeeder

**Files:**
- Create: `database/seeders/PurchaseWorkflowSeeder.php`
- Modify: `database/seeders/NavigationMenuSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create PurchaseWorkflowSeeder**

```php
<?php
// database/seeders/PurchaseWorkflowSeeder.php
namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStage;
use App\Models\DocumentForm;
use App\Models\DocumentFormWorkflowPolicy;
use App\Models\DocumentFormWorkflowRange;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PurchaseWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $deptMgr  = Position::where('code', 'DEPT_MGR')->first();
        $plantMgr = Position::where('code', 'PLANT_MGR')->first();

        if (! $deptMgr || ! $plantMgr) {
            $this->command?->warn('PurchaseWorkflowSeeder: positions missing; run PositionDemoSeeder first.');
            return;
        }

        // ─── PR Workflows ───────────────────────────────────────
        $prSmall = $this->createWorkflow('PR - Small (≤50k)', 'purchase_request', 'ใบขอซื้อมูลค่าต่ำ', [
            ['step_no' => 1, 'name' => 'ผจก.แผนกอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $deptMgr->id],
        ]);

        $prLarge = $this->createWorkflow('PR - Large (>50k)', 'purchase_request', 'ใบขอซื้อมูลค่าสูง', [
            ['step_no' => 1, 'name' => 'ผจก.แผนกอนุมัติ',    'approver_type' => 'position', 'approver_ref' => $deptMgr->id],
            ['step_no' => 2, 'name' => 'ผจก.โรงงานอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $plantMgr->id],
        ]);

        // ─── PO Workflow ────────────────────────────────────────
        $poStandard = $this->createWorkflow('PO - Standard', 'purchase_order', 'ใบสั่งซื้อทุกมูลค่า', [
            ['step_no' => 1, 'name' => 'ผจก.โรงงานอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $plantMgr->id],
        ]);

        // ─── Amount Policies ────────────────────────────────────
        $this->createAmountPolicy('purchase_request_default', [
            ['min' => 0,         'max' => 50000, 'workflow' => $prSmall],
            ['min' => 50000.01,  'max' => null,  'workflow' => $prLarge],
        ]);

        $this->createAmountPolicy('purchase_order_default', [
            ['min' => 0, 'max' => null, 'workflow' => $poStandard],
        ]);

        $this->command?->info('PurchaseWorkflowSeeder: 3 workflows + 2 amount policies created.');
    }

    private function createWorkflow(string $name, string $documentType, string $description, array $stages): ApprovalWorkflow
    {
        $workflow = ApprovalWorkflow::updateOrCreate(
            ['name' => $name],
            ['document_type' => $documentType, 'description' => $description, 'is_active' => true]
        );
        $workflow->stages()->delete();
        foreach ($stages as $stage) {
            ApprovalWorkflowStage::create([
                'workflow_id'   => $workflow->id,
                'step_no'       => $stage['step_no'],
                'name'          => $stage['name'],
                'approver_type' => $stage['approver_type'],
                'approver_ref'  => (string) $stage['approver_ref'],
                'min_approvals' => 1,
                'is_active'     => true,
            ]);
        }
        return $workflow;
    }

    private function createAmountPolicy(string $formKey, array $ranges): void
    {
        $form = DocumentForm::where('form_key', $formKey)->first();
        if (! $form) {
            $this->command?->warn("PurchaseWorkflowSeeder: form {$formKey} not found.");
            return;
        }
        $policy = DocumentFormWorkflowPolicy::updateOrCreate(
            ['form_id' => $form->id, 'department_id' => null],
            ['use_amount_condition' => true, 'workflow_id' => $ranges[0]['workflow']->id]
        );
        $policy->ranges()->delete();
        foreach ($ranges as $i => $range) {
            DocumentFormWorkflowRange::create([
                'policy_id'   => $policy->id,
                'min_amount'  => $range['min'],
                'max_amount'  => $range['max'],
                'workflow_id' => $range['workflow']->id,
                'sort_order'  => $i + 1,
            ]);
        }
    }
}
```

- [ ] **Step 2: Add Purchasing nav menus to NavigationMenuSeeder**

Append to the `$menus` array in `database/seeders/NavigationMenuSeeder.php` (before the closing `]`):

```php
[
    'id' => 50, 'parent_id' => null,
    'label' => 'Purchasing', 'label_en' => 'Purchasing', 'label_th' => 'จัดซื้อ',
    'icon' => 'shopping-cart', 'route' => null, 'permission' => null, 'sort_order' => 5,
],
[
    'id' => 51, 'parent_id' => 50,
    'label' => 'Purchase Requests', 'label_en' => 'Purchase Requests', 'label_th' => 'ใบขอซื้อ',
    'icon' => 'document-plus', 'route' => '/purchase-requests', 'permission' => 'view_purchase_requests', 'sort_order' => 1,
],
[
    'id' => 52, 'parent_id' => 50,
    'label' => 'Create PR', 'label_en' => 'Create Purchase Request', 'label_th' => 'สร้างใบขอซื้อ',
    'icon' => 'document-plus', 'route' => '/purchase-requests/create', 'permission' => null, 'sort_order' => 2,
],
[
    'id' => 53, 'parent_id' => 50,
    'label' => 'Purchase Orders', 'label_en' => 'Purchase Orders', 'label_th' => 'ใบสั่งซื้อ',
    'icon' => 'document-check', 'route' => '/purchase-orders', 'permission' => 'view_purchase_orders', 'sort_order' => 3,
],
[
    'id' => 54, 'parent_id' => 50,
    'label' => 'Create PO', 'label_en' => 'Create Purchase Order', 'label_th' => 'สร้างใบสั่งซื้อ',
    'icon' => 'document-check', 'route' => '/purchase-orders/create', 'permission' => 'purchase_order.create', 'sort_order' => 4,
],
```

- [ ] **Step 3: Add PurchaseWorkflowSeeder to DatabaseSeeder**

In `database/seeders/DatabaseSeeder.php`, append to the `$this->call([...])` array:

```php
PurchaseWorkflowSeeder::class,
```

- [ ] **Step 4: Run and verify**

```bash
php artisan db:seed --class=PurchaseWorkflowSeeder
php artisan db:seed --class=NavigationMenuSeeder
```
Expected: no errors. Sidebar shows "จัดซื้อ" menu.

- [ ] **Step 5: Commit**

```bash
git add database/seeders/PurchaseWorkflowSeeder.php \
        database/seeders/NavigationMenuSeeder.php \
        database/seeders/DatabaseSeeder.php
git commit -m "feat: add PR/PO workflows, nav menus, and register in DatabaseSeeder"
```

---

### Task 4: Routes + Translations

**Files:**
- Modify: `routes/web.php`
- Modify: `lang/en/common.php`, `lang/th/common.php`
- Modify: `resources/lang/en/common.php`, `resources/lang/th/common.php`

- [ ] **Step 1: Add PR/PO routes to routes/web.php**

Add after the spare-parts routes (after line containing `spare-parts.requisition.issue`):

```php
use App\Http\Controllers\Web\PurchaseRequestController;
use App\Http\Controllers\Web\PurchaseOrderController;
```

Add to the `use` imports at the top of the file (after existing use statements).

Then add routes inside the `Route::middleware('auth.web')->group(...)`:

```php
// Purchase Requests
Route::get('/purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
Route::get('/purchase-requests/create', [PurchaseRequestController::class, 'create'])->name('purchase-requests.create');
Route::post('/purchase-requests', [PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
Route::get('/purchase-requests/{instance}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');

// Purchase Orders
Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])
    ->middleware('permission:purchase_order.create')
    ->name('purchase-orders.create');
Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])
    ->middleware('permission:purchase_order.create')
    ->name('purchase-orders.store');
Route::get('/purchase-orders/{instance}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
```

- [ ] **Step 2: Add translation keys to lang/en/common.php and lang/th/common.php**

Add to `lang/en/common.php`:
```php
'purchasing'                => 'Purchasing',
'purchase_requests'         => 'Purchase Requests',
'purchase_request'          => 'Purchase Request',
'create_purchase_request'   => 'Create Purchase Request',
'purchase_request_desc'     => 'Submit a new purchase request for approval.',
'purchase_orders'           => 'Purchase Orders',
'purchase_order'            => 'Purchase Order',
'create_purchase_order'     => 'Create Purchase Order',
'purchase_order_desc'       => 'Create a purchase order from an approved purchase request.',
'vendor_name'               => 'Vendor Name',
'vendor_address'            => 'Vendor Address',
'required_date'             => 'Required Date',
'budget_code'               => 'Budget Code',
'delivery_date'             => 'Delivery Date',
'payment_terms'             => 'Payment Terms',
'payment_cash'              => 'Cash',
'payment_net_30'            => 'Net 30',
'payment_net_60'            => 'Net 60',
'line_items'                => 'Line Items',
'item_name'                 => 'Item Name',
'qty'                       => 'Qty',
'unit_label'                => 'Unit',
'unit_price'                => 'Unit Price',
'total_price'               => 'Total Price',
'add_line_item'             => 'Add Item',
'create_po_from_pr'         => 'Create PO from this PR',
'pr_reference'              => 'PR Reference',
'no_purchase_requests'      => 'No purchase requests found.',
'no_purchase_orders'        => 'No purchase orders found.',
'po_already_exists'         => 'A purchase order already exists for this purchase request.',
'pr_not_approved'           => 'Purchase request must be approved before creating a PO.',
```

Add the same keys to `lang/th/common.php`:
```php
'purchasing'                => 'จัดซื้อ',
'purchase_requests'         => 'ใบขอซื้อ',
'purchase_request'          => 'ใบขอซื้อ',
'create_purchase_request'   => 'สร้างใบขอซื้อ',
'purchase_request_desc'     => 'ส่งใบขอซื้อใหม่เพื่อขออนุมัติ',
'purchase_orders'           => 'ใบสั่งซื้อ',
'purchase_order'            => 'ใบสั่งซื้อ',
'create_purchase_order'     => 'สร้างใบสั่งซื้อ',
'purchase_order_desc'       => 'สร้างใบสั่งซื้อจากใบขอซื้อที่ได้รับการอนุมัติแล้ว',
'vendor_name'               => 'ชื่อผู้ขาย',
'vendor_address'            => 'ที่อยู่ผู้ขาย',
'required_date'             => 'วันที่ต้องการสินค้า',
'budget_code'               => 'รหัสงบประมาณ',
'delivery_date'             => 'วันที่ต้องการส่งของ',
'payment_terms'             => 'เงื่อนไขการชำระเงิน',
'payment_cash'              => 'เงินสด',
'payment_net_30'            => 'เครดิต 30 วัน',
'payment_net_60'            => 'เครดิต 60 วัน',
'line_items'                => 'รายการสินค้า',
'item_name'                 => 'ชื่อสินค้า/บริการ',
'qty'                       => 'จำนวน',
'unit_label'                => 'หน่วย',
'unit_price'                => 'ราคาต่อหน่วย',
'total_price'               => 'ราคารวม',
'add_line_item'             => 'เพิ่มรายการ',
'create_po_from_pr'         => 'สร้าง PO จาก PR นี้',
'pr_reference'              => 'อ้างอิง PR',
'no_purchase_requests'      => 'ไม่มีใบขอซื้อ',
'no_purchase_orders'        => 'ไม่มีใบสั่งซื้อ',
'po_already_exists'         => 'มีใบสั่งซื้อสำหรับใบขอซื้อนี้แล้ว',
'pr_not_approved'           => 'ใบขอซื้อต้องได้รับการอนุมัติก่อนสร้างใบสั่งซื้อ',
```

Also add the same keys to `resources/lang/en/common.php` and `resources/lang/th/common.php`.

- [ ] **Step 3: Commit**

```bash
git add routes/web.php lang/en/common.php lang/th/common.php \
        resources/lang/en/common.php resources/lang/th/common.php
git commit -m "feat: add PR/PO routes and translation keys"
```

---

### Task 5: PurchaseRequestController + Views

**Files:**
- Create: `app/Http/Controllers/Web/PurchaseRequestController.php`
- Create: `resources/views/purchase-requests/index.blade.php`
- Create: `resources/views/purchase-requests/create.blade.php`
- Create: `resources/views/purchase-requests/show.blade.php`

- [ ] **Step 1: Create PurchaseRequestController**

```php
<?php
// app/Http/Controllers/Web/PurchaseRequestController.php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApprovalInstance;
use App\Models\ApprovalInstanceStep;
use App\Models\Department;
use App\Models\DocumentForm;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Services\ApprovalFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PurchaseRequestController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) (session('user.id') ?? 0);
        $status = $request->query('status');
        if ($status !== null && $status !== '' && ! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = null;
        }

        $myInstances = ApprovalInstance::query()
            ->where('document_type', 'purchase_request')
            ->where('requester_user_id', $userId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['department'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase-requests.index', compact('myInstances', 'status'));
    }

    public function create(): View
    {
        $userId    = (int) (session('user.id') ?? 0);
        $userDeptId = session('user.department_id') ?? User::find($userId)?->department_id;
        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();
        $form = DocumentForm::query()
            ->with('fields')
            ->where('document_type', 'purchase_request')
            ->where('is_active', true)
            ->visibleToUser($userDeptId)
            ->orderBy('id')
            ->first();

        $userModel = $userId > 0 ? User::with(['company', 'branch'])->find($userId) : null;
        $company   = $userModel?->company;
        $branch    = null;
        if ($userModel && $userModel->branch && $userModel->branch->is_active
            && (int) $userModel->branch->company_id === (int) $userModel->company_id) {
            $branch = $userModel->branch;
        }

        return view('purchase-requests.create', compact('departments', 'form', 'company', 'branch', 'userDeptId'));
    }

    public function store(Request $request, ApprovalFlowService $approvalFlowService): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'nullable|integer|exists:departments,id',
            'form_key'      => 'nullable|string|max:100',
            'form_payload'  => 'nullable|array',
            'amount'        => 'nullable|numeric|min:0',
            'items'         => 'required|array|min:1',
            'items.*.item_name'  => 'required|string|max:255',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.unit'       => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price'=> 'required|numeric|min:0',
            'items.*.notes'      => 'nullable|string|max:500',
        ]);

        $payload     = $validated['form_payload'] ?? [];
        $totalAmount = array_sum(array_column($validated['items'], 'total_price'));

        try {
            $instance = $approvalFlowService->start(
                'purchase_request',
                $validated['department_id'] ?? null,
                (int) (session('user.id') ?? 1),
                null,
                $payload,
                $validated['form_key'] ?? null,
                $totalAmount > 0 ? (float) $totalAmount : null
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['workflow' => $this->workflowErrorMessage($e)])->withInput();
        }

        foreach ($validated['items'] as $item) {
            PurchaseRequestItem::create([
                'approval_instance_id' => $instance->id,
                'item_name'   => $item['item_name'],
                'qty'         => $item['qty'],
                'unit'        => $item['unit'],
                'unit_price'  => $item['unit_price'],
                'total_price' => $item['total_price'],
                'notes'       => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('purchase-requests.show', $instance)->with('success', __('common.saved'));
    }

    public function show(ApprovalInstance $instance): View
    {
        abort_unless($instance->document_type === 'purchase_request', 404);
        $this->authorizeViewInstance($instance);

        $instance->load(['steps.actor', 'workflow', 'requester.company', 'requester.branch', 'department']);
        $userId = (int) (session('user.id') ?? 0);

        $lineItems = PurchaseRequestItem::where('approval_instance_id', $instance->id)->get();

        $formForLabels = DocumentForm::query()->with('fields')
            ->where('document_type', 'purchase_request')->where('is_active', true)->orderBy('id')->first();
        $formFields = $formForLabels?->fields ?? collect();

        $userDeptId  = session('user.department_id') ?? User::find($userId)?->department_id;
        $editorRole  = $this->resolveEditorRole($instance, $userId);

        $canAct = false;
        if ($instance->status === 'pending' && in_array('approval.approve', session('user_permissions', []), true)) {
            $currentStep = $instance->steps->firstWhere('step_no', $instance->current_step_no);
            if ($currentStep && $currentStep->action === 'pending') {
                $canAct = $this->userCanActStep($currentStep, $userId);
            }
        }

        // Show "Create PO" button when PR is approved and user has permission
        $canCreatePo = $instance->status === 'approved'
            && (session('user.is_super_admin', false) || in_array('purchase_order.create', session('user_permissions', []), true))
            && ! ApprovalInstance::where('document_type', 'purchase_order')
                ->whereRaw("json_extract(payload, '$.purchase_request_id') = ?", [$instance->id])
                ->exists();

        $requester = $instance->requester;
        $company   = $requester?->company;
        $branch    = null;
        if ($requester && $requester->branch && $requester->branch->is_active
            && (int) $requester->branch->company_id === (int) $requester->company_id) {
            $branch = $requester->branch;
        }

        return view('purchase-requests.show', compact(
            'instance', 'lineItems', 'canAct', 'canCreatePo',
            'formFields', 'formForLabels', 'userDeptId', 'editorRole', 'company', 'branch'
        ));
    }

    private function resolveEditorRole(ApprovalInstance $instance, int $userId): string
    {
        if ($instance->status !== 'pending') return 'view_only';
        $currentStep = $instance->steps->firstWhere('step_no', $instance->current_step_no);
        if ($currentStep && $currentStep->action === 'pending' && $this->userCanActStep($currentStep, $userId)) {
            return 'step_'.$instance->current_step_no;
        }
        return 'view_only';
    }

    private function authorizeViewInstance(ApprovalInstance $instance): void
    {
        if (session('user.is_super_admin', false)) return;
        $uid = (int) (session('user.id') ?? 0);
        if ($instance->requester_user_id === $uid) return;
        if (in_array('approval.approve', session('user_permissions', []), true)) return;
        abort(403);
    }

    private function userCanActStep(ApprovalInstanceStep $step, int $userId): bool
    {
        $user = User::find($userId);
        if (! $user) return false;
        if ($step->approver_type === 'user')     return (string) $step->approver_ref === (string) $userId;
        if ($step->approver_type === 'position') return $user->position_id && (string) $step->approver_ref === (string) $user->position_id;
        return $user->hasRole($step->approver_ref);
    }

    private function workflowErrorMessage(RuntimeException $e): string
    {
        $msg = $e->getMessage();
        return match (true) {
            str_contains($msg, 'Amount is required for amount-based') => __('common.workflow_error_amount_required'),
            str_contains($msg, 'No matching amount range')            => __('common.workflow_error_no_amount_range'),
            str_contains($msg, 'Department is required')              => __('common.workflow_error_department_required'),
            str_contains($msg, 'No workflow binding found')           => __('common.workflow_error_no_binding'),
            str_contains($msg, 'Workflow is not configured')          => __('common.workflow_error_not_configured'),
            default                                                   => __('common.workflow_error_generic'),
        };
    }
}
```

- [ ] **Step 2: Create index view**

```blade
{{-- resources/views/purchase-requests/index.blade.php --}}
@extends('layouts.app')
@section('title', __('common.purchase_requests'))
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.purchase_requests') }}</h2>
        <a href="{{ route('purchase-requests.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            + {{ __('common.create_purchase_request') }}
        </a>
    </div>

    {{-- Status filter tabs --}}
    <div class="flex gap-2 mb-4">
        @foreach ([null => __('common.all'), 'pending' => __('common.status_pending'), 'approved' => __('common.status_approved'), 'rejected' => __('common.status_rejected')] as $val => $label)
            <a href="{{ route('purchase-requests.index', $val ? ['status' => $val] : []) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $status === $val ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($myInstances->isEmpty())
            <p class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">{{ __('common.no_purchase_requests') }}</p>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('common.reference_no') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.department') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.created_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($myInstances as $instance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('purchase-requests.show', $instance) }}"
                                   class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                    {{ $instance->reference_no ?? '#'.$instance->id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $instance->department?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @include('components.status-badge', ['status' => $instance->status])
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $instance->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $myInstances->links() }}
            </div>
        @endif
    </div>
@endsection
```

- [ ] **Step 3: Create create view (with Alpine.js line items)**

```blade
{{-- resources/views/purchase-requests/create.blade.php --}}
@extends('layouts.app')
@section('title', __('common.create_purchase_request'))
@section('content')
    <div class="mb-6">
        <a href="{{ route('purchase-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">{{ __('common.create_purchase_request') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.purchase_request_desc') }}</p>
    </div>

    @if ($errors->has('workflow'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200">
            {{ $errors->first('workflow') }}
        </div>
    @endif

    <div x-data="prForm()" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left: Form fields --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])
            <form id="pr-form" method="POST" action="{{ route('purchase-requests.store') }}" class="space-y-3">
                @csrf
                @if($form)
                    <input type="hidden" name="form_key" value="{{ $form->form_key }}">
                @endif
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.department') }}</label>
                    <select name="department_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <option value="">{{ __('common.department_not_selected') }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($form)
                    @foreach($form->fields as $field)
                        @php $name = "form_payload[{$field->field_key}]"; $value = old("form_payload.{$field->field_key}"); @endphp
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-300">{{ $field->label }}@if($field->is_required) <span class="text-red-500">*</span>@endif</label>
                            @include('components.dynamic-field', ['field' => $field, 'name' => $name, 'value' => $value, 'userDeptId' => $userDeptId ?? null])
                        </div>
                    @endforeach
                @endif
                <input type="hidden" name="amount" :value="totalAmount">
            </form>
        </div>

        {{-- Right: Line items --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.line_items') }}</h4>
            <template x-for="(item, index) in items" :key="index">
                <div class="mb-3 p-3 bg-white dark:bg-gray-900/30 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
                    <div>
                        <label class="text-xs text-gray-500">{{ __('common.item_name') }}</label>
                        <input :name="'items['+index+'][item_name]'" x-model="item.item_name" required
                               class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.qty') }}</label>
                            <input :name="'items['+index+'][qty]'" x-model="item.qty" type="number" min="0.01" step="0.01"
                                   @input="updateTotal(item)" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.unit_label') }}</label>
                            <input :name="'items['+index+'][unit]'" x-model="item.unit" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.unit_price') }}</label>
                            <input :name="'items['+index+'][unit_price]'" x-model="item.unit_price" type="number" min="0" step="0.01"
                                   @input="updateTotal(item)" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                    </div>
                    <input type="hidden" :name="'items['+index+'][total_price]'" :value="item.total_price">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">{{ __('common.total_price') }}: <span x-text="Number(item.total_price).toLocaleString('th-TH', {minimumFractionDigits:2})" class="font-medium text-gray-800 dark:text-gray-200"></span></span>
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                class="text-xs text-red-500 hover:text-red-700">{{ __('common.remove') }}</button>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">{{ __('common.notes') }}</label>
                        <input :name="'items['+index+'][notes]'" x-model="item.notes"
                               class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                </div>
            </template>
            <button type="button" @click="addItem"
                    class="w-full py-2 text-sm text-blue-600 dark:text-blue-400 border border-dashed border-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">
                + {{ __('common.add_line_item') }}
            </button>
            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('common.total_price') }}</span>
                <span x-text="Number(totalAmount).toLocaleString('th-TH', {minimumFractionDigits:2})" class="text-lg font-bold text-blue-600 dark:text-blue-400"></span>
            </div>
            <div class="mt-4">
                <button type="submit" form="pr-form"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    {{ __('common.submit') }}
                </button>
            </div>
        </div>
    </div>

    <script>
    function prForm() {
        return {
            items: [{ item_name: '', qty: 1, unit: '', unit_price: 0, total_price: 0, notes: '' }],
            get totalAmount() {
                return this.items.reduce((s, i) => s + (parseFloat(i.total_price) || 0), 0);
            },
            addItem() { this.items.push({ item_name: '', qty: 1, unit: '', unit_price: 0, total_price: 0, notes: '' }); },
            removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1); },
            updateTotal(item) { item.total_price = ((parseFloat(item.qty)||0) * (parseFloat(item.unit_price)||0)).toFixed(2); },
        };
    }
    </script>
@endsection
```

- [ ] **Step 4: Create show view**

```blade
{{-- resources/views/purchase-requests/show.blade.php --}}
@extends('layouts.app')
@section('title', __('common.purchase_request') . ' ' . ($instance->reference_no ?? '#'.$instance->id))
@section('content')
    <div class="mb-6 flex items-start justify-between">
        <div>
            <a href="{{ route('purchase-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">
                {{ __('common.purchase_request') }}: {{ $instance->reference_no ?? '#'.$instance->id }}
            </h2>
        </div>
        <div class="flex items-center gap-3">
            @include('components.status-badge', ['status' => $instance->status])
            @if($canCreatePo)
                <a href="{{ route('purchase-orders.create', ['from_pr' => $instance->id]) }}"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                    {{ __('common.create_po_from_pr') }}
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Document form fields --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])
            @foreach($formFields as $field)
                @php $value = $instance->payload[$field->field_key] ?? null; @endphp
                @if($value !== null && $value !== '')
                    <div class="mb-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $field->label }}</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-0.5">{{ $value }}</dd>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Approval steps --}}
        <div class="space-y-4">
            @include('components.approval-timeline', ['instance' => $instance, 'canAct' => $canAct])
        </div>
    </div>

    {{-- Line items --}}
    <div class="mt-6 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('common.line_items') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">{{ __('common.item_name') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.qty') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('common.unit_label') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.unit_price') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.total_price') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($lineItems as $i => $item)
                    <tr>
                        <td class="px-4 py-2 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-2">{{ $item->item_name }}@if($item->notes) <span class="text-xs text-gray-400 block">{{ $item->notes }}</span>@endif</td>
                        <td class="px-4 py-2 text-right">{{ number_format($item->qty, 2) }}</td>
                        <td class="px-4 py-2">{{ $item->unit }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right font-semibold text-gray-700 dark:text-gray-300">{{ __('common.total_price') }}</td>
                    <td class="px-4 py-2 text-right font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($lineItems->sum('total_price'), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
```

- [ ] **Step 5: Visit /purchase-requests/create and verify form loads**

```bash
php artisan serve
# Open browser: http://localhost:8000/purchase-requests/create
# Should show form with department selector, PR form fields, line items table
```

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Web/PurchaseRequestController.php \
        resources/views/purchase-requests/
git commit -m "feat: add PurchaseRequestController and views (index, create, show)"
```

---

### Task 6: PurchaseOrderController + Views

**Files:**
- Create: `app/Http/Controllers/Web/PurchaseOrderController.php`
- Create: `resources/views/purchase-orders/index.blade.php`
- Create: `resources/views/purchase-orders/create.blade.php`
- Create: `resources/views/purchase-orders/show.blade.php`

- [ ] **Step 1: Create PurchaseOrderController**

```php
<?php
// app/Http/Controllers/Web/PurchaseOrderController.php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApprovalInstance;
use App\Models\ApprovalInstanceStep;
use App\Models\DocumentForm;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Services\ApprovalFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) (session('user.id') ?? 0);
        $status = $request->query('status');
        if ($status !== null && $status !== '' && ! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = null;
        }

        $myInstances = ApprovalInstance::query()
            ->where('document_type', 'purchase_order')
            ->where('requester_user_id', $userId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase-orders.index', compact('myInstances', 'status'));
    }

    public function create(Request $request): View
    {
        $prInstance  = null;
        $prLineItems = collect();

        if ($fromPrId = $request->query('from_pr')) {
            $prInstance = ApprovalInstance::find($fromPrId);
            if ($prInstance) {
                abort_unless($prInstance->document_type === 'purchase_request', 422);
                if ($prInstance->status !== 'approved') {
                    return redirect()->route('purchase-requests.show', $prInstance)
                        ->withErrors(['workflow' => __('common.pr_not_approved')]);
                }
                $exists = ApprovalInstance::where('document_type', 'purchase_order')
                    ->whereRaw("json_extract(payload, '$.purchase_request_id') = ?", [$prInstance->id])
                    ->exists();
                if ($exists) {
                    return redirect()->route('purchase-requests.show', $prInstance)
                        ->withErrors(['workflow' => __('common.po_already_exists')]);
                }
                $prLineItems = PurchaseRequestItem::where('approval_instance_id', $prInstance->id)->get();
            }
        }

        $form = DocumentForm::query()
            ->with('fields')
            ->where('document_type', 'purchase_order')
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        $userId    = (int) (session('user.id') ?? 0);
        $userModel = $userId > 0 ? User::with(['company', 'branch'])->find($userId) : null;
        $company   = $userModel?->company;
        $branch    = null;
        if ($userModel && $userModel->branch && $userModel->branch->is_active
            && (int) $userModel->branch->company_id === (int) $userModel->company_id) {
            $branch = $userModel->branch;
        }

        return view('purchase-orders.create', compact('form', 'prInstance', 'prLineItems', 'company', 'branch'));
    }

    public function store(Request $request, ApprovalFlowService $approvalFlowService): RedirectResponse
    {
        $validated = $request->validate([
            'form_key'              => 'nullable|string|max:100',
            'form_payload'          => 'nullable|array',
            'purchase_request_id'   => 'nullable|integer|exists:approval_instances,id',
            'items'                 => 'required|array|min:1',
            'items.*.item_name'     => 'required|string|max:255',
            'items.*.qty'           => 'required|numeric|min:0.01',
            'items.*.unit'          => 'required|string|max:50',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.total_price'   => 'required|numeric|min:0',
            'items.*.notes'         => 'nullable|string|max:500',
        ]);

        // Guard: check PR is approved and no duplicate PO
        if ($prId = $validated['purchase_request_id'] ?? null) {
            $pr = ApprovalInstance::find($prId);
            if (! $pr || $pr->status !== 'approved') {
                return back()->withErrors(['workflow' => __('common.pr_not_approved')])->withInput();
            }
            $exists = ApprovalInstance::where('document_type', 'purchase_order')
                ->whereRaw("json_extract(payload, '$.purchase_request_id') = ?", [$prId])
                ->exists();
            if ($exists) {
                return back()->withErrors(['workflow' => __('common.po_already_exists')])->withInput();
            }
        }

        $payload     = $validated['form_payload'] ?? [];
        $totalAmount = array_sum(array_column($validated['items'], 'total_price'));

        // Store PR reference in payload for linking
        if ($prId && isset($pr)) {
            $payload['purchase_request_id']  = $pr->id;
            $payload['parent_reference']     = $pr->reference_no ?? 'PR#'.$pr->id;
        }

        try {
            $instance = $approvalFlowService->start(
                'purchase_order',
                null,
                (int) (session('user.id') ?? 1),
                null,
                $payload,
                $validated['form_key'] ?? null,
                $totalAmount > 0 ? (float) $totalAmount : null
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['workflow' => $this->workflowErrorMessage($e)])->withInput();
        }

        foreach ($validated['items'] as $item) {
            PurchaseOrderItem::create([
                'approval_instance_id' => $instance->id,
                'item_name'   => $item['item_name'],
                'qty'         => $item['qty'],
                'unit'        => $item['unit'],
                'unit_price'  => $item['unit_price'],
                'total_price' => $item['total_price'],
                'notes'       => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('purchase-orders.show', $instance)->with('success', __('common.saved'));
    }

    public function show(ApprovalInstance $instance): View
    {
        abort_unless($instance->document_type === 'purchase_order', 404);
        $this->authorizeViewInstance($instance);

        $instance->load(['steps.actor', 'workflow', 'requester.company', 'requester.branch']);
        $userId = (int) (session('user.id') ?? 0);

        $lineItems = PurchaseOrderItem::where('approval_instance_id', $instance->id)->get();

        $formForLabels = DocumentForm::query()->with('fields')
            ->where('document_type', 'purchase_order')->where('is_active', true)->orderBy('id')->first();
        $formFields = $formForLabels?->fields ?? collect();

        $editorRole = $this->resolveEditorRole($instance, $userId);

        $canAct = false;
        if ($instance->status === 'pending' && in_array('approval.approve', session('user_permissions', []), true)) {
            $currentStep = $instance->steps->firstWhere('step_no', $instance->current_step_no);
            if ($currentStep && $currentStep->action === 'pending') {
                $canAct = $this->userCanActStep($currentStep, $userId);
            }
        }

        // Link back to source PR
        $sourcePr = null;
        if ($prId = $instance->payload['purchase_request_id'] ?? null) {
            $sourcePr = ApprovalInstance::find($prId);
        }

        $requester = $instance->requester;
        $company   = $requester?->company;
        $branch    = null;
        if ($requester && $requester->branch && $requester->branch->is_active
            && (int) $requester->branch->company_id === (int) $requester->company_id) {
            $branch = $requester->branch;
        }

        return view('purchase-orders.show', compact(
            'instance', 'lineItems', 'canAct', 'sourcePr',
            'formFields', 'formForLabels', 'editorRole', 'company', 'branch'
        ));
    }

    private function resolveEditorRole(ApprovalInstance $instance, int $userId): string
    {
        if ($instance->status !== 'pending') return 'view_only';
        $currentStep = $instance->steps->firstWhere('step_no', $instance->current_step_no);
        if ($currentStep && $currentStep->action === 'pending' && $this->userCanActStep($currentStep, $userId)) {
            return 'step_'.$instance->current_step_no;
        }
        return 'view_only';
    }

    private function authorizeViewInstance(ApprovalInstance $instance): void
    {
        if (session('user.is_super_admin', false)) return;
        $uid = (int) (session('user.id') ?? 0);
        if ($instance->requester_user_id === $uid) return;
        if (in_array('approval.approve', session('user_permissions', []), true)) return;
        abort(403);
    }

    private function userCanActStep(ApprovalInstanceStep $step, int $userId): bool
    {
        $user = User::find($userId);
        if (! $user) return false;
        if ($step->approver_type === 'user')     return (string) $step->approver_ref === (string) $userId;
        if ($step->approver_type === 'position') return $user->position_id && (string) $step->approver_ref === (string) $user->position_id;
        return $user->hasRole($step->approver_ref);
    }

    private function workflowErrorMessage(RuntimeException $e): string
    {
        $msg = $e->getMessage();
        return match (true) {
            str_contains($msg, 'Amount is required for amount-based') => __('common.workflow_error_amount_required'),
            str_contains($msg, 'No matching amount range')            => __('common.workflow_error_no_amount_range'),
            str_contains($msg, 'Department is required')              => __('common.workflow_error_department_required'),
            str_contains($msg, 'No workflow binding found')           => __('common.workflow_error_no_binding'),
            str_contains($msg, 'Workflow is not configured')          => __('common.workflow_error_not_configured'),
            default                                                   => __('common.workflow_error_generic'),
        };
    }
}
```

- [ ] **Step 2: Create index view**

```blade
{{-- resources/views/purchase-orders/index.blade.php --}}
@extends('layouts.app')
@section('title', __('common.purchase_orders'))
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.purchase_orders') }}</h2>
    </div>

    <div class="flex gap-2 mb-4">
        @foreach ([null => __('common.all'), 'pending' => __('common.status_pending'), 'approved' => __('common.status_approved'), 'rejected' => __('common.status_rejected')] as $val => $label)
            <a href="{{ route('purchase-orders.index', $val ? ['status' => $val] : []) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $status === $val ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($myInstances->isEmpty())
            <p class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">{{ __('common.no_purchase_orders') }}</p>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('common.reference_no') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.pr_reference') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.created_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($myInstances as $instance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('purchase-orders.show', $instance) }}"
                                   class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                    {{ $instance->reference_no ?? '#'.$instance->id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $instance->payload['parent_reference'] ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @include('components.status-badge', ['status' => $instance->status])
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $instance->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $myInstances->links() }}
            </div>
        @endif
    </div>
@endsection
```

- [ ] **Step 3: Create create view (pre-fillable from PR)**

```blade
{{-- resources/views/purchase-orders/create.blade.php --}}
@extends('layouts.app')
@section('title', __('common.create_purchase_order'))
@section('content')
    <div class="mb-6">
        <a href="{{ $prInstance ? route('purchase-requests.show', $prInstance) : route('purchase-orders.index') }}"
           class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">{{ __('common.create_purchase_order') }}</h2>
        @if($prInstance)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ __('common.pr_reference') }}: <span class="font-medium text-blue-600">{{ $prInstance->reference_no ?? '#'.$prInstance->id }}</span>
            </p>
        @endif
    </div>

    @if ($errors->has('workflow'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200">
            {{ $errors->first('workflow') }}
        </div>
    @endif

    <div x-data="poForm({{ json_encode($prLineItems->map(fn($i) => ['item_name' => $i->item_name, 'qty' => $i->qty, 'unit' => $i->unit, 'unit_price' => $i->unit_price, 'total_price' => $i->total_price, 'notes' => $i->notes ?? ''])->values()) }})" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])
            <form id="po-form" method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-3">
                @csrf
                @if($form)
                    <input type="hidden" name="form_key" value="{{ $form->form_key }}">
                @endif
                @if($prInstance)
                    <input type="hidden" name="purchase_request_id" value="{{ $prInstance->id }}">
                @endif
                @if($form)
                    @foreach($form->fields as $field)
                        @php
                            $name  = "form_payload[{$field->field_key}]";
                            $value = old("form_payload.{$field->field_key}");
                            // Pre-fill from PR payload if available
                            if ($value === null && $prInstance && isset($prInstance->payload[$field->field_key])) {
                                $value = $prInstance->payload[$field->field_key];
                            }
                        @endphp
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-300">{{ $field->label }}@if($field->is_required) <span class="text-red-500">*</span>@endif</label>
                            @include('components.dynamic-field', ['field' => $field, 'name' => $name, 'value' => $value, 'userDeptId' => null])
                        </div>
                    @endforeach
                @endif
                <input type="hidden" name="amount" :value="totalAmount">
            </form>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.line_items') }}</h4>
            <template x-for="(item, index) in items" :key="index">
                <div class="mb-3 p-3 bg-white dark:bg-gray-900/30 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
                    <div>
                        <label class="text-xs text-gray-500">{{ __('common.item_name') }}</label>
                        <input :name="'items['+index+'][item_name]'" x-model="item.item_name" required
                               class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.qty') }}</label>
                            <input :name="'items['+index+'][qty]'" x-model="item.qty" type="number" min="0.01" step="0.01"
                                   @input="updateTotal(item)" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.unit_label') }}</label>
                            <input :name="'items['+index+'][unit]'" x-model="item.unit" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.unit_price') }}</label>
                            <input :name="'items['+index+'][unit_price]'" x-model="item.unit_price" type="number" min="0" step="0.01"
                                   @input="updateTotal(item)" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                    </div>
                    <input type="hidden" :name="'items['+index+'][total_price]'" :value="item.total_price">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">{{ __('common.total_price') }}: <span x-text="Number(item.total_price).toLocaleString('th-TH',{minimumFractionDigits:2})" class="font-medium text-gray-800 dark:text-gray-200"></span></span>
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-xs text-red-500 hover:text-red-700">{{ __('common.remove') }}</button>
                    </div>
                    <input type="hidden" :name="'items['+index+'][notes]'" :value="item.notes">
                </div>
            </template>
            <button type="button" @click="addItem"
                    class="w-full py-2 text-sm text-blue-600 dark:text-blue-400 border border-dashed border-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">
                + {{ __('common.add_line_item') }}
            </button>
            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('common.total_price') }}</span>
                <span x-text="Number(totalAmount).toLocaleString('th-TH',{minimumFractionDigits:2})" class="text-lg font-bold text-blue-600 dark:text-blue-400"></span>
            </div>
            <div class="mt-4">
                <button type="submit" form="po-form"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    {{ __('common.submit') }}
                </button>
            </div>
        </div>
    </div>

    <script>
    function poForm(prefill) {
        return {
            items: prefill && prefill.length ? prefill : [{ item_name: '', qty: 1, unit: '', unit_price: 0, total_price: 0, notes: '' }],
            get totalAmount() { return this.items.reduce((s, i) => s + (parseFloat(i.total_price) || 0), 0); },
            addItem() { this.items.push({ item_name: '', qty: 1, unit: '', unit_price: 0, total_price: 0, notes: '' }); },
            removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1); },
            updateTotal(item) { item.total_price = ((parseFloat(item.qty)||0) * (parseFloat(item.unit_price)||0)).toFixed(2); },
        };
    }
    </script>
@endsection
```

- [ ] **Step 4: Create show view**

```blade
{{-- resources/views/purchase-orders/show.blade.php --}}
@extends('layouts.app')
@section('title', __('common.purchase_order') . ' ' . ($instance->reference_no ?? '#'.$instance->id))
@section('content')
    <div class="mb-6 flex items-start justify-between">
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">
                {{ __('common.purchase_order') }}: {{ $instance->reference_no ?? '#'.$instance->id }}
            </h2>
            @if($sourcePr)
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('common.pr_reference') }}:
                    <a href="{{ route('purchase-requests.show', $sourcePr) }}" class="text-blue-600 hover:underline">
                        {{ $sourcePr->reference_no ?? 'PR#'.$sourcePr->id }}
                    </a>
                </p>
            @endif
        </div>
        @include('components.status-badge', ['status' => $instance->status])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])
            @foreach($formFields as $field)
                @php $value = $instance->payload[$field->field_key] ?? null; @endphp
                @if($value !== null && $value !== '')
                    <div class="mb-3">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $field->label }}</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-0.5">{{ $value }}</dd>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="space-y-4">
            @include('components.approval-timeline', ['instance' => $instance, 'canAct' => $canAct])
        </div>
    </div>

    <div class="mt-6 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('common.line_items') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">{{ __('common.item_name') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.qty') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('common.unit_label') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.unit_price') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.total_price') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($lineItems as $i => $item)
                    <tr>
                        <td class="px-4 py-2 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-2">{{ $item->item_name }}@if($item->notes) <span class="text-xs text-gray-400 block">{{ $item->notes }}</span>@endif</td>
                        <td class="px-4 py-2 text-right">{{ number_format($item->qty, 2) }}</td>
                        <td class="px-4 py-2">{{ $item->unit }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right font-semibold text-gray-700 dark:text-gray-300">{{ __('common.total_price') }}</td>
                    <td class="px-4 py-2 text-right font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($lineItems->sum('total_price'), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Web/PurchaseOrderController.php \
        resources/views/purchase-orders/
git commit -m "feat: add PurchaseOrderController and views (index, create, show) with PR pre-fill"
```

---

### Task 7: Tests

**Files:**
- Create: `tests/Feature/PurchaseWorkflowTest.php`

- [ ] **Step 1: Write tests**

```php
<?php
// tests/Feature/PurchaseWorkflowTest.php
namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DocumentFormSeeder;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PurchaseWorkflowSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\PositionDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PurchaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_permissions_exist_after_seeding(): void
    {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);

        foreach (['view_purchase_requests', 'view_purchase_orders', 'purchase_order.create'] as $perm) {
            $this->assertTrue(
                Permission::where('name', $perm)->where('guard_name', 'web')->exists(),
                "Permission {$perm} should exist"
            );
        }
    }

    public function test_approver_role_has_view_purchase_permissions(): void
    {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);

        $role = \Spatie\Permission\Models\Role::where('name', 'approver')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('view_purchase_requests'));
        $this->assertTrue($role->hasPermissionTo('view_purchase_orders'));
    }

    public function test_document_types_seeded(): void
    {
        $this->seed([DocumentTypeSeeder::class]);

        $this->assertDatabaseHas('document_types', ['code' => 'purchase_request']);
        $this->assertDatabaseHas('document_types', ['code' => 'purchase_order']);
    }

    public function test_document_forms_seeded(): void
    {
        $this->seed([DocumentFormSeeder::class]);

        $this->assertDatabaseHas('document_forms', ['form_key' => 'purchase_request_default']);
        $this->assertDatabaseHas('document_forms', ['form_key' => 'purchase_order_default']);
    }

    public function test_purchase_workflow_seeder_creates_workflows(): void
    {
        $this->seed([
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            DocumentTypeSeeder::class,
            DocumentFormSeeder::class,
            PositionDemoSeeder::class,
            PurchaseWorkflowSeeder::class,
        ]);

        $this->assertDatabaseHas('approval_workflows', ['name' => 'PR - Small (≤50k)', 'document_type' => 'purchase_request']);
        $this->assertDatabaseHas('approval_workflows', ['name' => 'PR - Large (>50k)', 'document_type' => 'purchase_request']);
        $this->assertDatabaseHas('approval_workflows', ['name' => 'PO - Standard',     'document_type' => 'purchase_order']);
    }

    public function test_purchase_request_index_requires_auth(): void
    {
        $response = $this->get('/purchase-requests');
        $response->assertRedirect('/login');
    }

    public function test_purchase_request_items_table_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('purchase_request_items'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('purchase_order_items'));
    }
}
```

- [ ] **Step 2: Run tests**

```bash
cd backend && php artisan test --filter PurchaseWorkflowTest
```
Expected: 7 tests pass.

- [ ] **Step 3: Run full test suite**

```bash
php artisan test
```
Expected: all tests pass (ExampleTest will still fail by design — that's expected).

- [ ] **Step 4: Final commit**

```bash
git add tests/Feature/PurchaseWorkflowTest.php
git commit -m "test: add PurchaseWorkflowTest covering permissions, seeders, and table existence"
```

---

## End-to-End Verification

```bash
# 1. Seed demo data
php artisan db:seed --class=DevelopmentDemoSeeder

# 2. Serve the app
php artisan serve

# 3. Login as employee@demo.com / demo1234
#    → ไปที่ /purchase-requests/create → สร้างใบขอซื้อพร้อม line items → Submit
#    → เห็น reference_no เช่น PR-2026-00001, status = pending

# 4. Login as manager@demo.com / demo1234
#    → ไปที่ /approvals/my → เห็น PR รออนุมัติ → Approve

# 5. Login กลับ employee@demo.com
#    → PR status = approved → เห็นปุ่ม "สร้าง PO"

# 6. Login as admin@example.com (has purchase_order.create)
#    → ไปที่ /purchase-requests/{id} → กด "สร้าง PO" → form pre-fill ปรากฏ → Submit
#    → PO status = pending

# 7. Login as gm@demo.com / demo1234
#    → /approvals/my → Approve PO → status = approved

# 8. PO show page → เห็น link กลับ PR ต้นทาง
```
