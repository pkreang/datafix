# Test Plan: ใบแจ้งซ่อมเครื่องจักร NTEQ Polymer

## เตรียมตัว

```bash
cd backend && composer dev
```

> ทดสอบกับข้อมูลที่ seed ไว้ (`NteqPolymerDemoSeeder`)

---

## T-01: Login ผู้แจ้งซ่อม + เห็นฟอร์มถูกต้อง

**Login:** `somchai@nteq.test` / `Nteq1234!`

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เปิด `/forms` | เห็น "ใบแจ้งซ่อมเครื่องจักร NTEQ" | ☐ |
| 2 | ดูว่าเห็นฟอร์มอื่นไหม | **ไม่เห็น** ฟอร์มบดินทรเดชา (ใบลา/กิจกรรม) เพราะ dept ไม่ตรง | ☐ |
| 3 | คลิกเข้าฟอร์ม "ใบแจ้งซ่อมเครื่องจักร NTEQ" | เปิดหน้ากรอกฟอร์มได้ ไม่ error | ☐ |

---

## T-02: Visibility Rules — ซ่อน/โชว์ field ตามเงื่อนไข

**หน้า:** หน้ากรอกฟอร์ม (ต่อจาก T-01)

### 2A: priority → emergency_reason

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | ดู field "เหตุผลฉุกเฉิน" | **ซ่อน** (ไม่เห็นทั้ง label + input) | ☐ |
| 2 | เลือก "ระดับความเร่งด่วน" = **ฉุกเฉิน** | "เหตุผลฉุกเฉิน" **โผล่** | ☐ |
| 3 | เปลี่ยนเป็น **ปกติ** | "เหตุผลฉุกเฉิน" **ซ่อนกลับ** | ☐ |
| 4 | เปลี่ยนเป็น **เร่งด่วน** | ยังคง **ซ่อน** (ไม่ใช่ "ฉุกเฉิน") | ☐ |

### 2B: production_stopped → stop_duration

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 5 | ดู field "ระยะเวลาหยุด" | **ซ่อน** | ☐ |
| 6 | ติ๊ก "สายผลิตหยุดหรือไม่" = **หยุดแล้ว** | "ระยะเวลาหยุด" **โผล่** | ☐ |
| 7 | เอาติ๊กออก | **ซ่อนกลับ** | ☐ |

### 2C: needs_parts → parts_list

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 8 | ดู field "รายการอะไหล่" (table) | **ซ่อน** | ☐ |
| 9 | ติ๊ก "ต้องการอะไหล่" = **ต้องการ** | "รายการอะไหล่" (table) **โผล่** พร้อมหัวตาราง 4 columns | ☐ |
| 10 | เอาติ๊กออก | **ซ่อนกลับ** | ☐ |

---

## T-03: Validation Rules — server-side

**กรอกข้อมูลบางส่วนก่อน:**
- priority = ปกติ
- equipment_id = เลือก `Shredder #1` (หรืออันไหนก็ได้) จาก lookup
- problem_type = เครื่องกล
- found_date = วันนี้

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | กรอก description = `สั้นมาก` (< 20 ตัวอักษร) → กด Save Draft | **error**: min 20 ตัวอักษร | ☐ |
| 2 | แก้ description ให้ยาว 20+ ตัว → กรอก estimated_cost = `600000` → Save | **error**: max 500,000 | ☐ |
| 3 | เลือก priority = ฉุกเฉิน → กรอก emergency_reason = `สั้น` (< 10 ตัว) → Save | **error**: min 10 ตัวอักษร | ☐ |
| 4 | ติ๊ก "หยุดแล้ว" → กรอก stop_duration = `800` (> 720) → Save | **error**: max 720 | ☐ |

---

## T-04: Save Draft สำเร็จ

**กรอกข้อมูลถูกต้องครบ:**

