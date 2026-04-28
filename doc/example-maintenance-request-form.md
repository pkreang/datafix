# ตัวอย่างฟอร์มใบแจ้งซ่อมระดับโรงงาน (NTEQ Maintenance Request)

**Scope:** playbook + reference template สำหรับฟอร์มแจ้งซ่อมเครื่องจักรแบบ enterprise-grade ในโรงงานอุตสาหกรรม อ้างอิงจาก CMMS มาตรฐาน (SAP PM / Maximo / Oracle eAM) — seed อยู่ในฟอร์ม `nteq_maintenance` ของระบบนี้

**Source:** `database/seeders/NteqPolymerDemoSeeder.php` — เป็นตัวอ้างอิงสำหรับสร้างฟอร์มแจ้งซ่อมของลูกค้าใหม่ (copy + ปรับ)

---

## 7 sections · 36 fields

### 1. Document header (3 fields)
| Field | Type | Required | Note |
|-------|------|----------|------|
| `reference_no` | auto_number | no | `MR{YYYYMM}-{NNNNN}` generated |
| `document_date` | date | yes | default = today, readonly |
| `priority` | lookup → `maintenance_priority` | yes | normal / urgent / emergency |
| `emergency_reason` | textarea | no | visible when `priority = emergency` |

### 2. Equipment + problem classification (3 fields)
| Field | Type | Required | Note |
|-------|------|----------|------|
| `equipment_id` | lookup → Equipment | yes | branch-scoped |
| `problem_type` | lookup → `maintenance_problem_type` | yes | electrical / mechanical / plumbing / ... |
| `description` | textarea | yes | min 20 chars |

### 3. Failure mode (2 fields)
| Field | Type | Required | Note |
|-------|------|----------|------|
| `failure_mode` | lookup → `failure_mode` | yes | not_starting / leakage / overheating / ... — **สำคัญสำหรับ Pareto analysis** |
| `is_recurring` | checkbox | no | ถ้า true → flag chronic fault ใน report |

### 4. Discovery info (2 fields)
| Field | Type | Required |
|-------|------|----------|
| `found_date` | date | yes |
| `found_time` | time | no |

### 5. Impact assessment (5 fields)
| Field | Type | Required | Note |
|-------|------|----------|------|
| `production_stopped` | checkbox | no | trigger visibility ของ `stop_duration` |
| `stop_duration` | number (0–720 hr) | no | visible when production_stopped ticked |
| `safety_impact` | lookup → `impact_severity` | yes | none / low / medium / high / critical |
| `quality_impact` | lookup → `impact_severity` | yes | |
| `environmental_impact` | lookup → `impact_severity` | yes | |

### 6. Safety / Permits (5 fields) — **critical for factory liability**
| Field | Type | Required | Note |
|-------|------|----------|------|
| `loto_required` | checkbox | no | Lock-Out Tag-Out |
| `hot_work_permit` | checkbox | no | เชื่อม/ตัด/ไฟ |
| `confined_space` | checkbox | no | ที่อับอากาศ |
| `hazards_present` | multi_select | no | 9 options (electrical, chemical, fire, height, ...) |
| `ppe_required` | multi_select | no | 8 options (helmet, glasses, gloves, harness, ...) |

### 7. Resources (6 fields)
| Field | Type | Required | Note |
|-------|------|----------|------|
| `skills_needed` | multi_select | no | electrician / mechanic / welder / ... |
| `estimated_repair_hours` | number (0–168 hr) | no | ≠ `stop_duration` (that's production side) |
| `requested_completion_date` | date | no | "อยากให้เสร็จก่อน" |
| `estimated_cost` | currency | no | 0–500,000 THB |
| `needs_parts` | checkbox | no | trigger visibility ของ `parts_list` |
| `parts_list` | table | no | columns: spare_part (lookup), qty, unit, remark |

### 8. Attachments + Sign-off (3 fields)
| Field | Type | Required | Note |
|-------|------|----------|------|
| `photos` | multi_file | no | หลายรูปพร้อมกัน (before/after/กลไกที่เสีย) |
| `reporter_phone` | phone | no | |
| `reporter_signature` | signature | no | canvas-drawn, stored as data URL |

---

## Searchable defaults (columns ในหน้า list + filter bar)

```
document_date, priority, equipment_id, problem_type,
failure_mode, is_recurring, production_stopped, safety_impact, found_date
```

Admin เปิด-ปิด `is_searchable` ได้จากหน้า form-builder `/settings/document-forms/{form}/edit`

---

## Visibility rules (conditional display)

| Target | เงื่อนไข |
|--------|---------|
| `emergency_reason` | `priority = emergency` |
| `stop_duration` | `production_stopped = หยุดแล้ว` |
| `parts_list` | `needs_parts = ต้องการ` |

---

## Lookup lists ที่เกี่ยวข้อง

Seed ใน `NteqPolymerDemoSeeder` (รายการ + items + bilingual):

| Key | Items | ใช้ที่ไหน |
|-----|-------|-----------|
| `maintenance_priority` | 3 (normal/urgent/emergency) | priority field |
| `maintenance_problem_type` | 6 | problem_type field |
| `failure_mode` | 8 | failure_mode field |
| `impact_severity` | 5 (none→critical) | safety/quality/environmental impact fields |

แก้ใน DB ได้ที่ `/settings/lookups` (super-admin)

---

## การใช้เป็น template สำหรับฟอร์มอื่น

**Copy-friendly playbook:** ถ้าต้องสร้างฟอร์มแจ้งซ่อมสำหรับลูกค้าอื่น

1. เริ่มจาก `nteq_maintenance` ใน `NteqPolymerDemoSeeder.php` — copy block `$fields = [...]`
2. ตัด sections ที่ไม่จำเป็น:
   - ไม่มี production line → ตัด `production_stopped`, `stop_duration`, `quality_impact`, `environmental_impact`
   - ไม่ต้อง safety permit → ตัด section_safety ทั้ง block
   - ไม่มี equipment registry → เปลี่ยน `equipment_id` เป็น text
3. ปรับ lookup lists ตาม domain ของลูกค้า
4. Run `composer switch:factory` (หรือ seeder ตัวใหม่) → fdata table สร้างอัตโนมัติ

---

## Field types ที่ใช้ในฟอร์มนี้

`auto_number`, `date`, `time`, `lookup`, `textarea`, `checkbox`, `number`, `currency`, `multi_select`, `section`, `table`, `multi_file`, `phone`, `signature` — ครอบคลุม field types 14 จาก 20 ของระบบ

**ที่ยังไม่ได้ใช้ในฟอร์มนี้** (มีในระบบ): `text`, `email`, `radio`, `file` (ใช้ multi_file แทน), `image` (ใช้ multi_file), `datetime`

---

## ประวัติการเปลี่ยนแปลง

- **2026-04-23** — Initial playbook (18 → 36 fields): เพิ่ม 4 sections (Failure/Impact/Safety/Resources) + multi_file สำหรับ photos + 3 lookup lists ใหม่ (failure_mode, impact_severity) + bilingual labels

## Reference (CMMS industry standards)

- SAP PM Notification Type M1 (Maintenance Request) — 5 tabs: General / Location / Items / Tasks / Activities
- IBM Maximo Service Request — includes Failure Class hierarchy + Impact severity
- ISO 55000 Asset Management — reporting requirements
