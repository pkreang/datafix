# ทดสอบสิทธิ์ / บทบาท / เมนู — แนวทางที่ปลอดภัย

คำแนะทางเทคนิค: **อย่าลบสิทธิ์ (`permissions`) ทั้งตาราง** เพื่อ “เริ่มจากศูนย์” ใน DB ที่ใช้งาน — แอปออกแบบให้อิงชื่อสิทธิ์จาก seed, เมนู (`navigation_menus.permission`), และ middleware `permission:...` การเคลียร์ทั้งก้อนจะทำให้เมนูและฟีเจอร์พังเป็นลูกโซ่ และกู้ยากกว่าการรีเซ็ตแบบมีฐาน

---

## ทางเลือกที่แนะนำ (เรียงตามความเหมาะสม)

### 1) Dev / เครื่องตัวเอง — อยากฐานสะอาดและลองสร้าง user–role ใหม่

```bash
cd backend
php artisan migrate:fresh --seed
```

ได้ **permissions + roles ชุดมาตรฐาน** กลับมา (`PermissionSeeder`, `RolePermissionSeeder` ฯลฯ ตาม `DatabaseSeeder`) แล้วค่อยทดลองสร้างผู้ใช้ / ปรับบทบาทใน UI

เหมาะเมื่อ: **ยอมให้ข้อมูลทั้ง DB หาย** (ไม่ใช่ production)

---

### 2) เก็บบริษัท / ฝ่าย / ตำแหน่ง — ล้างแค่ชั้นผู้ใช้ทดสอบ

ใช้คำสั่งที่มีอยู่แล้ว (ไม่ลบ `permissions` / `roles` นิยามระบบ):

```bash
cd backend
php artisan testing:reset-user-layer --dry-run
php artisan testing:reset-user-layer --keep=admin@example.com --force
```

รายละเอียด: **`doc/uat-reset-testing-layer.md`**

เหมาะเมื่อ: มี master องค์กรที่คีย์แล้ว และอยาก **เริ่มรอบสร้างผู้ใช้ + การมอบบทบาท** โดยไม่แตะตารางสิทธิ์

---

### 3) Staging / UAT แยกจาก production

- Copy schema + ข้อมูล master ที่ต้องการไป DB ชื่อ UAT  
- ทดสอบสร้าง role / permission เพิ่ม / ผู้ใช้บน UAT เท่านั้น  
- Production **ไม่** truncate `permissions`

---

## ถ้าต้องการ “สร้างสิทธิ์เพิ่ม” เอง (ไม่ลบของเดิม)

1. สร้าง **Permission** ใหม่ใน UI (หรือ seeder เล็ก) — ใช้ naming `{module}.{action}` ให้สอดคล้อง `doc/api-spec.md`  
2. ผูกเข้า **Role** หรือ **User (custom role)** ตามแบบที่แอปรองรับ  
3. ถ้าต้องการให้เมนูแสดงตามสิทธิ์ใหม่ — แก้แถว **`navigation_menus.permission`** ให้ตรงกับชื่อสิทธิ์ (หรือ `null` ถ้าไม่จำกัด) แล้วดู sidebar หลัง **ล็อกอินใหม่** (session เก็บ `user_permissions` ตอน login)

การกรองเมนูอ้างอิง `NavigationService::getMenus()` และ `session('user_permissions')` — ดู `backend/app/Services/NavigationService.php`

---

## เมื่อทำพลาดจน permission ไม่ตรง seed

บน **dev** สามารถรันเฉพาะ seed ฐานสิทธิ์ได้ (ระวังว่าอาจ overwrite บทบาทระบบ):

```bash
cd backend
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolePermissionSeeder
```

ตรวจสอบผลกระทบกับบทบาทที่สร้างเองก่อนรันใน environment ที่มีข้อมูลสำคัญ

---

## สรุป

| ต้องการ | ทำอย่างไร |
|---------|-----------|
| ฐานสะอาดทั้งก้อน | `migrate:fresh --seed` บน dev |
| เก็บบริษัท–ฝ่าย–ตำแหน่ง | `testing:reset-user-layer` |
| ลบสิทธิ์ทั้งหมด | **ไม่แนะนำ** — ใช้สองทางบนแทน |
