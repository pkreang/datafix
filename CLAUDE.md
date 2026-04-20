# CLAUDE.md

**บทบาทไฟล์นี้:** คอนเท็กซ์ปฏิบัติการหลักสำหรับ AI / ทีม (workspace rules) — ให้ความถูกต้องของ **คำสั่ง, auth/RBAC, เมนู, ข้อควรระวัง** ที่นี่ก่อน เรื่องเล่ายาว โดเมนละเอียด และ checklist ภาษาไทยอยู่ที่ **`Summary.md`** — เมื่อพฤติกรรมระบบเปลี่ยน ควรอัปเดตทั้งสองไฟล์ให้สอดคล้อง

**ผลิตภัณฑ์:** CMMS + eForm แบบไดนามิกบน **Laravel 12** — Web: **Blade + Alpine.js + Tailwind v4** — API JSON: **Sanctum** (`backend/routes/api.php`) — **รันคำสั่ง shell/composer ทั้งหมดจากโฟลเดอร์ `backend/`** — ชื่อที่แสดงในระบบมาจาก **`APP_NAME`** (`backend/config/app.php` / `.env`)

---

## 1. ภาพรวมที่ต้องรู้

| หัวข้อ | ตำแหน่ง |
|--------|---------|
| โค้ดแอป | `backend/app/`, `backend/routes/`, `backend/resources/` |
| ลำดับ seed หลัก | `backend/database/seeders/DatabaseSeeder.php` |
| สเปก API | `doc/api-spec.md` |
| ERD | `doc/erd.md` |
| รายละเอียด seed / demo | `backend/README.md`, `Summary.md` (ส่วน seed) |

---

## 2. คำสั่งที่ใช้บ่อย

```bash
cd backend

composer setup                    # ติดตั้งแพ็กเกจ + migrate + seed
composer dev                      # เว็บเซิร์ฟเวอร์ + queue + Vite พร้อมกัน
composer test                     # รัน PHPUnit ทั้งชุด

php artisan migrate:fresh --seed  # ล้าง DB แล้ว migrate + seed ใหม่ (โปรดักชันอย่าใช้)
php artisan db:seed --class=NavigationMenuSeeder    # หลังแก้แถวเมนูใน NavigationMenuSeeder
php artisan db:seed --class=IndustryTemplateSeeder   # เทมเพลตโรงเรียน eForm เท่านั้น (ไม่รวม CMMS)
php artisan db:seed --class=FactoryCmmsTemplateSeeder # เทมเพลตโรงงาน CMMS แยกต่างหาก
php artisan test --filter ExampleTest               # ตัวอย่าง: รันเทสเฉพาะคลาส (เปลี่ยนเป็นชื่อคลาสจริง)
```

กระบวนการพิเศษ / ทางเลือก — ดู **`Summary.md`** และ **`backend/README.md`** (เช่น `DevelopmentDemoSeeder`, `RepairApprovalDemoSeeder`, `PurchaseWorkflowSeeder`, `school:workflow-test-users`, `testing:reset-user-layer`)

---

## 3. การยืนยันตัวตน (Auth)

**ไม่ใช่** web guard แบบดีฟอลต์ของ Laravel

1. `POST /login` เรียก login ภายในผ่าน API แล้วเก็บ bearer token ใน session เป็น `api_token`
2. Middleware **`AuthenticateWeb`** (`auth.web`): ไม่มี token → redirect ไป login; โหลด `User` จาก `session('user')['id']` แล้วเรียก **`Auth::setUser($user)`** ทุก request เพื่อให้ `@can()`, Spatie, `$request->user()` ทำงาน
3. Session เก็บ `user`, `user_permissions`, `user.is_super_admin` (ค่า `is_super_admin` ใน session ใช้ **แสดง UI เท่านั้น**)

**โหมดล็อกอิน (ระดับ instance):** Local, Microsoft Entra (OIDC), LDAP — ผู้ใช้แบบ JIT (สร้างเมื่อล็อกอินครั้งแรก) โค้ดอยู่ที่ `app/Services/Auth/`

---

## 4. RBAC (Spatie)

- แพ็กเกจ **Spatie Permission v7.2**, `guard_name: web`
- **บทบาทปกติ (default):** สิทธิ์ผ่าน `role_has_permissions`
- **กำหนดเองต่อคน (custom):** สิทธิ์ตรงที่ user ผ่าน `model_has_permissions` (ไม่ผ่านชั้น role)
- **Super-admin:** คอลัมน์ **`users.is_super_admin`** ใน DB → `Gate::before` ข้ามการเช็ค permission — **ไม่เทียบเท่า**การมี Spatie role ชื่อ `admin` หรือ `super-admin` อย่างเดียว

