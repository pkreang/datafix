# UAT — แจ้งซ่อม (Repair request)

เอกสารนี้เป็นจุดเริ่มสำหรับทดสอบ **login → master data → workflow → ฟอร์มแจ้งซ่อม** ก่อนขยายไปประเภทเอกสารอื่น

**โค้ดอ้างอิง:** `RepairRequestController`, `ApprovalFlowService`, `document_type` = `repair_request`, ฟอร์มค่าเริ่มต้น `repair_request_default` (จาก `FactoryCmmsTemplateSeeder`)

**Route หลัก:** `GET/POST /repair-requests`, `GET /repair-requests/{id}` — ดู `backend/routes/web.php`

---

## 0. เตรียมสภาพแวดล้อม

```bash
cd backend
composer setup
# หรือมี DB แล้ว: เพิ่มเทมเพลต CMMS โรงงาน
php artisan db:seed --class=FactoryCmmsTemplateSeeder
# ตัวอย่างผู้ใช้ requester/approver (ถ้าต้องการทดสอบอนุมัติแบบ demo)
php artisan db:seed --class=RepairApprovalDemoSeeder
```

ตรวจว่ามีฟอร์มแจ้งซ่อมใน DB: ตาราง `document_forms` มี `form_key = repair_request_default` และ `document_type = repair_request`

---

## Phase A — Login

| # | ขั้นตอน | ผลที่คาด |
|---|---------|----------|
| A1 | เปิด `/login` | แสดงฟอร์ม local (หรือ SSO ตาม settings) |
| A2 | ล็อกอินด้วยผู้ใช้ที่มีสิทธิ์ยื่นแจ้งซ่อม | redirect ไป dashboard, session มี token |
| A3 | ล็อกเอาต์แล้วเข้า `/repair-requests` ตรงๆ | redirect ไป login |

---

## Phase B — Master data (ก่อนยื่นได้จริง)

ตรวจตามลำดับ (ข้ามขั้นจะได้ error workflow / ไม่มีฟอร์ม)

| # | รายการ | วิธีตรวจ (ตัวอย่าง) |
|---|--------|---------------------|
| B1 | ประเภทเอกสาร `repair_request` active | Settings → Document types |
| B2 | ฟอร์ม `repair_request_default` active + ฟิลด์ (อย่างน้อย `title`) | Settings → Document forms |
| B3 | Workflow ผูกกับฟอร์ม (global policy หรือผูกแผนก) | Settings → Document form policy / workflow |
| B4 | แผนก (ถ้า workflow แบบผูกแผนก) | ผู้ใช้มี `department_id` และมี binding `department_workflow_bindings` |
| B5 | ผู้ใช้ + บทบาท | ผู้ยื่น: สิทธิ์เข้าเมนูแจ้งซ่อม; ผู้อนุมัติ: มี `approval.approve` ถ้าทดสอบปุ่มอนุมัติ |

---

## Phase C — ฟอร์ม + การยื่น

| # | ขั้นตอน | ผลที่คาด |
|---|---------|----------|
| C1 | เปิด `/repair-requests` | เห็นฟอร์มยื่น (ถ้าไม่มีฟอร์มที่ `visibleToUser` — ตรวจการจำกัดแผนกบนฟอร์ม) |
| C2 | กรอก `title` (บังคับ) + ฟิลด์อื่นตามเทมเพลต | validation ผ่าน |
| C3 | Submit | redirect ไปหน้ารายละเอียดคำขอ, ข้อความสำเร็จ |
| C4 | รายการ “คำขอของฉัน” | แสดงรายการใหม่, กรอง status ได้ |

**รูปแบบฟอร์มต่างบริษัท:** ทำซ้ำ Phase C ต่อ **archetype** (จำนวนคอลัมน์, ฟิลด์พิเศษ, บังคับ/ไม่บังคับ) — หนึ่งแถวในตาราง UAT ต่อ archetype

---

## Phase D — Workflow (อนุมัติ / ปฏิเสธ)

| # | ขั้นตอน | ผลที่คาด |
|---|---------|----------|
| D1 | ล็อกอินเป็น **approver** ที่ตรงขั้น workflow | เห็นคำขอใน **My approvals** (`/approvals/my`) |
| D2 | อนุมัติขั้นแรก | `current_step_no` / สถานะ instance อัปเดต |
| D3 | ครบขั้นสุดท้าย | สถานะ `approved` (หรือตาม workflow หลายขั้น) |
| D4 | ล็อกอินกลับเป็น requester | เห็นสถานะสุดท้ายถูกต้อง |

ทดสอบ **ปฏิเสธ** และ **requester เป็นผู้อนุมัติได้หรือไม่** (`allow_requester_as_approver`) แยกเป็นเคสถ้าลูกค้าใช้

---

## Phase E — Regression / automated

- รัน `composer test` — รวม `RepairRequestWebTest` (submit + redirect เมื่อ seed CMMS)
- เมื่อเพิ่มฟิลด์ใหม่ในเทมเพลต: พิจารณาเพิ่ม assertion หรือ snapshot payload ตามความเสี่ยง

---

## เมื่อล้มเหลว — จุดตรวจเร็ว

| อาการ | แนวทาง |
|-------|--------|
| ข้อความ workflow แปลกๆ หลัง submit | ดู key ใน `RepairRequestController::workflowErrorMessage` / log exception เดิม |
| ไม่มีฟอร์ม | ฟอร์ม inactive หรือถูกจำกัดแผนก (`document_form_departments`) |
| Approver ไม่เห็นคำขอ | ขั้น workflow ชี้ user/position/role ไม่ตรงกับผู้ทดสอบ |
| 403 หน้ารายละเอียด | ไม่ใช่ requester และไม่มี `approval.approve` และไม่ใช่ super-admin |
