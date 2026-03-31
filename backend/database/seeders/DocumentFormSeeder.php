<?php

namespace Database\Seeders;

use App\Models\DocumentForm;
use Illuminate\Database\Seeder;

class DocumentFormSeeder extends Seeder
{
    public function run(): void
    {
        $form = DocumentForm::updateOrCreate(
            ['form_key' => 'repair_request_default'],
            [
                'name' => 'ฟอร์มแจ้งซ่อม (ค่าเริ่มต้น)',
                'document_type' => 'repair_request',
                'description' => 'ฟอร์มมาตรฐานสำหรับแจ้งซ่อม — ปรับได้ที่ ตั้งค่า → ฟอร์มเอกสาร',
                'is_active' => true,
            ]
        );

        $fields = [
            ['field_key' => 'title', 'label' => 'หัวข้อ / อุปกรณ์', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 1, 'placeholder' => 'ระบุหัวข้อหรือชื่ออุปกรณ์', 'options' => null],
            ['field_key' => 'detail', 'label' => 'รายละเอียดปัญหา', 'field_type' => 'textarea', 'is_required' => true, 'sort_order' => 2, 'placeholder' => 'อาการ สถานที่ ความเร่งด่วน', 'options' => null],
            ['field_key' => 'amount', 'label' => 'ประมาณการค่าใช้จ่าย (บาท)', 'field_type' => 'number', 'is_required' => false, 'sort_order' => 3, 'placeholder' => '0.00', 'options' => null],
            ['field_key' => 'urgent_level', 'label' => 'ระดับความเร่งด่วน', 'field_type' => 'select', 'is_required' => false, 'sort_order' => 4, 'placeholder' => null, 'options' => ['ต่ำ', 'ปานกลาง', 'สูง']],
        ];

        foreach ($fields as $field) {
            $form->fields()->updateOrCreate(
                ['field_key' => $field['field_key']],
                $field
            );
        }

        // ─── PM/AM Plan form ───────────────────────────────────

        $pmForm = DocumentForm::updateOrCreate(
            ['form_key' => 'pm_am_plan_default'],
            [
                'name' => 'ฟอร์มแผนงาน PM/AM (ค่าเริ่มต้น)',
                'document_type' => 'pm_am_plan',
                'description' => 'ฟอร์มมาตรฐานสำหรับแผนงานบำรุงรักษาเชิงป้องกัน',
                'is_active' => true,
            ]
        );

        $pmFields = [
            ['field_key' => 'title', 'label' => 'หัวข้อแผนงาน', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 1, 'placeholder' => 'ระบุหัวข้อแผนงาน PM/AM', 'options' => null],
            ['field_key' => 'equipment_id', 'label' => 'อุปกรณ์/เครื่องจักร', 'field_type' => 'select', 'is_required' => true, 'sort_order' => 2, 'placeholder' => 'เลือกอุปกรณ์', 'options' => null],
            ['field_key' => 'plan_type', 'label' => 'ประเภทงาน', 'field_type' => 'select', 'is_required' => true, 'sort_order' => 3, 'placeholder' => null, 'options' => ['PM - Preventive Maintenance', 'AM - Autonomous Maintenance']],
            ['field_key' => 'scheduled_date', 'label' => 'วันที่กำหนดทำ', 'field_type' => 'date', 'is_required' => true, 'sort_order' => 4, 'placeholder' => null, 'options' => null],
            ['field_key' => 'detail', 'label' => 'รายละเอียดงาน/ขั้นตอน', 'field_type' => 'textarea', 'is_required' => true, 'sort_order' => 5, 'placeholder' => 'รายละเอียดงานที่ต้องทำ', 'options' => null],
            ['field_key' => 'estimated_hours', 'label' => 'ชั่วโมงที่คาดว่าจะใช้', 'field_type' => 'number', 'is_required' => false, 'sort_order' => 6, 'placeholder' => '0', 'options' => null],
            ['field_key' => 'amount', 'label' => 'ประมาณการค่าใช้จ่าย (บาท)', 'field_type' => 'number', 'is_required' => false, 'sort_order' => 7, 'placeholder' => '0.00', 'options' => null],
        ];

        foreach ($pmFields as $field) {
            $pmForm->fields()->updateOrCreate(
                ['field_key' => $field['field_key']],
                $field
            );
        }

        // ─── Spare Parts Requisition form ──────────────────────

        $spForm = DocumentForm::updateOrCreate(
            ['form_key' => 'spare_parts_requisition_default'],
            [
                'name' => 'ฟอร์มเบิกอะไหล่ (ค่าเริ่มต้น)',
                'document_type' => 'spare_parts_requisition',
                'description' => 'ฟอร์มมาตรฐานสำหรับเบิกอะไหล่',
                'is_active' => true,
            ]
        );

        $spFields = [
            ['field_key' => 'title', 'label' => 'หัวข้อ/เหตุผลเบิก', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 1, 'placeholder' => 'ระบุเหตุผลในการเบิกอะไหล่', 'options' => null],
            ['field_key' => 'purpose', 'label' => 'วัตถุประสงค์', 'field_type' => 'select', 'is_required' => true, 'sort_order' => 2, 'placeholder' => null, 'options' => ['ซ่อมแซม', 'บำรุงรักษา PM/AM', 'สำรองคลัง', 'อื่นๆ']],
            ['field_key' => 'parent_reference', 'label' => 'อ้างอิงใบงาน (ถ้ามี)', 'field_type' => 'text', 'is_required' => false, 'sort_order' => 3, 'placeholder' => 'เลขที่ใบแจ้งซ่อม/แผน PM', 'options' => null],
            ['field_key' => 'detail', 'label' => 'รายละเอียดเพิ่มเติม', 'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 4, 'placeholder' => 'รายละเอียดเพิ่มเติม', 'options' => null],
            ['field_key' => 'amount', 'label' => 'มูลค่ารวมประมาณ (บาท)', 'field_type' => 'number', 'is_required' => false, 'sort_order' => 5, 'placeholder' => '0.00', 'options' => null],
            ['field_key' => 'urgent_level', 'label' => 'ระดับความเร่งด่วน', 'field_type' => 'select', 'is_required' => false, 'sort_order' => 6, 'placeholder' => null, 'options' => ['ต่ำ', 'ปานกลาง', 'สูง']],
        ];

        foreach ($spFields as $field) {
            $spForm->fields()->updateOrCreate(
                ['field_key' => $field['field_key']],
                $field
            );
        }
    }
}
