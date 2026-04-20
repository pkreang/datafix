# Test Cases — Phase 1 (Shared Components) + Phase 2 (Form Builder)

## เตรียมตัว

```bash
cd backend
composer dev
```

Login: **admin@example.com** (password ตามที่ seed ไว้)

> ข้อมูลปัจจุบัน: 4 departments, 5 positions, 0 equipment categories, 0 locations, 5 active forms

---

## TC-01: Departments List Page

**หน้า:** `/settings/departments`

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เปิดหน้า `/settings/departments` | หน้าโหลดไม่ error, เห็นตาราง 4 columns (Code, Name, Remark, Actions) | ☐ |
| 2 | ดูข้อมูลในตาราง | แสดง 4 departments ถูกต้อง | ☐ |
| 3 | คลิกเมนู ⋮ (สามจุด) ของแถวใดแถวหนึ่ง | dropdown เปิดขึ้น มี Edit + Delete พร้อม icon | ☐ |
| 4 | คลิกนอก dropdown | dropdown ปิด | ☐ |
| 5 | คลิก Edit | ไปหน้าแก้ไข department ถูกต้อง | ☐ |
| 6 | กลับมาหน้า list, คลิก Delete | แสดง confirm dialog ก่อนลบ | ☐ |
| 7 | สลับ Dark mode (คลิก icon บน header) | ตาราง, ข้อความ, พื้นหลัง เปลี่ยนสีถูกต้อง ไม่มีส่วนที่อ่านไม่ออก | ☐ |

---

## TC-02: Positions List Page

**หน้า:** `/settings/positions`

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เปิดหน้า `/settings/positions` | ตาราง 5 columns (Code, Name, Remark, **Status**, Actions) | ☐ |
| 2 | ดู Status column | แสดง badge สีเขียว "Active" หรือสีเทา "Inactive" | ☐ |
| 3 | คลิกเมนู ⋮ | dropdown เปิด มี Edit + Delete | ☐ |
| 4 | สลับ Dark mode | badge สีถูกต้อง, ตารางอ่านได้ | ☐ |

---

## TC-03: Equipment Categories List Page (ข้อมูลว่าง)

**หน้า:** `/settings/equipment`

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เปิดหน้า `/settings/equipment` | หน้าโหลดไม่ error | ☐ |
| 2 | เห็น breadcrumb | "Settings / Equipment" | ☐ |
| 3 | เห็น empty state | ข้อความ "ไม่มีข้อมูล" + ปุ่มสร้างใหม่ | ☐ |
| 4 | คลิกปุ่ม "เพิ่มประเภทอุปกรณ์" (หรือ CTA ใน empty state) | ไปหน้า create ไม่ error | ☐ |
| 5 | กรอก Name=`Pump`, Code=`PUMP`, ติ๊ก Active, กด Save | redirect กลับ list, เห็น flash success, เห็น 1 แถว | ☐ |
| 6 | ดูแถวที่สร้าง | Name=Pump, Code=PUMP, Status=Active (badge สีเขียว) | ☐ |
| 7 | คลิก ⋮ → Edit | เปิดหน้าแก้ไข, ข้อมูลถูกต้อง | ☐ |
| 8 | คลิก ⋮ → Delete → ยืนยัน | ลบสำเร็จ, กลับเป็น empty state | ☐ |
| 9 | ทดสอบ search: สร้างใหม่ 2 ตัว (Pump, Motor) → พิมพ์ "Motor" ใน search → Enter | แสดงเฉพาะ Motor | ☐ |

---

## TC-04: Equipment Locations List Page (ข้อมูลว่าง)

**หน้า:** `/settings/equipment-locations`

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เปิดหน้า `/settings/equipment-locations` | หน้าโหลด, เห็น empty state | ☐ |
| 2 | สร้างใหม่: Name=`ตึก A ชั้น 1`, Code=`A1`, Building=`ตึก A`, Floor=`1`, Zone=`โซน A` | redirect กลับ list, เห็น 1 แถวพร้อมข้อมูลครบ 7 columns | ☐ |
| 3 | ดู columns | Name, Code, Building, Floor, Zone, Status, Actions ครบ | ☐ |
| 4 | เมนู ⋮ ทำงาน (Edit, Delete) | ทำงานปกติ | ☐ |