| Field | ค่า |
|---|---|
| priority | **ฉุกเฉิน** |
| emergency_reason | `เครื่องหยุดกะทันหัน ต้องรีบซ่อมก่อนกะหน้า` (≥ 10 ตัว) |
| equipment_id | `Shredder #1` (จาก lookup) |
| problem_type | `เครื่องกล` |
| description | `สายพานลำเลียงวัตถุดิบเข้าเครื่อง Shredder เส้นที่ 1 ขาดตรงจุดเชื่อมต่อ ทำให้ยางดิบตกกระจายบนพื้น` |
| found_date | วันนี้ |
| found_time | `06:30` |
| production_stopped | ติ๊ก **หยุดแล้ว** |
| stop_duration | `4` |
| estimated_cost | `25000` |
| needs_parts | ติ๊ก **ต้องการ** |
| parts_list | แถว 1: spare_part=`สายพาน Conveyor Belt 500mm` / qty=`1` / unit=`เส้น` / remark=`-` |
| | แถว 2: spare_part=`ลูกปืนแบริ่ง 6205-2RS` / qty=`4` / unit=`ตัว` / remark=`-` |
| reporter_phone | `089-123-4567` |
| reporter_signature | วาดลายมือชื่อ |

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | กด **Save Draft** | redirect ไปหน้า draft, flash "บันทึกสำเร็จ" | ☐ |
| 2 | ดูข้อมูลในหน้า draft | ทุก field แสดงค่าที่กรอกครบ | ☐ |
| 3 | "เหตุผลฉุกเฉิน" ยังแสดงอยู่ | เพราะ priority ยังเป็น "ฉุกเฉิน" | ☐ |
| 4 | "ระยะเวลาหยุด" ยังแสดง = 4 | เพราะ "หยุดแล้ว" ยังติ๊กอยู่ | ☐ |
| 5 | "รายการอะไหล่" ยังแสดง 2 แถว | เพราะ "ต้องการ" ยังติ๊กอยู่ | ☐ |

---

## T-05: ตรวจ Dual-Write (fdata table)

> ขั้นตอนนี้ใช้ terminal