---

## 5. เมนู (Navigation)

- Sidebar มาจาก DB: **`navigation_menus`** — `NavigationService::getMenus()` กรองตามสตริง permission + กฎ super-admin สำหรับบาง route; แคชต้นไม้ **3600 วินาที** (ล้างเมื่อ model save/delete)
- คอลัมน์ **`permission`:** ต้อง **ตรงทุกตัวอักษร** กับชื่อ permission ที่ user มี; `null` = ไม่ล็อกสิทธิ์ที่เมนู (ยังอาจถูกจำกัดด้วยกฎ super-admin) — **ไม่ได้** สร้างชื่อสิทธิ์อัตโนมัติจากรูปแบบ `module.action`
- ป้ายกำกับ: `label_en` / `label_th` มี fallback จาก `lang/*/common.php` — จัดการเมนูใน UI: **`/settings/navigation`** (เฉพาะ super-admin)
- Reseed แบบกลุ่มจาก PHP: แก้ `NavigationMenuSeeder` แล้ว `php artisan db:seed --class=NavigationMenuSeeder`

---

## 6. สิทธิ์การเข้าถึง (ใช้งานจริง)

- **ชื่อ permission** เป็นสตริงธรรมดา (`user_access.read`, `manage_settings`, `approval.approve` หรือ `module.action`) — แค่สร้างแถวในเมนู Permissions **ยังไม่** ผูก route หรือเมนูจนกว่าโค้ดจะอ้างชื่อเดียวกัน (`@can`, `middleware('permission:…')`, policy, `navigation_menus.permission`)
- **ลบ permission ไม่ได้** ถ้ายังผูกกับ **role** ใดๆ หรือ **model_has_permissions** — UI จะแสดงว่า **กำลังถูกใช้งาน**; role `admin` จาก seed มักได้สิทธิ์ครบ → หลายแถวลบไม่ได้จนกว่าจะปรับ role
- **ฟอร์มเอกสาร:** flow ทั่วไป **`/forms`** ใช้การมองเห็นตามแผนกบน `DocumentForm` และแบบร่างเช็คเจ้าของ — **ไม่ใช่** รายการสร้าง–อ่าน–แก้–ลบเต็มรูปแบบ + สี่สิทธิ์ต่อฟอร์มโดยอัตโนมัติ ยกเว้นจะเพิ่ม controller/route และผูกเช็คเอง (ดูโมดูลเฉพาะ เช่น repair requests)

---

## 7. โดเมนธุรกิจ (สรุปสั้น)

| หัวข้อ | หมายเหตุ |
|--------|----------|
| **องค์กร** | Companies → branches → `users.company_id` / `branch_id` — หัวเอกสาร / lookup ใช้ FK เหล่านี้ |
| **แผนก / ตำแหน่ง** | เลือก workflow (`department_workflow_bindings`, `approver_type: position`) |
| **อนุมัติ** | `approval_workflows` → ขั้น → instances + `approval_instance_steps` — policy/range บนฟอร์ม: `ApprovalFlowService` |
| **CMMS** | อุปกรณ์ + อะไหล่ + movement |
| **ฟอร์มเอกสาร** | `document_forms` → `document_form_fields` (field-level permissions) → `document_form_submissions`; การมองเห็นผ่าน `document_form_departments` |
| **Password lifecycle** | `EnforcePasswordChange` middleware บังคับเปลี่ยนรหัส; `user_password_histories` เก็บประวัติ; `PasswordLifecycleService` + `PasswordCapabilityService` ควบคุม flow |
| **Dashboard / Reports** | `DataSourceRegistry` กำหนด data sources (repair_requests, equipment, spare_parts ฯลฯ) สำหรับ widget; `DashboardWidgetDataController` ให้ API |
| **Branch scoping** | `navigation_menus` มี branch scoping; `BranchScopingController` จัดการ isolation ตามสาขา |
| **ตั้งค่า** | ตาราง `settings` แบบ key-value — หลาย route `/settings/*` ใช้ middleware **`super-admin`** (ค่า DB) |
| **ภาษา** | `en` / `th` — ตรวจ **ทั้ง** `lang/{locale}/` และ `resources/lang/{locale}/` — JS: `lang/en.json`, `lang/th.json` |

---

## 8. ข้อควรระวัง (Gotchas)

