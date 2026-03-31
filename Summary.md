# DataPLC / DATA FIX — สรุปโปรเจกต์ (สำหรับ Claude Code)

เอกสารนี้สรุปสถาปัตยกรรม ฟีเจอร์หลัก จุดที่ต้องระวัง และที่อยู่ของโค้ด เพื่อให้ agent อ่านก่อนแก้งาน

---

## 1. โปรเจกต์คืออะไร

- **ชื่อผลิตภัณฑ์:** DATA FIX / DataPLC — ระบบ **CMMS** (Computerized Maintenance Management System)
- **รูปแบบ:** Laravel 12 backend เดียว รองรับทั้ง
  - **Web UI:** Blade + Alpine.js + Tailwind v4 (ไม่ใช่ SPA)
  - **JSON API:** Sanctum (`routes/api.php`) สำหรับ client / mobile
- **โฟลเดอร์หลักของแอป:** `backend/` — คำสั่งทั้งหมดรันจากที่นี่

---

## 2. คำสั่งที่ใช้บ่อย

```bash
cd backend

composer setup          # ติดตั้ง + migrate + seed
composer dev            # serve + queue + vite พร้อมกัน
composer test           # PHPUnit

php artisan serve
npm run dev
php artisan migrate:fresh --seed
php artisan db:seed --class=NavigationMenuSeeder   # หลังแก้ลำดับเมนูใน seeder
```

---

## 3. Authentication (สำคัญมาก)

### Web = Session + Sanctum token (ไม่ใช้ web guard แบบดั้งเดิม)

1. Login (`POST /login`) เรียก API ภายใน `POST /api/v1/auth/login` แล้วเก็บ **bearer token** ใน session (`api_token`)
2. Middleware **`AuthenticateWeb`** (`auth.web`):
   - ถ้าไม่มี `api_token` → redirect login
   - โหลด `User` จาก `session('user')['id']` แล้ว **`Auth::setUser($user)` ทุก request** เพื่อให้ `@can()`, Spatie, `$request->user()` ทำงาน
3. Session เก็บเพิ่ม: `user`, `user_permissions`, `user.is_super_admin` (ใช้แสดง UI)

### โหมด sign-in (ตั้งค่า instance-wide ใน Settings)

- **Local:** email + password
- **Microsoft Entra (OIDC):** redirect → callback → JIT user
- **LDAP:** bind + search + JIT user

คีย์ใน `settings` (ดู `SettingSeeder`, หน้า **Settings → Authentication & SSO**):

- `auth_local_enabled`, `auth_entra_enabled`, `auth_ldap_enabled`
- `auth_local_super_admin_only`, `auth_default_role`, `auth_password_help_url`
- `auth_directory_group_role_map` — JSON map กลุ่ม directory → Spatie roles (substring match)
- Entra/LDAP host ฯลฯ (รายละเอียดใน settings UI)

**Secrets ใน `.env` เท่านั้น:** `ENTRA_CLIENT_SECRET`, `AUTH_LDAP_BIND_PASSWORD` (ดู `config/services.php`)

**ผู้ใช้จาก directory:** ฟิลด์ `users.auth_provider`, `external_id`, `ldap_dn`; เปลี่ยนรหัสในแอปถูกซ่อนตาม `PasswordCapabilityService`

### RBAC

- **Spatie Permission** (`guard_name`: `web`)
- **Super-admin จริงๆ:** `users.is_super_admin` → `Gate::before` ใน `AppServiceProvider` ให้ผ่านทุก ability
- **Session flag `is_super_admin`:** ใน `AuthController` ถือว่า role `admin` หรือ `super-admin` = true (ใช้แสดงแบนเนอร์ ฯลฯ — อย่าสับสนกับ DB flag เดียวกัน)

---

## 4. โดเมนธุรกิจที่ควรรู้

### บริษัท / สาขา / ผู้ใช้

- **`companies`:** ที่อยู่หลัก, โลโก้, ฯลฯ — แก้ที่ **Companies** (ต้องมีสิทธิ์ `manage companies`)
- **`branches`:** สาขาต่อบริษัท — จัดการในหน้าแก้ไขบริษัท (`CompanyController` branches routes)
- **`users.company_id` / `users.branch_id`:** ใช้ผูกผู้ใช้กับบริษัท/สาขา; ฟอร์มเอกสาร (เช่นแจ้งซ่อม) แสดงหัวกระดาษบริษัท/ที่อยู่จากความสัมพันธ์นี้ (และสาขาเมื่อมี `branch_id` ที่ active)

### Navigation (sidebar)

- ข้อมูลจากตาราง **`navigation_menus`**
- **`NavigationService::getMenus($permissions, $isSuperAdmin)`** — cache tree 1 ชม.; filter ตาม permission ของเมนู
- View composer ใน `AppServiceProvider` ส่ง **`$navigationMenus`** เข้า `layouts.app`
- แก้ลำดับ/โครงสร้างใน **`NavigationMenuSeeder`** แล้วรัน `db:seed --class=NavigationMenuSeeder`