---

## TC-05: Form Builder — Visibility Rules UI

**หน้า:** `/settings/document-forms/create`

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เปิดหน้าสร้างฟอร์มใหม่ | เห็น 2 default fields (title, amount) | ☐ |
| 2 | ดูใต้ field แรก (title) | เห็นลิงก์ "Advanced Settings" สีน้ำเงิน | ☐ |
| 3 | คลิก "Advanced Settings" | expand ออก เห็น Visibility Rules + Validation Rules | ☐ |
| 4 | คลิก "+ Add condition" ใน Visibility Rules | เห็น row ใหม่: dropdown field, dropdown operator, input value | ☐ |
| 5 | dropdown field | แสดง field อื่นในฟอร์ม (amount) ไม่แสดงตัวเอง (title) | ☐ |
| 6 | เลือก operator "Is empty" | ช่อง value ซ่อน | ☐ |
| 7 | เลือก operator "Equals" | ช่อง value กลับมาแสดง | ☐ |
| 8 | กด × ลบ condition | condition หายไป | ☐ |
| 9 | คลิก "Advanced Settings" อีกครั้ง | collapse กลับ | ☐ |

---

## TC-06: Form Builder — Validation Rules UI

**หน้า:** `/settings/document-forms/create`

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เพิ่ม field ใหม่ type = **text** | field ถูกเพิ่ม | ☐ |
| 2 | คลิก Advanced Settings ของ field นั้น | เห็น Validation Rules: Min length, Max length, Regex pattern | ☐ |
| 3 | เปลี่ยน type เป็น **number** | Validation Rules เปลี่ยนเป็น: Min value, Max value | ☐ |
| 4 | เปลี่ยน type เป็น **select** | Validation Rules ซ่อนหมด (ไม่มี input ให้กรอก) | ☐ |
| 5 | เปลี่ยน type กลับเป็น **text**, ใส่ Min length = 5 | ค่าแสดงใน input | ☐ |

---

## TC-07: Form Builder — Save + Load Visibility/Validation Rules

**หน้า:** สร้างฟอร์มทดสอบ

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | กรอกข้อมูลฟอร์ม: form_key=`test_vis`, name=`Test Visibility`, document_type=(เลือกอันแรก), table_name=`test_visibility` | | ☐ |
| 2 | Field 1: key=`request_type`, label=`ประเภทคำขอ`, type=**select**, options: `normal` (บรรทัดแรก) `urgent` (บรรทัดที่สอง) | | ☐ |
| 3 | Field 2: key=`urgency_reason`, label=`เหตุผลเร่งด่วน`, type=**text** | | ☐ |
| 4 | Field 2 → Advanced Settings → Visibility Rules → Add condition: field=`request_type`, operator=`Equals`, value=`urgent` | | ☐ |
| 5 | Field 2 → Validation Rules → Min length = `5` | | ☐ |
| 6 | กด Save (ยืนยัน) | redirect ไม่ error, flash success | ☐ |
| 7 | เปิดแก้ไขฟอร์ม `test_vis` อีกครั้ง | | ☐ |
| 8 | Field 2 → Advanced Settings → Visibility Rules | แสดง condition: field=request_type, operator=Equals, value=urgent | ☐ |
| 9 | Field 2 → Validation Rules | Min length = 5 ยังอยู่ | ☐ |

---

## TC-08: Visibility Rules ทำงานจริง (ตอนกรอกฟอร์ม)

**หน้า:** `/forms` → เลือก "Test Visibility"

