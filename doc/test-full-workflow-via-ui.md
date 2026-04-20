# Full Workflow Test — สร้างข้อมูลทดสอบผ่าน UI

## สถานการณ์จำลอง: บริษัทโรงงาน

สมมติเป็นโรงงานผลิตอาหาร มี 3 แผนก, 4 ตำแหน่ง, 5 users, workflow อนุมัติ 2 ขั้น, ฟอร์มแจ้งซ่อมที่ครอบคลุม field types หลากหลาย

---

## Step 1: สร้าง Document Type ใหม่

**หน้า:** `/settings/document-types` → กด Add

| Field | ค่า |
|---|---|
| Code | `maintenance_request` |
| Name (EN) | Maintenance Request |
| Name (TH) | ใบแจ้งซ่อม |
| Active | ติ๊ก |

กด Save

---

## Step 2: สร้างแผนก (3 แผนก)

**หน้า:** `/settings/departments` → กด Add แต่ละแผนก

| # | Code | Name | Remark |
|---|---|---|---|
| 1 | `PROD` | ฝ่ายผลิต | สายผลิตทั้งหมด |
| 2 | `MAINT` | ฝ่ายซ่อมบำรุง | ดูแลเครื่องจักร |
| 3 | `MGMT` | ฝ่ายบริหาร | ผู้บริหาร |

---

## Step 3: สร้างตำแหน่ง (4 ตำแหน่ง)

**หน้า:** `/settings/positions` → กด Add แต่ละตำแหน่ง

| # | Code | Name |
|---|---|---|
| 1 | `OPERATOR` | พนักงานผลิต |
| 2 | `SUPERVISOR` | หัวหน้างาน |
| 3 | `MAINT_LEAD` | หัวหน้าช่างซ่อม |
| 4 | `PLANT_MGR` | ผู้จัดการโรงงาน |

---

## Step 4: สร้าง Users (5 คน)

**หน้า:** `/users` → กด Add แต่ละคน

| # | Email | First Name | Last Name | Department | Position | Role | Password |
|---|---|---|---|---|---|---|---|
| 1 | requester@test.com | สมชาย | ผลิตดี | ฝ่ายผลิต | พนักงานผลิต | (default) | `Test1234!` |
| 2 | supervisor@test.com | สมหญิง | คุมงาน | ฝ่ายผลิต | หัวหน้างาน | (default) | `Test1234!` |
| 3 | maint.lead@test.com | ช่างใหญ่ | ซ่อมเก่ง | ฝ่ายซ่อมบำรุง | หัวหน้าช่างซ่อม | (default) | `Test1234!` |
| 4 | manager@test.com | ผู้จัดการ | ใหญ่มาก | ฝ่ายบริหาร | ผู้จัดการโรงงาน | (default) | `Test1234!` |
| 5 | **admin@example.com** | (แก้ไข) | — | ฝ่ายบริหาร | ผู้จัดการโรงงาน | super-admin | (ไม่เปลี่ยน) |

> **สำคัญ:** แก้ admin ให้มี department=ฝ่ายบริหาร + position=ผู้จัดการโรงงาน

---

## Step 5: สร้าง Approval Workflow

**หน้า:** `/settings/workflow` → กด Add

**Workflow: "อนุมัติแจ้งซ่อม — 2 ขั้น"**

| Field | ค่า |
|---|---|
| Name | อนุมัติแจ้งซ่อม — 2 ขั้น |
| Document Type | maintenance_request |

กด Save → แล้วเพิ่ม Stages:

| Step | Approver Type | Approver Ref | Min Approvals |
|---|---|---|---|
| 1 | **Position** | หัวหน้างาน (SUPERVISOR) | 1 |
| 2 | **Position** | ผู้จัดการโรงงาน (PLANT_MGR) | 1 |

> Flow: พนักงาน submit → หัวหน้างานอนุมัติ → ผู้จัดการอนุมัติ → เสร็จสิ้น

---

## Step 6: ผูก Workflow กับ Department

**หน้า:** `/settings/departments` → แก้ไข **ฝ่ายผลิต**

| Workflow Binding | ค่า |
|---|---|
| Maintenance Request | อนุมัติแจ้งซ่อม — 2 ขั้น |

กด Save

**ทำเหมือนกันกับ ฝ่ายซ่อมบำรุง:**
- Maintenance Request → อนุมัติแจ้งซ่อม — 2 ขั้น

---

## Step 7: สร้างฟอร์มเอกสาร "ใบแจ้งซ่อม"

**หน้า:** `/settings/document-forms/create`

### ข้อมูลฟอร์ม

| Field | ค่า |
|---|---|
| Form Key | `factory_maintenance` |
| Name | ใบแจ้งซ่อมเครื่องจักร |
| Document Type | maintenance_request |
| Layout | 2 columns |
| Table Name | `factory_maintenance` |
| Active | ติ๊ก |

### Fields (ทดสอบหลาย field types)