```bash
php artisan tinker --execute="DB::table('nteq_maintenance')->latest('id')->first()?->status"
```

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | รันคำสั่งด้านบน | ได้ `draft` | ☐ |
| 2 | ตรวจ field values | `php artisan tinker --execute="dd(DB::table('nteq_maintenance')->latest('id')->first())"` → เห็น equipment_id (id ของ Shredder #1), priority=ฉุกเฉิน ฯลฯ | ☐ |

---

## T-06: แก้ Draft

**กลับหน้า draft ที่บันทึกไว้**

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | แก้ estimated_cost เป็น `35000` | | |
| 2 | กด Save Draft | บันทึกสำเร็จ, estimated_cost แสดง 35,000 | ☐ |
| 3 | ตรวจ fdata table | `php artisan tinker --execute="DB::table('nteq_maintenance')->latest('id')->value('estimated_cost')"` → `35000` | ☐ |

---

## T-07: Submit (ส่งอนุมัติ)

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | กด **Submit** (ส่งอนุมัติ) | confirm dialog | ☐ |
| 2 | กดยืนยัน | redirect ไปหน้า submission detail | ☐ |
| 3 | สถานะ | **pending** + มี reference number | ☐ |
| 4 | เห็น workflow status | step 1: pending (หัวหน้ากะ), step 2-3: รอ | ☐ |
| 5 | ตรวจ fdata | `php artisan tinker --execute="DB::table('nteq_maintenance')->latest('id')->first(['status','reference_no'])"` → status=submitted, reference_no มีค่า | ☐ |

---

## T-08: Approve Step 1 — หัวหน้ากะ

**Logout → Login:** `somsri@nteq.test` / `Nteq1234!`

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เปิด `/approvals/my` | เห็นใบแจ้งซ่อมที่เพิ่ง submit | ☐ |
| 2 | คลิกดูรายละเอียด | เห็นข้อมูลฟอร์มครบ (priority, machine, description ฯลฯ) | ☐ |
| 3 | กด **Approve** | อนุมัติ step 1 สำเร็จ | ☐ |
| 4 | สถานะเปลี่ยน | step 1: approved, step 2: **pending** | ☐ |

---

## T-09: Approve Step 2 — ผจก.แผนก

**Logout → Login:** `pranee@nteq.test` / `Nteq1234!`

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เปิด `/approvals/my` | เห็นใบแจ้งซ่อม (step 2 รออนุมัติ) | ☐ |
| 2 | กด **Approve** | อนุมัติ step 2 สำเร็จ | ☐ |
| 3 | สถานะ | step 2: approved, step 3: **pending** | ☐ |

---

## T-10: Approve Step 3 — ผจก.โรงงาน

**Logout → Login:** `somkit@nteq.test` / `Nteq1234!`

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เปิด `/approvals/my` | เห็นใบแจ้งซ่อม (step 3 รออนุมัติ) | ☐ |
| 2 | กด **Approve** | อนุมัติ step 3 สำเร็จ → status = **approved** | ☐ |

---

## T-11: Requester ดูผลลัพธ์

**Logout → Login:** `somchai@nteq.test` / `Nteq1234!`

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เปิด My Submissions (หรือ `/forms` → ดูรายการ) | เห็นใบแจ้งซ่อม | ☐ |
| 2 | คลิกดูรายละเอียด | status = **approved** | ☐ |
| 3 | เห็น approval history | step 1: สมศรี approved, step 2: ปราณี approved, step 3: สมคิด approved | ☐ |

---

## T-12: ทดสอบ Reject

**Login:** `somchai@nteq.test` → สร้าง draft ใหม่ (ข้อมูลง่ายๆ) → Submit

**Login:** `somsri@nteq.test` (หัวหน้ากะ)

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | เปิด `/approvals/my` | เห็นใบใหม่ | ☐ |
| 2 | กด **Reject** | ปฏิเสธสำเร็จ | ☐ |
| 3 | สถานะ = **rejected** | ไม่ไปต่อ step 2-3 | ☐ |

**Login:** `somchai@nteq.test`

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 4 | ดู My Submissions | เห็นสถานะ **rejected** | ☐ |

---

## T-13: ทดสอบ Delete Draft

**Login:** `somchai@nteq.test`

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | สร้าง draft ใหม่ → Save Draft | บันทึกสำเร็จ | ☐ |
| 2 | ตรวจ fdata table มี row ใหม่ | `DB::table('nteq_maintenance')->count()` เพิ่มขึ้น 1 | ☐ |
| 3 | กด **Delete Draft** | ลบสำเร็จ | ☐ |
| 4 | ตรวจ fdata table | row ถูกลบด้วย (count กลับเท่าเดิม) | ☐ |

---

## T-14: ทดสอบ Clone Form

**Login:** `admin@example.com`

**หน้า:** `/settings/document-forms`

| # | ทำ | คาดหวัง | ผ่าน |
|---|---|---|---|
| 1 | คลิก ⋮ ของ "ใบแจ้งซ่อมเครื่องจักร NTEQ" | เห็นเมนู: Edit, Workflow Policy, **Clone**, Delete | ☐ |
| 2 | กด **Clone** | redirect ไป edit ฟอร์มใหม่ | ☐ |
| 3 | ดู form_key | `nteq_maintenance_copy` | ☐ |
| 4 | ดู name | `ใบแจ้งซ่อมเครื่องจักร NTEQ (copy)` | ☐ |
| 5 | ดู is_active | **inactive** (ยังไม่เปิดใช้) | ☐ |
| 6 | ดู fields | ครบ 18 ตัว | ☐ |
| 7 | เปิด Advanced Settings ของ field "เหตุผลฉุกเฉิน" | visibility rule: priority = ฉุกเฉิน ยังอยู่ | ☐ |
| 8 | เปิด Advanced Settings ของ field "รายละเอียดปัญหา" | validation rule: min_length = 20 ยังอยู่ | ☐ |

---

## สรุป Test Cases

| กลุ่ม | TC | ทดสอบ | เวลา |
|---|---|---|---|
| **ดูข้อมูล** | T-01 | Login + เห็นเฉพาะฟอร์มที่ควรเห็น | 2 นาที |
| **Visibility** | T-02 | 3 visibility rules (10 ข้อย่อย) | 5 นาที |
| **Validation** | T-03 | 4 validation rules | 5 นาที |
| **Draft** | T-04, T-05, T-06 | Save + dual-write + edit | 8 นาที |
| **Submit** | T-07 | ส่งอนุมัติ + fdata update | 3 นาที |
| **Approve 3 ขั้น** | T-08, T-09, T-10 | 3 users login approve ทีละขั้น | 10 นาที |
| **ดูผล** | T-11 | Requester เห็น approved + history | 2 นาที |
| **Reject** | T-12 | Submit ใหม่ → reject | 5 นาที |
| **Delete** | T-13 | Draft + fdata ถูกลบ | 3 นาที |
| **Clone** | T-14 | Clone form + ตรวจ fields/rules | 3 นาที |

**รวม: 14 test cases, ~46 นาที**

> ถ้าพบปัญหาตรงไหน จด TC# + ข้อ# + screenshot แล้วแจ้งได้เลย
