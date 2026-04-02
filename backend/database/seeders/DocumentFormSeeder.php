<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DocumentForm;
use Illuminate\Database\Seeder;

class DocumentFormSeeder extends Seeder
{
    public function run(): void
    {
        $deptIds = Department::whereIn('code', ['MAINT', 'PROD', 'WH'])
            ->pluck('id', 'code');

        $maintId = $deptIds['MAINT'] ?? null;
        $prodId  = $deptIds['PROD']  ?? null;
        $whId    = $deptIds['WH']    ?? null;

        // ─── Repair Request Form ────────────────────────────────

        $form = DocumentForm::updateOrCreate(
            ['form_key' => 'repair_request_default'],
            [
                'name' => 'ฟอร์มแจ้งซ่อม (ค่าเริ่มต้น)',
                'document_type' => 'repair_request',
                'description' => 'ฟอร์มมาตรฐานสำหรับแจ้งซ่อม — ปรับได้ที่ ตั้งค่า → ฟอร์มเอกสาร',
                'is_active' => true,
            ]
        );

        // Feature 1A: เห็นได้เฉพาะ MAINT + PROD
        $form->departments()->sync(array_values(array_filter([$maintId, $prodId])));

        $fields = [
            [
                'field_key' => 'title',
                'label' => 'หัวข้อ / อุปกรณ์',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 1,
                'placeholder' => 'ระบุหัวข้อหรือชื่ออุปกรณ์',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'detail',
                'label' => 'รายละเอียดปัญหา',
                'field_type' => 'textarea',
                'is_required' => true,
                'sort_order' => 2,
                'placeholder' => 'อาการ สถานที่ ความเร่งด่วน',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'urgent_level',
                'label' => 'ระดับความเร่งด่วน',
                'field_type' => 'select',
                'is_required' => false,
                'sort_order' => 3,
                'placeholder' => null,
                'options' => ['ต่ำ', 'ปานกลาง', 'สูง'],
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'amount',
                'label' => 'ประมาณการค่าใช้จ่าย (บาท)',
                'field_type' => 'number',
                'is_required' => false,
                'sort_order' => 4,
                'placeholder' => '0.00',
                'options' => null,
                'editable_by' => ['requester', 'step_1'],
                'visible_to_departments' => null,
            ],
            [
                // Feature 1B: เห็นเฉพาะ MAINT; Feature 2: step_1 เท่านั้น
                'field_key' => 'technician_note',
                'label' => 'บันทึกช่าง',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 5,
                'placeholder' => 'ข้อมูลเพิ่มเติมจากช่างซ่อม',
                'options' => null,
                'editable_by' => ['step_1'],
                'visible_to_departments' => $maintId ? [$maintId] : null,
            ],
            [
                // Feature 2: step_1 กรอกสาเหตุหลังตรวจสอบ
                'field_key' => 'root_cause',
                'label' => 'สาเหตุที่แท้จริง',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 6,
                'placeholder' => 'ระบุสาเหตุที่วิเคราะห์ได้',
                'options' => null,
                'editable_by' => ['step_1'],
                'visible_to_departments' => null,
            ],
            [
                // Feature 2: step_1 กรอกวิธีแก้ไข
                'field_key' => 'solution',
                'label' => 'วิธีแก้ไข',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 7,
                'placeholder' => 'ระบุขั้นตอนหรือวิธีการแก้ไข',
                'options' => null,
                'editable_by' => ['step_1'],
                'visible_to_departments' => null,
            ],
        ];

        foreach ($fields as $field) {
            $form->fields()->updateOrCreate(['field_key' => $field['field_key']], $field);
        }

        // ─── PM/AM Plan Form ────────────────────────────────────

        $pmForm = DocumentForm::updateOrCreate(
            ['form_key' => 'pm_am_plan_default'],
            [
                'name' => 'ฟอร์มแผนงาน PM/AM (ค่าเริ่มต้น)',
                'document_type' => 'pm_am_plan',
                'description' => 'ฟอร์มมาตรฐานสำหรับแผนงานบำรุงรักษาเชิงป้องกัน',
                'is_active' => true,
            ]
        );

        // Feature 1A: เห็นได้เฉพาะ MAINT
        $pmForm->departments()->sync(array_values(array_filter([$maintId])));

        $pmFields = [
            [
                'field_key' => 'title',
                'label' => 'หัวข้อแผนงาน',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 1,
                'placeholder' => 'ระบุหัวข้อแผนงาน PM/AM',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'equipment_id',
                'label' => 'อุปกรณ์/เครื่องจักร',
                'field_type' => 'select',
                'is_required' => true,
                'sort_order' => 2,
                'placeholder' => 'เลือกอุปกรณ์',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'plan_type',
                'label' => 'ประเภทงาน',
                'field_type' => 'select',
                'is_required' => true,
                'sort_order' => 3,
                'placeholder' => null,
                'options' => ['PM - Preventive Maintenance', 'AM - Autonomous Maintenance'],
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'scheduled_date',
                'label' => 'วันที่กำหนดทำ',
                'field_type' => 'date',
                'is_required' => true,
                'sort_order' => 4,
                'placeholder' => null,
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'detail',
                'label' => 'รายละเอียดงาน/ขั้นตอน',
                'field_type' => 'textarea',
                'is_required' => true,
                'sort_order' => 5,
                'placeholder' => 'รายละเอียดงานที่ต้องทำ',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'estimated_hours',
                'label' => 'ชั่วโมงที่คาดว่าจะใช้',
                'field_type' => 'number',
                'is_required' => false,
                'sort_order' => 6,
                'placeholder' => '0',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'amount',
                'label' => 'ประมาณการค่าใช้จ่าย (บาท)',
                'field_type' => 'number',
                'is_required' => false,
                'sort_order' => 7,
                'placeholder' => '0.00',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                // Feature 2: step_1 บันทึกวันที่ทำงานจริง
                'field_key' => 'completion_date',
                'label' => 'วันที่ดำเนินการแล้วเสร็จ',
                'field_type' => 'date',
                'is_required' => false,
                'sort_order' => 8,
                'placeholder' => null,
                'options' => null,
                'editable_by' => ['step_1'],
                'visible_to_departments' => null,
            ],
            [
                // Feature 2: step_1 บันทึกผลการดำเนินงาน
                'field_key' => 'completion_note',
                'label' => 'ผลการดำเนินงาน',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 9,
                'placeholder' => 'สรุปผลงานที่ทำ ปัญหาที่พบ และข้อเสนอแนะ',
                'options' => null,
                'editable_by' => ['step_1'],
                'visible_to_departments' => null,
            ],
        ];

        foreach ($pmFields as $field) {
            $pmForm->fields()->updateOrCreate(['field_key' => $field['field_key']], $field);
        }

        // ─── Spare Parts Requisition Form ──────────────────────

        $spForm = DocumentForm::updateOrCreate(
            ['form_key' => 'spare_parts_requisition_default'],
            [
                'name' => 'ฟอร์มเบิกอะไหล่ (ค่าเริ่มต้น)',
                'document_type' => 'spare_parts_requisition',
                'description' => 'ฟอร์มมาตรฐานสำหรับเบิกอะไหล่',
                'is_active' => true,
            ]
        );

        // Feature 1A: เห็นได้เฉพาะ MAINT + PROD + WH
        $spForm->departments()->sync(array_values(array_filter([$maintId, $prodId, $whId])));

        $spFields = [
            [
                'field_key' => 'title',
                'label' => 'หัวข้อ/เหตุผลเบิก',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 1,
                'placeholder' => 'ระบุเหตุผลในการเบิกอะไหล่',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'purpose',
                'label' => 'วัตถุประสงค์',
                'field_type' => 'select',
                'is_required' => true,
                'sort_order' => 2,
                'placeholder' => null,
                'options' => ['ซ่อมแซม', 'บำรุงรักษา PM/AM', 'สำรองคลัง', 'อื่นๆ'],
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'parent_reference',
                'label' => 'อ้างอิงใบงาน (ถ้ามี)',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 3,
                'placeholder' => 'เลขที่ใบแจ้งซ่อม/แผน PM',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'detail',
                'label' => 'รายละเอียดเพิ่มเติม',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 4,
                'placeholder' => 'รายละเอียดเพิ่มเติม',
                'options' => null,
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'urgent_level',
                'label' => 'ระดับความเร่งด่วน',
                'field_type' => 'select',
                'is_required' => false,
                'sort_order' => 5,
                'placeholder' => null,
                'options' => ['ต่ำ', 'ปานกลาง', 'สูง'],
                'editable_by' => ['requester'],
                'visible_to_departments' => null,
            ],
            [
                'field_key' => 'amount',
                'label' => 'มูลค่ารวมประมาณ (บาท)',
                'field_type' => 'number',
                'is_required' => false,
                'sort_order' => 6,
                'placeholder' => '0.00',
                'options' => null,
                'editable_by' => ['requester', 'step_1'],
                'visible_to_departments' => null,
            ],
            [
                // Feature 2: step_1 บันทึกหมายเหตุ
                'field_key' => 'approver_remark',
                'label' => 'หมายเหตุผู้อนุมัติ',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 7,
                'placeholder' => 'เงื่อนไขหรือข้อกำหนดเพิ่มเติมจากผู้อนุมัติ',
                'options' => null,
                'editable_by' => ['step_1'],
                'visible_to_departments' => null,
            ],
        ];

        foreach ($spFields as $field) {
            $spForm->fields()->updateOrCreate(['field_key' => $field['field_key']], $field);
        }

        // ─── Purchase Request Form ──────────────────────────────
        $prForm = DocumentForm::updateOrCreate(
            ['form_key' => 'purchase_request_default'],
            [
                'name'          => 'ฟอร์มใบขอซื้อ (ค่าเริ่มต้น)',
                'document_type' => 'purchase_request',
                'description'   => 'ฟอร์มมาตรฐานสำหรับใบขอซื้อ',
                'is_active'     => true,
            ]
        );
        $prForm->departments()->sync([]);

        foreach ([
            ['field_key' => 'title',          'label' => 'หัวข้อ',              'field_type' => 'text',     'is_required' => true,  'sort_order' => 1, 'placeholder' => 'ระบุหัวข้อใบขอซื้อ',    'options' => null, 'editable_by' => ['requester']],
            ['field_key' => 'vendor_name',    'label' => 'ชื่อผู้ขาย',          'field_type' => 'text',     'is_required' => false, 'sort_order' => 2, 'placeholder' => 'ชื่อบริษัท/ร้านค้า',     'options' => null, 'editable_by' => ['requester']],
            ['field_key' => 'required_date',  'label' => 'วันที่ต้องการสินค้า', 'field_type' => 'date',     'is_required' => true,  'sort_order' => 3, 'placeholder' => null,                    'options' => null, 'editable_by' => ['requester']],
            ['field_key' => 'budget_code',    'label' => 'รหัสงบประมาณ',       'field_type' => 'text',     'is_required' => false, 'sort_order' => 4, 'placeholder' => 'รหัสงบประมาณ (ถ้ามี)', 'options' => null, 'editable_by' => ['requester']],
            ['field_key' => 'reason',         'label' => 'เหตุผลการขอซื้อ',    'field_type' => 'textarea', 'is_required' => true,  'sort_order' => 5, 'placeholder' => 'ระบุเหตุผลและความจำเป็น', 'options' => null, 'editable_by' => ['requester']],
            ['field_key' => 'amount',         'label' => 'มูลค่ารวม (บาท)',     'field_type' => 'number',   'is_required' => true,  'sort_order' => 6, 'placeholder' => '0.00',                 'options' => null, 'editable_by' => ['requester']],
            ['field_key' => 'approver_note',  'label' => 'หมายเหตุผู้อนุมัติ', 'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 7, 'placeholder' => null,                    'options' => null, 'editable_by' => ['step_1']],
        ] as $field) {
            $prForm->fields()->updateOrCreate(['field_key' => $field['field_key']], array_merge($field, ['visible_to_departments' => null]));
        }

        // ─── Purchase Order Form ────────────────────────────────
        $poForm = DocumentForm::updateOrCreate(
            ['form_key' => 'purchase_order_default'],
            [
                'name'          => 'ฟอร์มใบสั่งซื้อ (ค่าเริ่มต้น)',
                'document_type' => 'purchase_order',
                'description'   => 'ฟอร์มมาตรฐานสำหรับใบสั่งซื้อ',
                'is_active'     => true,
            ]
        );
        $poForm->departments()->sync([]);

        foreach ([
            ['field_key' => 'title',          'label' => 'หัวข้อ',              'field_type' => 'text',     'is_required' => true,  'sort_order' => 1, 'placeholder' => 'ระบุหัวข้อใบสั่งซื้อ', 'options' => null,                          'editable_by' => ['requester']],
            ['field_key' => 'vendor_name',    'label' => 'ชื่อผู้ขาย',          'field_type' => 'text',     'is_required' => true,  'sort_order' => 2, 'placeholder' => 'ชื่อบริษัท/ร้านค้า',   'options' => null,                          'editable_by' => ['requester']],
            ['field_key' => 'vendor_address', 'label' => 'ที่อยู่ผู้ขาย',       'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 3, 'placeholder' => 'ที่อยู่สำหรับออกใบสั่งซื้อ', 'options' => null,                   'editable_by' => ['requester']],
            ['field_key' => 'delivery_date',  'label' => 'วันที่ต้องการส่งของ', 'field_type' => 'date',     'is_required' => true,  'sort_order' => 4, 'placeholder' => null,                  'options' => null,                          'editable_by' => ['requester']],
            ['field_key' => 'payment_terms',  'label' => 'เงื่อนไขการชำระเงิน', 'field_type' => 'select',   'is_required' => true,  'sort_order' => 5, 'placeholder' => null,                  'options' => ['cash','net_30','net_60'],     'editable_by' => ['requester']],
            ['field_key' => 'amount',         'label' => 'มูลค่ารวม (บาท)',     'field_type' => 'number',   'is_required' => true,  'sort_order' => 6, 'placeholder' => '0.00',               'options' => null,                          'editable_by' => ['requester']],
            ['field_key' => 'approver_note',  'label' => 'หมายเหตุผู้อนุมัติ', 'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 7, 'placeholder' => null,                  'options' => null,                          'editable_by' => ['step_1']],
        ] as $field) {
            $poForm->fields()->updateOrCreate(['field_key' => $field['field_key']], array_merge($field, ['visible_to_departments' => null]));
        }
    }
}