### Layout / UI ที่แก้ล่าสุด (อย่าทำพัง)

- **Sidebar vs main:** เคยซ้อน spacer + `padding-left` บน main ทำให้ช่องว่างคู่ — ตอนนี้ใช้แค่ **spacer** กว้างเท่า sidebar; main **ไม่**ใส่ `sidebar-main-expanded` padding
- **`main` ใน layout:** `class="p-6 overflow-auto flex-1"` — ถ้าห่อตารางด้วย **`overflow-hidden`** จะ **ตัด dropdown** (เมนู ⋮ แก้ไข/ลบ) ให้ใช้ **`overflow-visible`** บนการ์ดตารางที่มีเมนูแบบ absolute
- หน้า **รายการบริษัท** จัดสไตล์ให้ใกล้เคียงรายการผู้ใช้ (หัวข้อ, ตาราง, แถวกระชับ, เมนู actions)

### Settings (key-value)

- โมเดล **`Setting`** + cache; นโยบายรหัสผ่าน, branding, auth, approval routing ฯลฯ

---

## 5. โครงสร้างโฟลเดอร์สำคัญ

```
backend/
├── app/Http/Controllers/Api/     # API Sanctum
├── app/Http/Controllers/Web/     # Blade
├── app/Http/Middleware/
│   ├── AuthenticateWeb.php
│   └── SetLocale.php
├── app/Services/
│   ├── NavigationService.php
│   ├── Auth/                     # AuthModeService, EntraOAuthService, LdapAuthService,
│   │                             # DirectoryUserProvisioner, DirectoryGroupRoleMapper, ...
│   └── ...
├── app/Models/
├── database/migrations/
├── database/seeders/
│   └── DatabaseSeeder.php        # ลำดับ: Permission → RolePermission → Setting →
│                                 # NavigationMenu → DocumentForm → Company → Branch →
│                                 # Department → RepairApprovalDemo
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── companies/
│   └── users/
├── resources/lang/               # แปลหลัก (en/th) — มีบางไฟล์ซ้ำใน lang/
├── routes/web.php
└── routes/api.php
```

---

## 6. Middleware / การอนุญาต route (web)

- **`auth.web`** — ต้อง login (session token)
- **`super-admin`** — เฉพาะ super-admin (หน้า settings หลายอย่าง)
- **`permission:name`** — Spatie

ตัวอย่าง: `companies` resource อยู่ใต้ `auth.web`; การแก้ไข/ลบบริษัทเช็ค **`manage companies`** ใน controller/view

---

## 7. เอกสารอ้างอิงใน repo

| ไฟล์ | เนื้อหา |
|------|---------|
| `CLAUDE.md` | คู่มือสั้นสำหรับ Claude (คำสั่ง + สถาปัตยกรรม) |
| `doc/api-spec.md` | API endpoints + permission matrix |
| `doc/erd.md` | ERD / ตารางหลัก |
| `backend/README.md` | seed, demo users, auth/SSO, navigation note |

---

## 8. การทดสอบ & ข้อจำกัด

- `php artisan test` — มี **Feature ExampleTest** ที่คาด `GET /` = 200 แต่แอป redirect ไป login (**302**) → เทสนี้อาจ fail ถ้ายังไม่แก้
- มี Unit test **`DirectoryGroupRoleMapperTest`** สำหรับ group→role mapping

---

## 9. Checklist เวลาแก้ฟีเจอร์

1. Web ที่ใช้ `@can` ต้องแน่ใจว่า **`AuthenticateWeb` ตั้ง Auth user** แล้ว (อย่าให้มีแค่ session โดยไม่มี User บน guard)
2. Dropdown ใน `<main class="overflow-auto">` + การ์ดตาราง: อย่าใช้ **`overflow-hidden`** บนตัวห่อที่ตัดเมนู
3. แก้เมนู sidebar: อัปเดต seeder + รัน **`NavigationMenuSeeder`**
4. แปล: ตรวจทั้ง **`resources/lang`** และ **`lang`** ถ้ามีคีย์ซ้ำ
5. Auth directory: อย่าลืม scope Entra **`GroupMember.Read.All`** ถ้าใช้ group mapping (ดู README)

---

## 10. เวอร์ชัน / config

- Laravel 12, Vite 7, Tailwind 4, Alpine 3, Spatie Permission v7.2, Sanctum
- Locale: `th`, `en` — middleware `SetLocale`, สลับที่ header

---

*อัปเดตล่าสุด: สรุปตามสถานะ repo ณ ช่วงที่เขียนไฟล์นี้ — ถ้าโค้ดเปลี่ยน ให้ cross-check กับ `CLAUDE.md` และ migration ล่าสุด*