1. **`@can` / Spatie:** ใช้ได้น่าเชื่อถือเมื่อ `AuthenticateWeb` เรียก `Auth::setUser()` แล้ว — ไม่งั้นอาจ **เช็คผิดแบบเงียบๆ** (ไม่ error แต่ได้ผลเป็น false)
2. **`<main>`** ใช้ `overflow-auto` — เมนู action แบบ absolute บนการ์ดตารางต้องใส่ **`overflow-visible`** ที่ wrapper — ห้าม `overflow-hidden`
3. **Sidebar:** spacer กว้างเท่าแถบเมนู; `<main>` **ไม่มี** `padding-left` เพิ่มสำหรับ sidebar
4. **แปลภาษา:** ใช้ต้นไม้เดียว `backend/resources/lang/` (Laravel `lang_path()` ของโปรเจกต์ชี้ที่นี่) — ไฟล์ `lang/` ที่เคยอยู่ root ถูกลบเมื่อ 2026-04-20 เพราะเป็น orphan (Laravel ไม่เคยอ่าน) — school vertical overrides อยู่ที่ `resources/lang/verticals/school/{locale}/`
5. **`ExampleTest`:** `GET /` redirect guest ไป `login`
6. **`super-admin` middleware:** ยึด **`is_super_admin` ใน DB เท่านั้น** — flag ใน session ไม่ให้สิทธิ์เข้า route; sidebar ซ่อน `/settings/*` ส่วนใหญ่จากผู้ที่ไม่ใช่ super-admin จริง — ตัวอย่าง API: `/v1/departments`, `/v1/equipment-categories`, `/v1/equipment-locations` → 403 JSON `auth.super_admin_only`
7. **ผู้ใช้ SSO:** มี `auth_provider`, `external_id`, `ldap_dn` — อย่าพึ่งพา `password`
8. **ผู้ใช้:** ใช้ **`first_name` + `last_name`** — อย่าอ้าง `users.name`
9. **`EnforcePasswordChange` middleware:** ถ้า user มี `password_change_required = true` หรือรหัสหมดอายุ จะ **redirect ไปหน้าเปลี่ยนรหัส** ก่อนเข้าหน้าอื่น — API มี `EnforcePasswordChangeForSanctum` คืน 403 JSON; ทดสอบ flow ต้องตั้งค่าฟิลด์เหล่านี้ด้วย
10. **Seeder ที่ถูกลบ:** `CompanySeeder` และ `ReportDashboardSeeder` ไม่มีแล้ว — อย่าอ้างถึง

---

## 9. ดัชนีเอกสาร

| ไฟล์ | ใช้ทำอะไร |
|------|-----------|
| `Summary.md` | ภาพรวมไทย, seed, checklist — sync กับ Auth/RBAC และข้อควรระวังที่นี่ |
| `doc/api-spec.md` | เส้นทาง REST + middleware สิทธิ์ |
| `doc/erd.md` | ความสัมพันธ์ entity |
| `doc/uat-repair-request.md` | UAT แจ้งซ่อม + workflow |
| `doc/uat-reset-testing-layer.md` | รีเซ็ต user ทดสอบ (`testing:reset-user-layer`) |
| `doc/uat-rbac-permissions.md` | ทดสอบ RBAC อย่างปลอดภัย |
| `doc/backlog.md` | งาน Phase 2+ / out-of-scope ที่คุยแล้วแต่ยังไม่ได้ลุย |
| `backend/README.md` | Seed, demo user, SSO |

**มาตรฐานทีม (เมนู + list + CRUD + audit):** เมื่อตกลงแล้ว ให้สรุปเป็น playbook ไฟล์เดียวใต้ `doc/` (เช่น `doc/menu-permissions-and-forms.md`) แล้วเพิ่ม **หนึ่งแถว** ในตารางนี้ — อย่าให้ไฟล์นี้ยาวเกินจำเป็น

---

## 10. เมื่อแก้จุดที่ชนกันบ่อย

| การเปลี่ยนแปลง | จำไว้ |
|----------------|--------|
| ลำดับเมนู / seed เมนู | `NavigationMenuSeeder` + `db:seed` หรือหน้าจัดการเมนู + cache จาก model events |
| permission ใหม่ที่ใช้ในโค้ด | เพิ่มใน `PermissionSeeder` (หรือ UI) + มอบให้ role + อาจต้อง `PermissionRegistrar::forgetCachedPermissions()` |
| route API ใหม่ | อัปเดต `doc/api-spec.md` + middleware ใน `routes/api.php` |
| เพิ่ม/แก้ฟิลด์ฟอร์มเอกสาร | ตรวจ field-level permissions ใน `document_form_fields` + `document_form_departments` visibility |
| password policy เปลี่ยน | ตรวจ `PasswordLifecycleService`, `EnforcePasswordChange` middleware, `user_password_histories` |