> **หมายเหตุ:** ต้องให้ department ของ user เข้าถึงฟอร์มนี้ได้ ถ้า access denied → ไปแก้ฟอร์มให้ไม่จำกัด department

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เปิดหน้ากรอกฟอร์ม Test Visibility | เห็น field "ประเภทคำขอ" (select) | ☐ |
| 2 | ดู field "เหตุผลเร่งด่วน" | **ซ่อนอยู่** (ไม่เห็นทั้ง label + input) | ☐ |
| 3 | เลือก "ประเภทคำขอ" = **urgent** | field "เหตุผลเร่งด่วน" **โผล่ขึ้นมา** (ทั้ง label + input) | ☐ |
| 4 | เลือก "ประเภทคำขอ" = **normal** | field "เหตุผลเร่งด่วน" **ซ่อนกลับ** | ☐ |
| 5 | เลือก urgent อีกครั้ง → กรอก "เหตุผลเร่งด่วน" = `ab` (2 ตัวอักษร) → กด Save Draft | | ☐ |
| 6 | ดูผลลัพธ์ | **validation error** แจ้งว่า min 5 ตัวอักษร | ☐ |
| 7 | กรอก "เหตุผลเร่งด่วน" = `เครื่องเสียด่วน` → Save Draft | บันทึกสำเร็จ, redirect ไปหน้า draft | ☐ |

---

## TC-09: Dropdown ไม่ถูกตัดโดย overflow (Regression)

**หน้า:** ทุกหน้า list ที่ refactor

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เปิด `/settings/positions` (มีหลายแถว) | | ☐ |
| 2 | คลิก ⋮ ของ **แถวสุดท้าย** ในตาราง | dropdown เปิด **ขึ้นด้านบน** ไม่ถูกตัดหายออกนอก card | ☐ |
| 3 | คลิก ⋮ ของ **แถวแรก** | dropdown เปิดเหมือนกัน ไม่หลุดออกนอกจอ | ☐ |
| 4 | ทำเหมือนกันที่ `/settings/departments` | dropdown ทำงานปกติ | ☐ |

---

## TC-10: Form Builder — แก้ไขฟอร์มที่มีอยู่แล้ว (ไม่เสียหาย)

**หน้า:** `/settings/document-forms` → แก้ไข "คำขอลา (ตัวอย่าง)"

| # | ขั้นตอน | ผลที่คาดหวัง | ผ่าน |
|---|---------|-------------|------|
| 1 | เปิดแก้ไข "คำขอลา (ตัวอย่าง)" | โหลดได้ปกติ, fields ที่มีอยู่แสดงครบ | ☐ |
| 2 | ดู Advanced Settings ของ field แรก | เห็นลิงก์, คลิกแล้ว expand, Visibility Rules ว่าง (ไม่มี condition) | ☐ |
| 3 | **ไม่แก้อะไร** → กด Save | บันทึกสำเร็จ ไม่ error, field เดิมยังครบ | ☐ |
| 4 | เปิดกรอกฟอร์ม "คำขอลา" จากหน้า `/forms` | แสดงฟอร์มปกติ ไม่มี field หาย | ☐ |

---

## สรุปลำดับทดสอบ

| ลำดับ | Test Case | เวลาโดยประมาณ | ระดับความสำคัญ |
|-------|-----------|--------------|--------------|
| 1 | TC-01 Departments | 3 นาที | สูง |
| 2 | TC-02 Positions | 2 นาที | สูง |
| 3 | TC-03 Equipment Categories | 5 นาที | สูง |
| 4 | TC-04 Equipment Locations | 3 นาที | สูง |
| 5 | TC-09 Dropdown overflow | 2 นาที | สูง |
| 6 | TC-05 Visibility Rules UI | 3 นาที | สูง |
| 7 | TC-06 Validation Rules UI | 3 นาที | สูง |
| 8 | TC-07 Save + Load Rules | 5 นาที | สูง |
| 9 | TC-10 แก้ไขฟอร์มเดิม | 3 นาที | สูง (regression) |
| 10 | **TC-08 Visibility ทำงานจริง** | 5 นาที | **สูงมาก** |

**รวม:** ~34 นาที

> ถ้าพบปัญหาใน TC ไหน หยุดแล้วแจ้งได้เลยครับ — จะแก้ให้ทันที
