# รีเซ็ตชั้นทดสอบ (เก็บบริษัท / ฝ่าย / ตำแหน่ง)

ดูแนวทางรวมเรื่อง **ไม่ลบ `permissions` ทั้งตาราง** และทางเลือก `migrate:fresh` / UAT แยก: **`doc/uat-rbac-permissions.md`**

แนวทาง **ทางที่ 2** จากที่ปรึกษา: **ไม่ `migrate:fresh` ทั้ง DB** — เก็บ master องค์กรไว้ แล้วล้างเฉพาะข้อมูลที่สร้างระหว่างทดสอบ **ผู้ใช้ + งานที่ผูกกับผู้ใช้นั้น** เพื่อเริ่มรอบใหม่สำหรับการทดสอบ **สร้างผู้ใช้ / บทบาท / สิทธิ์**

---

## สิ่งที่เก็บไว้ (ไม่แตะ)

- `companies`, `branches`
- `departments` (ฝ่าย)
- `positions` (ตำแหน่ง)
- `roles` / `permissions` / `role_has_permissions` (นิยามระบบจาก seed — **ไม่** ลบด้วยคำสั่งนี้)
- `document_types`, `document_forms`, `approval_workflows` ฯลฯ

---

## สิ่งที่คำสั่ง `testing:reset-user-layer` ทำ

ลบผู้ใช้ที่ **ไม่อยู่ในรายการ `--keep`** (ค่าเริ่มต้นเก็บ `admin@example.com`) แบบ **force delete** พร้อมล้างข้อมูลที่ผูกก่อน:

| ลำดับ | รายการ |
|--------|--------|
| 1 | `personal_access_tokens` (Sanctum) ของผู้ใช้ที่ถูกลบ |
| 2 | `model_has_roles`, `model_has_permissions` (Spatie สำหรับ User) |
| 3 | `notification_preferences` |
| 4 | `notifications` ที่ `notifiable_type` = User |
| 5 | `approval_instances` ที่ `requester_user_id` ชี้ผู้ใช้ที่ถูกลบ (แถว `approval_instance_steps` ถูกลบตาม cascade) |
| 6 | `document_form_submissions` ที่ `user_id` ชี้ผู้ใช้ที่ถูกลบ |
| 7 | `sessions.user_id` (ถ้ามีแถวชี้ผู้ใช้นั้น) |
| 8 | `users` force delete |

**ไม่ลบ:** บทบาทที่สร้างเองใน UI — ถ้าต้องการล้างบทบาททดสอบด้วย ให้ทำในเมนูตั้งค่าหรือเขียน seeder/sql แยก (ระวัง role `is_system`)

---

## การใช้งาน

จากโฟลเดอร์ `backend/`:

```bash
# ดูว่าจะลบใครบ้าง (ไม่ลบจริง)
php artisan testing:reset-user-layer --dry-run

# ลบทุก user ยกเว้น admin@example.com (ค่าเริ่มต้น)
php artisan testing:reset-user-layer

# ไม่ถามยืนยัน (สคริปต์ / CI)
php artisan testing:reset-user-layer --force

# เก็บหลายอีเมล (เช่น bootstrap admin + บัญชี UAT ที่ต้องการคงไว้)
php artisan testing:reset-user-layer --keep=admin@example.com --keep=qa-lead@school.ac.th
```

หลังรันแล้ว: สร้างผู้ใช้ / มอบบทบาท / สิทธิ์ใหม่ผ่าน UI ตามแผนทดสอบ

---

## ข้อควรระวัง

1. **ไม่มี undo** — ใช้บน **UAT / dev** ก่อนเสมอ ไม่แนะนำบน production โดยไม่สำรอง snapshot  
2. **คำขออนุมัติ** ที่ผู้ยื่นถูกลบจะหายทั้ง instance — ถูกต้องสำหรับ “เริ่มรอบทดสอบ workflow”  
3. **ผู้ใช้ที่เหลือ** ยังอ้าง `company_id` / `department_id` / `position_id` เดิมได้ — master ไม่ถูกลบ  
4. ถ้าใช้ **SSO/LDAP JIT** ผู้ใช้อาจถูกสร้างใหม่เมื่อ login — แยกจากคำสั่งนี้

---

## อ้างอิง

- `app/Console/Commands/ResetTestingUserLayerCommand.php`
- `CLAUDE.md` / `Summary.md` — คำสั่งและ auth