| # | Key | Label | Type | Required | Options / Config | Visibility Rules | Validation |
|---|---|---|---|---|---|---|---|
| 1 | `priority` | ระดับความเร่งด่วน | **select** | Yes | `ปกติ` / `เร่งด่วน` / `ฉุกเฉิน` | — | — |
| 2 | `emergency_reason` | เหตุผลฉุกเฉิน | **textarea** | No | — | field=`priority`, op=`equals`, value=`ฉุกเฉิน` | min_length=10 |
| 3 | `equipment_name` | ชื่อเครื่องจักร | **text** | Yes | — | — | — |
| 4 | `location` | ตำแหน่งที่ตั้ง | **text** | Yes | — | — | — |
| 5 | `problem_type` | ประเภทปัญหา | **radio** | Yes | `ไฟฟ้า` / `เครื่องกล` / `ท่อ/ประปา` / `อื่นๆ` | — | — |
| 6 | `description` | รายละเอียดปัญหา | **textarea** | Yes | — | — | min_length=20 |
| 7 | — | ข้อมูลเพิ่มเติม | **section** | — | — | — | — |
| 8 | `found_date` | วันที่พบปัญหา | **date** | Yes | — | — | — |
| 9 | `found_time` | เวลาที่พบ | **time** | No | — | — | — |
| 10 | `estimated_cost` | ประมาณค่าใช้จ่าย (บาท) | **currency** | No | — | — | min=0, max=500000 |
| 11 | `photo` | ภาพถ่ายประกอบ | **file** | No | — | — | — |
| 12 | `reporter_phone` | เบอร์โทรผู้แจ้ง | **phone** | No | — | — | — |
| 13 | `needs_parts` | ต้องการอะไหล่ | **checkbox** | No | `ใช่` | — | — |
| 14 | `parts_list` | รายการอะไหล่ | **table** | No | columns: `name`(text), `qty`(number), `unit`(text) | field=`needs_parts`, op=`equals`, value=`ใช่` | — |
| 15 | `reporter_signature` | ลายมือชื่อผู้แจ้ง | **signature** | No | — | — | — |

กด Save

---

## Step 8: ทดสอบกรอกฟอร์ม (Login เป็น requester)

### 8.1 Logout จาก admin → Login เป็น `requester@test.com` / `Test1234!`

### 8.2 เปิด `/forms` → เลือก "ใบแจ้งซ่อมเครื่องจักร"

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เห็น field ทั้งหมด (ยกเว้นที่มี visibility rules) | เห็น 13 fields (ไม่เห็น emergency_reason + parts_list) | ☐ |
| 2 | เลือก priority = **ฉุกเฉิน** | field "เหตุผลฉุกเฉิน" โผล่ขึ้น | ☐ |
| 3 | เลือก priority = **ปกติ** | field "เหตุผลฉุกเฉิน" ซ่อน | ☐ |
| 4 | ติ๊ก "ต้องการอะไหล่" = **ใช่** | field "รายการอะไหล่" (table) โผล่ | ☐ |
| 5 | เอาติ๊กออก | field "รายการอะไหล่" ซ่อน | ☐ |
| 6 | Section "ข้อมูลเพิ่มเติม" | เห็นเส้นแบ่งพร้อมข้อความ | ☐ |

### 8.3 กรอกข้อมูลทดสอบ

| Field | กรอก |
|---|---|
| priority | ฉุกเฉิน |
| emergency_reason | เครื่องหยุดกะทันหัน สายพานขาด ต้องซ่อมด่วน |
| equipment_name | เครื่องบรรจุภัณฑ์ #3 |
| location | อาคาร A ชั้น 2 |
| problem_type | เครื่องกล |
| description | สายพานลำเลียงขาดที่จุดเชื่อมต่อ ทำให้สินค้าตกกระจาย ต้องหยุดสายผลิตทันที |
| found_date | (วันนี้) |
| found_time | 08:30 |
| estimated_cost | 15000 |
| reporter_phone | 081-234-5678 |
| needs_parts | ติ๊ก "ใช่" |
| parts_list | เพิ่ม 2 แถว: (สายพาน, 1, เส้น), (ลูกปืน, 4, ตัว) |
| reporter_signature | วาดลายมือชื่อ |

### 8.4 กด Save Draft

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | Save Draft สำเร็จ | redirect ไปหน้า draft, flash success | ☐ |
| 2 | ข้อมูลที่กรอกยังอยู่ครบ | ทุก field แสดงค่าที่กรอก | ☐ |
| 3 | ตรวจ fdata_* table (optional) | `php artisan tinker` → `DB::table('fdata_factory_maintenance')->get()` มี 1 row, status=draft | ☐ |

### 8.5 Validation ทดสอบ

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | ลบ description ให้สั้นกว่า 20 ตัว → Save | validation error (min 20) | ☐ |
| 2 | ใส่ estimated_cost = 600000 → Save | validation error (max 500000) | ☐ |
| 3 | ใส่ emergency_reason สั้นกว่า 10 ตัว → Save | validation error (min 10) | ☐ |

