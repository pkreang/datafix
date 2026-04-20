# Backlog — งานที่ยังไม่ได้ทำ (เก็บไว้ก่อน)

ไฟล์นี้รวม Phase 2+ / "out of scope" / ideas ที่ผ่านการคุยแล้วแต่ยังไม่ได้ลุย — เรียงตามความเกี่ยวข้องเป็นกลุ่ม

## ~~Reports / Dashboards~~ (เสร็จแล้ว 2026-04-20)

- ~~**Per-form data source ใน `DataSourceRegistry`**~~ — done: auto-generate source `form:{form_key}` ต่อ active DocumentForm, aggregate/group_by/filter มาจาก field metadata, fdata_* → direct SQL columns, cache invalidate on form save
- ~~**"สร้างรายงานจากฟอร์มนี้" shortcut**~~ — done: ปุ่ม "📊 สร้างรายงาน" ใน `/settings/document-forms/{form}/edit` → สร้าง dashboard 3 widgets (count + donut by status + recent table)
- **Excel (.xlsx) export** — CSV พอใช้ได้; xlsx ต้อง `spatie/simple-excel` (~10MB) — DEFERRED ถึงลูกค้าขอ

## ~~Submission actions~~ (เสร็จแล้ว)

- **PDF binary engine** — Phase 1 browser print (`window.print()`); Phase 2 Browsershot/DomPDF — DEFERRED (ต้องเลือก library + setup server)
- ~~**Step-specific approver view**~~ — done: `authorizeView` ตรวจ approval_instance_steps (user/role/position) แทน blanket `approval.approve`
- **Email PDF** — DEFERRED (ต้องรอ PDF engine)
- ~~**Bulk actions**~~ — done: bulk-delete drafts พร้อม Alpine checkbox + toolbar (ownership check เข้มงวด)
- ~~**Activity log per action**~~ — done: `submission_activity_log` table + record on created/updated/submitted/printed/duplicated/deleted + แสดงใน show-submission view

## ~~Misc Lookup Management~~ (เสร็จแล้ว)

- ~~**Cascading UI**~~ — done: parent_id picker ใน items editor (cross-list dropdown) + registry filter by parent_id
- ~~**Bulk CSV import/export**~~ — done: export CSV with UTF-8 BOM + import with replace/append mode + validation
- ~~**Per-list permission**~~ — done: required_permission column + `LookupRegistry::accessibleSources()` + LookupController filters by user permission
- **Migrate built-in 9 ตัวเป็น DB-driven** — DEFERRED (hybrid registry ปัจจุบันทำงานดีอยู่, full migrate ความเสี่ยงสูง benefit ต่ำ)

## ~~Demo / Seed data~~ (เสร็จแล้ว)

- ~~**Pre-enable `is_searchable`**~~ — done: NteqPolymer 5 ฟิลด์ (document_date, priority, equipment_id, problem_type, found_date) + Bodindecha 8 ฟิลด์
- ~~**แยก IndustryTemplateSeeder ออกจาก factory demo**~~ — done: ลบจาก base DatabaseSeeder; เพิ่มใน `switch:school` และ `demo:reset` composer scripts
- ~~**Demo data สำหรับ reports**~~ — done: FactoryDashboardSeeder (metric + donut + bar + table widgets), เรียกใน NteqPolymerDemoSeeder; school dashboard ยังอยู่ แต่เรียกผ่าน BodindechaDemoSeeder

## ~~Navigation / UX polish~~ (บางส่วนเสร็จ)

- ~~**Theme persistence ต่อ user**~~ — done: `users.theme` column + profile dropdown + meta tag → Alpine theme store (server > localStorage > OS)
- ~~**Sidebar pinned favorites**~~ — done: `user_pinned_menus` table + toggle API + pinned section ที่ top ของ sidebar
- ~~**Pin toggle ★ button บน menu items**~~ — done 2026-04-20: `<x-sidebar-pin-button>` component + Alpine `pinnedMenus` store; renders on leaf items + group children (not on pinned-section mirrors); star-solid / star-outline icons via `<x-nav-icon>`
- **Density toggle** — DEFERRED (UX consistency pass ใหญ่)
- ~~**Breadcrumb consistency**~~ — done 2026-04-20: `<x-breadcrumb :items="[...]"` component auto-prepends Home; applied to 98 pages; slash separator, slate palette

## ~~Data model cleanup~~ (เสร็จแล้ว 2026-04-20)

- ~~`users.department` + `users.position` text columns → drop, ใช้ FK relation เท่านั้น~~ — done

## ~~Profile / Security~~ (เสร็จแล้ว)

- ~~Avatar upload~~ — done
- ~~Locale self-service toggle~~ — done
- ~~Notification preferences UI~~ — done (4 events × 2 channels matrix)
- ~~Phone number field~~ — done
- ~~Login history / last_active_at display~~ — done (LoginHistoryRecorder service + /myprofile/login-history page)
- ~~**Active sessions / revoke other devices**~~ — done (extended personal_access_tokens with ip_address/user_agent + `/myprofile/sessions` with per-token revoke + revoke-others)
- ~~**Connected SSO providers display**~~ — done (card on /myprofile showing Microsoft Entra / LDAP / Local + password-change hint + email_verified_at)
- ~~**Personal API tokens**~~ — done (`/myprofile/api-tokens` — create with name + optional expiry + one-time display + revoke)

## งานที่ยังคง DEFERRED (ต้องตัดสินใจเพิ่มเติม)

1. **PDF binary engine** (Browsershot vs DomPDF) — ต้องเลือก library + ยอมรับ Chrome Docker infra หรือ font setup ของ DomPDF
2. **Email PDF** — รอ PDF engine
3. **Excel xlsx export** — ต้อง install `spatie/simple-excel` (~10MB)
4. **Migrate built-in 9 lookups → DB** — ขัดแย้งกับ design ปัจจุบัน (hybrid ทำงานดีอยู่ — full migrate เสี่ยง)
5. **Density toggle** — UX pass ใหญ่, ต้อง design system alignment

---

> เพิ่มได้เรื่อยๆ — PR ที่ pick งานจาก backlog ให้ cross ออก (ใส่ ~~strike~~) แทนการลบแถว เพื่อเก็บ trail ของการตัดสินใจ