### 8.6 กด Submit (ส่งอนุมัติ)

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | กด Submit → ยืนยัน | status เปลี่ยนเป็น pending, ได้ reference number | ☐ |
| 2 | ดูรายละเอียด submission | เห็นข้อมูลครบ + workflow status (step 1 pending) | ☐ |

---

## Step 9: อนุมัติ Step 1 (Login เป็น supervisor)

### 9.1 Logout → Login เป็น `supervisor@test.com` / `Test1234!`

### 9.2 เปิด `/approvals/my`

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เห็นรายการรออนุมัติ | เห็นใบแจ้งซ่อมที่เพิ่ง submit | ☐ |
| 2 | คลิกดูรายละเอียด | เห็นข้อมูลฟอร์มครบ | ☐ |
| 3 | กด Approve | อนุมัติ step 1 สำเร็จ → เลื่อนไป step 2 | ☐ |

---

## Step 10: อนุมัติ Step 2 (Login เป็น manager)

### 10.1 Logout → Login เป็น `manager@test.com` / `Test1234!`

### 10.2 เปิด `/approvals/my`

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เห็นรายการรออนุมัติ | เห็นใบแจ้งซ่อม (step 2 pending) | ☐ |
| 2 | กด Approve | อนุมัติ step 2 สำเร็จ → status = **approved** | ☐ |

---

## Step 11: ตรวจสอบ requester เห็นผล

### 11.1 Logout → Login เป็น `requester@test.com`

### 11.2 เปิด "My Submissions"

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | ดูสถานะ submission | status = **approved** | ☐ |
| 2 | ดูรายละเอียด | เห็น approval history: step 1 approved by หัวหน้างาน, step 2 approved by ผู้จัดการ | ☐ |

---

## Step 12: ทดสอบ Reject flow

ทำซ้ำ Step 8 สร้าง draft ใหม่ → Submit → Login เป็น supervisor → **Reject** แทน Approve

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | supervisor กด Reject | status = **rejected** | ☐ |
| 2 | requester เห็นสถานะ rejected | แสดง rejected พร้อมเหตุผล (ถ้ามี) | ☐ |

---

## Step 13: ทดสอบ Clone Form

**Login กลับเป็น admin@example.com**

**หน้า:** `/settings/document-forms`

| # | ทดสอบ | ผลที่คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | คลิก ⋮ ของ "ใบแจ้งซ่อมเครื่องจักร" → Clone | redirect ไปหน้า edit ฟอร์มใหม่ | ☐ |
| 2 | ดูฟอร์มที่ clone | form_key = `factory_maintenance_copy`, is_active = false | ☐ |
| 3 | fields ครบ 15 ตัว | ทุก field + visibility rules + validation rules คงเดิม | ☐ |
| 4 | submission_table | เป็น null (ต้องใส่ table_name ใหม่ก่อนเปิดใช้) | ☐ |

---

## สรุป: ครอบคลุม field types อะไรบ้าง

| Field Type | ใช้ในฟอร์มทดสอบ | ทดสอบอะไร |
|---|---|---|
| text | ✅ equipment_name, location | basic input |
| textarea | ✅ description, emergency_reason | long text + validation |
| number | — | (ใช้ currency แทน) |
| currency | ✅ estimated_cost | ฿ prefix + min/max validation |
| date | ✅ found_date | date picker |
| time | ✅ found_time | time picker |
| select | ✅ priority | dropdown + visibility trigger |
| radio | ✅ problem_type | radio group |
| checkbox | ✅ needs_parts | toggle + visibility trigger |
| file | ✅ photo | file upload |
| phone | ✅ reporter_phone | tel input |
| signature | ✅ reporter_signature | canvas draw |
| table | ✅ parts_list | inline table + conditional show |
| section | ✅ ข้อมูลเพิ่มเติม | divider |
| **Visibility Rules** | ✅ 2 จุด | priority→emergency, needs_parts→parts_list |
| **Validation Rules** | ✅ 3 จุด | min_length, max currency |
| **Dual-write fdata_*** | ✅ | ตรวจ table หลัง save draft |
| **2-step approval** | ✅ | supervisor → manager |
| **Reject flow** | ✅ | supervisor reject |

### ไม่ได้ทดสอบ (เพราะต้องมี data ก่อน)

| Field Type | เหตุผล |
|---|---|
| lookup | ต้องมี equipment/spare_parts ใน DB ก่อน |
| email | ใช้ได้เหมือน text แค่มี email validation |
| datetime | ใช้ได้เหมือน date + time รวมกัน |
| number | ใช้ได้เหมือน currency แค่ไม่มี ฿ |

> ถ้าอยากทดสอบ **lookup** ด้วย → สร้าง equipment categories + equipment ก่อน (Step 3-4 ใน TC-03) แล้วเพิ่ม lookup field ในฟอร์ม
