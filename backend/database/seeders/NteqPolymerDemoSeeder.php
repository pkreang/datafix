<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStage;
use App\Models\Company;
use App\Models\Department;
use App\Models\DepartmentWorkflowBinding;
use App\Models\DocumentForm;
use App\Models\DocumentFormField;
use App\Models\DocumentFormWorkflowPolicy;
use App\Models\DocumentType;
use App\Models\Equipment;
use App\Models\SparePart;
use App\Models\EquipmentCategory;
use App\Models\EquipmentLocation;
use App\Models\Position;
use App\Models\User;
use App\Models\RunningNumberConfig;
use App\Services\FormSchemaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * NTEQ Polymer Co., Ltd. — โรงงานแปรรูปยางพารา จ.มุกดาหาร
 *
 * Seeds: company, departments (7), positions (8), users (8+admin),
 * equipment categories + locations + equipment, document type (maintenance_request),
 * 3-step approval workflow, maintenance form with 18 fields (visibility rules + validation),
 * and department workflow bindings.
 *
 * Idempotent (updateOrCreate). Safe to re-run.
 *
 *   php artisan db:seed --class=NteqPolymerDemoSeeder
 */
class NteqPolymerDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Company ──────────────────────────────────────
        $company = Company::updateOrCreate(
            ['code' => 'NTEQ'],
            [
                'name' => 'NTEQ Polymer Co., Ltd.',
                'tax_id' => '0495553000XXX',
                'business_type' => 'Natural Rubber Processing & Export',
                'address' => '319 หมู่ 16 ถ.ชยางกูร ต.คำป่าหลาย อ.เมือง',
                'address_province' => 'มุกดาหาร',
                'address_postal_code' => '49000',
                'phone' => '042-699-439',
                'email' => 'info@nteq-polymer.com',
                'is_active' => true,
            ]
        );
        $this->command?->info('Company: NTEQ Polymer');

        // ── 2. Document Type ────────────────────────────────
        DocumentType::updateOrCreate(
            ['code' => 'maintenance_request'],
            [
                'label_en' => 'Maintenance Request',
                'label_th' => 'ใบแจ้งซ่อม',
                'icon' => 'wrench-screwdriver',
                'sort_order' => 20,
                'routing_mode' => 'hybrid',
                'is_active' => true,
            ]
        );

        // ── 3. Departments (7) ──────────────────────────────
        $departments = [
            ['code' => 'PROD',  'name' => 'ฝ่ายผลิต',                        'description' => 'สายผลิตยางแท่ง STR/MVC — กำลังผลิต 72,000 ตัน/ปี'],
            ['code' => 'MAINT', 'name' => 'ฝ่ายซ่อมบำรุง',                    'description' => 'ดูแลเครื่องจักร ระบบไฟฟ้า ระบบควบคุม'],
            ['code' => 'QC',    'name' => 'ฝ่ายควบคุมคุณภาพ',                  'description' => 'ห้องแล็บ ทดสอบ Mooney/PRI/Plasticity — ISO/IEC 17025'],
            ['code' => 'WH',    'name' => 'ฝ่ายคลังสินค้า',                    'description' => 'วัตถุดิบ (ยางก้อน/ยางแผ่น) + สินค้าสำเร็จ (STR bales)'],
            ['code' => 'PROC',  'name' => 'ฝ่ายจัดซื้อ',                      'description' => 'จัดซื้อวัตถุดิบ อะไหล่ วัสดุสิ้นเปลือง'],
            ['code' => 'EHS',   'name' => 'ฝ่ายความปลอดภัยและสิ่งแวดล้อม',    'description' => 'ISO 14001, EcoVadis, บ่อบำบัดน้ำเสีย, จป.'],
            ['code' => 'MGMT',  'name' => 'ฝ่ายบริหาร',                       'description' => 'ผู้บริหาร บัญชี HR'],
        ];

        $deptMap = [];
        foreach ($departments as $d) {
            $dept = Department::updateOrCreate(['code' => $d['code']], ['name' => $d['name'], 'description' => $d['description']]);
            $deptMap[$d['code']] = $dept;
        }
        $this->command?->info('Departments: ' . count($departments));

        // ── 4. Positions (8) ────────────────────────────────
        $positions = [
            ['code' => 'OPERATOR',    'name' => 'พนักงานปฏิบัติการ',             'description' => 'Operator — สายผลิต/คลัง/แล็บ'],
            ['code' => 'TECHNICIAN',  'name' => 'ช่างเทคนิค',                   'description' => 'Maintenance Technician'],
            ['code' => 'LAB_TECH',    'name' => 'เจ้าหน้าที่ห้องแล็บ',           'description' => 'Lab Technician — ทดสอบ Mooney/PRI'],
            ['code' => 'SHIFT_LEAD',  'name' => 'หัวหน้ากะ',                    'description' => 'Shift Leader — อนุมัติขั้นที่ 1'],
            ['code' => 'SUPERVISOR',  'name' => 'หัวหน้างาน',                   'description' => 'Supervisor'],
            ['code' => 'DEPT_MGR',    'name' => 'ผู้จัดการแผนก',                 'description' => 'Department Manager — อนุมัติขั้นที่ 2'],
            ['code' => 'PLANT_MGR',   'name' => 'ผู้จัดการโรงงาน',               'description' => 'Plant Manager — อนุมัติขั้นสุดท้าย'],
            ['code' => 'EHS_OFFICER', 'name' => 'เจ้าหน้าที่ความปลอดภัย (จป.)', 'description' => 'EHS Officer'],
        ];

        $posMap = [];
        foreach ($positions as $p) {
            $pos = Position::updateOrCreate(['code' => $p['code']], ['name' => $p['name'], 'description' => $p['description'], 'is_active' => true]);
            $posMap[$p['code']] = $pos;
        }
        $this->command?->info('Positions: ' . count($positions));

        // ── 5. Users (8 + update admin) ─────────────────────
        $approverRole = Role::where('name', 'approver')->where('guard_name', 'web')->first();
        $viewerRole = Role::where('name', 'viewer')->where('guard_name', 'web')->first();

        $users = [
            ['email' => 'somchai@nteq.test',    'first_name' => 'สมชาย',   'last_name' => 'เดินเครื่อง', 'dept' => 'PROD',  'pos' => 'OPERATOR',    'role' => $viewerRole],
            ['email' => 'somsri@nteq.test',     'first_name' => 'สมศรี',   'last_name' => 'คุมงาน',     'dept' => 'PROD',  'pos' => 'SHIFT_LEAD',  'role' => $approverRole],
            ['email' => 'wichai@nteq.test',     'first_name' => 'วิชัย',   'last_name' => 'ซ่อมเก่ง',   'dept' => 'MAINT', 'pos' => 'SUPERVISOR',  'role' => $approverRole],
            ['email' => 'pranee@nteq.test',     'first_name' => 'ปราณี',   'last_name' => 'จัดการดี',   'dept' => 'PROD',  'pos' => 'DEPT_MGR',    'role' => $approverRole],
            ['email' => 'somkit@nteq.test',     'first_name' => 'สมคิด',   'last_name' => 'ใหญ่มาก',   'dept' => 'MGMT',  'pos' => 'PLANT_MGR',   'role' => $approverRole],
            ['email' => 'nida@nteq.test',       'first_name' => 'นิดา',   'last_name' => 'ตรวจเข้ม',   'dept' => 'QC',    'pos' => 'LAB_TECH',    'role' => $viewerRole],
            ['email' => 'preecha@nteq.test',    'first_name' => 'ปรีชา',   'last_name' => 'ปลอดภัย',   'dept' => 'EHS',   'pos' => 'EHS_OFFICER', 'role' => $approverRole],
            ['email' => 'malee@nteq.test',      'first_name' => 'มะลิ',   'last_name' => 'จัดซื้อดี',   'dept' => 'PROC',  'pos' => 'SUPERVISOR',  'role' => $approverRole],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'first_name' => $u['first_name'],
                    'last_name' => $u['last_name'],
                    'password' => 'Nteq1234!',
                    'department_id' => $deptMap[$u['dept']]->id,
                    'position_id' => $posMap[$u['pos']]->id,
                    'company_id' => $company->id,
                    'is_active' => true,
                    'is_super_admin' => false,
                ]
            );
            if ($u['role'] && ! $user->hasRole($u['role']->name)) {
                $user->syncRoles([$u['role']->name]);
            }
        }

        // Update admin user
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->update([
                'department_id' => $deptMap['MGMT']->id,
                'position_id' => $posMap['PLANT_MGR']->id,
                'company_id' => $company->id,
            ]);
        }
        $this->command?->info('Users: ' . count($users) . ' + admin updated');

        // ── 6. Equipment Categories ─────────────────────────
        $categories = [
            ['code' => 'SHRED',   'name' => 'Shredder / เครื่องสับยาง',        'description' => 'เครื่องสับย่อยยางก้อน'],
            ['code' => 'CREPER',  'name' => 'Creper / เครื่องรีดยาง',          'description' => 'เครื่องรีดแผ่นยาง'],
            ['code' => 'DRYER',   'name' => 'Dryer / เตาอบยาง',              'description' => 'เตาอบยางแท่ง STR'],
            ['code' => 'BALER',   'name' => 'Baling Press / เครื่องอัดก้อน',    'description' => 'เครื่องอัดยางเป็นก้อน 35 kg'],
            ['code' => 'CONV',    'name' => 'Conveyor / สายพานลำเลียง',       'description' => 'สายพานลำเลียงวัตถุดิบ/สินค้า'],
            ['code' => 'BOILER',  'name' => 'Boiler / หม้อไอน้ำ',             'description' => 'หม้อไอน้ำสำหรับเตาอบ'],
            ['code' => 'WWT',     'name' => 'Wastewater Treatment / ระบบบำบัดน้ำเสีย', 'description' => 'ระบบบำบัดน้ำเสียจากกระบวนการผลิต'],
            ['code' => 'FKLIFT',  'name' => 'Forklift / รถยก',               'description' => 'รถยกคลังสินค้า'],
            ['code' => 'LABINST', 'name' => 'Lab Instrument / เครื่องมือแล็บ',  'description' => 'Mooney Viscometer, Plasticity Tester'],
        ];

        $catMap = [];
        foreach ($categories as $c) {
            $cat = EquipmentCategory::updateOrCreate(['code' => $c['code']], ['name' => $c['name'], 'description' => $c['description'], 'is_active' => true]);
            $catMap[$c['code']] = $cat;
        }

        // ── 7. Equipment Locations ──────────────────────────
        $locations = [
            ['code' => 'PROD_A1', 'name' => 'อาคาร A สายผลิต 1',    'building' => 'อาคาร A', 'floor' => '1', 'zone' => 'สายผลิต STR10'],
            ['code' => 'PROD_A2', 'name' => 'อาคาร A สายผลิต 2',    'building' => 'อาคาร A', 'floor' => '1', 'zone' => 'สายผลิต STR20'],
            ['code' => 'DRY_B',   'name' => 'อาคาร B เครื่องอบ',     'building' => 'อาคาร B', 'floor' => '1', 'zone' => 'Drying'],
            ['code' => 'PACK_C',  'name' => 'อาคาร C บรรจุ',        'building' => 'อาคาร C', 'floor' => '1', 'zone' => 'Packing/Baling'],
            ['code' => 'WH_D',    'name' => 'คลังสินค้า D',          'building' => 'อาคาร D', 'floor' => '1', 'zone' => 'Warehouse'],
            ['code' => 'WWT_OUT', 'name' => 'บ่อบำบัดน้ำเสีย',       'building' => 'Outdoor', 'floor' => null, 'zone' => 'Wastewater'],
            ['code' => 'UTIL',    'name' => 'โรงไฟฟ้า/หม้อไอน้ำ',     'building' => 'Utility', 'floor' => null, 'zone' => 'Utilities'],
            ['code' => 'LAB',     'name' => 'ห้องปฏิบัติการ QC',       'building' => 'อาคาร A', 'floor' => '2', 'zone' => 'Laboratory'],
        ];

        $locMap = [];
        foreach ($locations as $l) {
            $loc = EquipmentLocation::updateOrCreate(['code' => $l['code']], ['name' => $l['name'], 'building' => $l['building'], 'floor' => $l['floor'], 'zone' => $l['zone'], 'is_active' => true]);
            $locMap[$l['code']] = $loc;
        }

        // ── 8. Equipment (sample) ───────────────────────────
        $equipmentList = [
            ['code' => 'SHRED-001', 'name' => 'Shredder #1',      'cat' => 'SHRED',   'loc' => 'PROD_A1', 'serial' => 'SH-2018-001'],
            ['code' => 'SHRED-002', 'name' => 'Shredder #2',      'cat' => 'SHRED',   'loc' => 'PROD_A2', 'serial' => 'SH-2018-002'],
            ['code' => 'CREP-001',  'name' => 'Creper Line 1',    'cat' => 'CREPER',  'loc' => 'PROD_A1', 'serial' => 'CR-2018-001'],
            ['code' => 'DRY-001',   'name' => 'Dryer Oven #1',    'cat' => 'DRYER',   'loc' => 'DRY_B',   'serial' => 'DR-2019-001'],
            ['code' => 'DRY-002',   'name' => 'Dryer Oven #2',    'cat' => 'DRYER',   'loc' => 'DRY_B',   'serial' => 'DR-2019-002'],
            ['code' => 'BALER-001', 'name' => 'Baling Press #1',  'cat' => 'BALER',   'loc' => 'PACK_C',  'serial' => 'BP-2018-001'],
            ['code' => 'CONV-001',  'name' => 'Conveyor Belt A1', 'cat' => 'CONV',    'loc' => 'PROD_A1', 'serial' => 'CB-2018-001'],
            ['code' => 'CONV-002',  'name' => 'Conveyor Belt A2', 'cat' => 'CONV',    'loc' => 'PROD_A2', 'serial' => 'CB-2018-002'],
            ['code' => 'BOIL-001',  'name' => 'Boiler #1',        'cat' => 'BOILER',  'loc' => 'UTIL',    'serial' => 'BL-2017-001'],
            ['code' => 'WWT-001',   'name' => 'Aeration Tank #1', 'cat' => 'WWT',     'loc' => 'WWT_OUT', 'serial' => 'WT-2018-001'],
            ['code' => 'FK-001',    'name' => 'Forklift Toyota 3T', 'cat' => 'FKLIFT','loc' => 'WH_D',    'serial' => 'FL-2020-001'],
            ['code' => 'MV-001',    'name' => 'Mooney Viscometer', 'cat' => 'LABINST', 'loc' => 'LAB',    'serial' => 'MV-2019-001'],
        ];

        foreach ($equipmentList as $eq) {
            Equipment::updateOrCreate(
                ['code' => $eq['code']],
                [
                    'name' => $eq['name'],
                    'equipment_category_id' => $catMap[$eq['cat']]->id,
                    'equipment_location_id' => $locMap[$eq['loc']]->id,
                    'company_id' => $company->id,
                    'serial_number' => $eq['serial'],
                    'status' => 'active',
                ]
            );
        }
        $this->command?->info('Equipment: ' . count($equipmentList) . ' items');

        // ── 8b. Spare Parts ─────────────────────────────────
        $spareParts = [
            ['code' => 'CB-001',   'name' => 'สายพาน Conveyor Belt 500mm', 'unit_cost' => 8500,  'current_stock' => 5,  'min_stock' => 2],
            ['code' => 'BRG-6205', 'name' => 'ลูกปืนแบริ่ง 6205-2RS',      'unit_cost' => 350,   'current_stock' => 20, 'min_stock' => 10],
            ['code' => 'BRG-6208', 'name' => 'ลูกปืนแบริ่ง 6208-2RS',      'unit_cost' => 550,   'current_stock' => 15, 'min_stock' => 5],
            ['code' => 'SEAL-001', 'name' => 'ซีลกันน้ำมัน Oil Seal',       'unit_cost' => 180,   'current_stock' => 30, 'min_stock' => 10],
            ['code' => 'BLD-001',  'name' => 'ใบมีด Shredder Blade',       'unit_cost' => 2500,  'current_stock' => 8,  'min_stock' => 4],
            ['code' => 'FLT-001',  'name' => 'ไส้กรองน้ำมันไฮดรอลิก',       'unit_cost' => 450,   'current_stock' => 12, 'min_stock' => 5],
            ['code' => 'VBT-001',  'name' => 'สายพาน V-Belt B68',         'unit_cost' => 320,   'current_stock' => 10, 'min_stock' => 5],
            ['code' => 'GRS-001',  'name' => 'จาระบี Grease EP2 (15kg)',   'unit_cost' => 1200,  'current_stock' => 6,  'min_stock' => 3],
        ];

        foreach ($spareParts as $sp) {
            SparePart::updateOrCreate(
                ['code' => $sp['code']],
                ['name' => $sp['name'], 'unit_cost' => $sp['unit_cost'], 'current_stock' => $sp['current_stock'], 'min_stock' => $sp['min_stock'], 'is_active' => true]
            );
        }
        $this->command?->info('Spare Parts: ' . count($spareParts) . ' items');

        // ── 9. Workflow: แจ้งซ่อม 3 ขั้น ────────────────────
        $wfMaint = ApprovalWorkflow::updateOrCreate(
            ['name' => 'NTEQ — อนุมัติแจ้งซ่อม 3 ขั้น'],
            [
                'document_type' => 'maintenance_request',
                'description' => 'พนักงาน → หัวหน้ากะ → ผจก.แผนก → ผจก.โรงงาน',
                'is_active' => true,
            ]
        );
        $wfMaint->stages()->delete();
        foreach ([
            ['step_no' => 1, 'name' => 'หัวหน้ากะอนุมัติ',     'approver_type' => 'position', 'approver_ref' => $posMap['SHIFT_LEAD']->id],
            ['step_no' => 2, 'name' => 'ผจก.แผนกอนุมัติ',     'approver_type' => 'position', 'approver_ref' => $posMap['DEPT_MGR']->id],
            ['step_no' => 3, 'name' => 'ผจก.โรงงานอนุมัติ',    'approver_type' => 'position', 'approver_ref' => $posMap['PLANT_MGR']->id],
        ] as $stage) {
            ApprovalWorkflowStage::create([
                'workflow_id' => $wfMaint->id,
                'step_no' => $stage['step_no'],
                'name' => $stage['name'],
                'approver_type' => $stage['approver_type'],
                'approver_ref' => (string) $stage['approver_ref'],
                'min_approvals' => 1,
                'is_active' => true,
            ]);
        }
        $this->command?->info('Workflow: 3-step maintenance approval');

        // ── 10. Department ↔ Workflow Binding ────────────────
        foreach (['PROD', 'MAINT', 'QC', 'WH'] as $deptCode) {
            DepartmentWorkflowBinding::updateOrCreate(
                ['department_id' => $deptMap[$deptCode]->id, 'document_type' => 'maintenance_request'],
                ['workflow_id' => $wfMaint->id]
            );
        }
        $this->command?->info('Workflow bindings: 4 departments → maintenance workflow');

        // ── 11. Form: ใบแจ้งซ่อมเครื่องจักร NTEQ ────────────
        $form = DocumentForm::updateOrCreate(
            ['form_key' => 'nteq_maintenance'],
            [
                'name' => 'ใบแจ้งซ่อมเครื่องจักร NTEQ',
                'document_type' => 'maintenance_request',
                'description' => 'ฟอร์มแจ้งซ่อม NTEQ Polymer — 18 fields, visibility rules, validation, 2-column layout',
                'is_active' => true,
                'layout_columns' => 2,
                'submission_table' => 'nteq_maintenance',
            ]
        );

        // Delete old fields and recreate
        $form->fields()->delete();

        $fields = [
            ['field_key' => 'reference_no',       'label' => 'เลขที่เอกสาร',            'field_type' => 'auto_number', 'is_required' => false, 'sort_order' => 1],
            ['field_key' => 'document_date',      'label' => 'วันเอกสาร',               'field_type' => 'date',      'is_required' => true,  'sort_order' => 2],
            ['field_key' => 'priority',           'label' => 'ระดับความเร่งด่วน',       'field_type' => 'select',    'is_required' => true,  'sort_order' => 3,  'options' => ['ปกติ', 'เร่งด่วน', 'ฉุกเฉิน']],
            ['field_key' => 'emergency_reason',    'label' => 'เหตุผลฉุกเฉิน',          'field_type' => 'textarea',  'is_required' => false, 'sort_order' => 4,
                'visibility_rules' => [['field' => 'priority', 'operator' => 'equals', 'value' => 'ฉุกเฉิน']],
                'validation_rules' => ['min_length' => 10]],
            ['field_key' => 'equipment_id',        'label' => 'เครื่องจักร',              'field_type' => 'lookup',    'is_required' => true,  'sort_order' => 5,
                'options' => ['source' => 'equipment']],
            ['field_key' => 'problem_type',        'label' => 'ประเภทปัญหา',            'field_type' => 'radio',     'is_required' => true,  'sort_order' => 6,
                'options' => ['ไฟฟ้า', 'เครื่องกล', 'ท่อ/ประปา', 'ระบบควบคุม', 'โครงสร้าง', 'อื่นๆ']],
            ['field_key' => 'description',         'label' => 'รายละเอียดปัญหา',         'field_type' => 'textarea',  'is_required' => true,  'sort_order' => 7,
                'validation_rules' => ['min_length' => 20]],
            ['field_key' => 'section_extra',       'label' => 'ข้อมูลเพิ่มเติม',          'field_type' => 'section',   'is_required' => false, 'sort_order' => 8],
            ['field_key' => 'found_date',          'label' => 'วันที่พบปัญหา',            'field_type' => 'date',      'is_required' => true,  'sort_order' => 9],
            ['field_key' => 'found_time',          'label' => 'เวลาที่พบ',               'field_type' => 'time',      'is_required' => false, 'sort_order' => 10],
            ['field_key' => 'production_stopped',  'label' => 'สายผลิตหยุดหรือไม่',       'field_type' => 'checkbox',  'is_required' => false, 'sort_order' => 11,
                'options' => ['หยุดแล้ว']],
            ['field_key' => 'stop_duration',       'label' => 'ระยะเวลาหยุด (ชั่วโมง)',   'field_type' => 'number',    'is_required' => false, 'sort_order' => 12,
                'visibility_rules' => [['field' => 'production_stopped', 'operator' => 'equals', 'value' => 'หยุดแล้ว']],
                'validation_rules' => ['min' => 0, 'max' => 720]],
            ['field_key' => 'estimated_cost',      'label' => 'ประมาณค่าซ่อม (บาท)',     'field_type' => 'currency',  'is_required' => false, 'sort_order' => 13,
                'validation_rules' => ['min' => 0, 'max' => 500000]],
            ['field_key' => 'photo',               'label' => 'ภาพถ่ายจุดเสียหาย',        'field_type' => 'file',      'is_required' => false, 'sort_order' => 14],
            ['field_key' => 'needs_parts',         'label' => 'ต้องการอะไหล่',           'field_type' => 'checkbox',  'is_required' => false, 'sort_order' => 15,
                'options' => ['ต้องการ']],
            ['field_key' => 'parts_list',          'label' => 'รายการอะไหล่ที่ต้องใช้',    'field_type' => 'table',     'is_required' => false, 'sort_order' => 16, 'col_span' => 2,
                'options' => ['columns' => [
                    ['key' => 'spare_part', 'label' => 'อะไหล่',     'type' => 'lookup', 'lookup_source' => 'spare_part'],
                    ['key' => 'qty',        'label' => 'จำนวน',      'type' => 'number'],
                    ['key' => 'unit',       'label' => 'หน่วย',      'type' => 'text'],
                    ['key' => 'remark',     'label' => 'หมายเหตุ',   'type' => 'text'],
                ]],
                'visibility_rules' => [['field' => 'needs_parts', 'operator' => 'equals', 'value' => 'ต้องการ']]],
            ['field_key' => 'reporter_phone',      'label' => 'เบอร์โทรผู้แจ้ง',          'field_type' => 'phone',     'is_required' => false, 'sort_order' => 17],
            ['field_key' => 'reporter_signature',  'label' => 'ลายมือชื่อผู้แจ้ง',         'field_type' => 'signature', 'is_required' => false, 'sort_order' => 18],
        ];

        // Pre-enable is_searchable on high-signal fields so the user's list filter
        // bar is useful immediately after seeding — no need for admin to tick them.
        $searchableDefaults = ['document_date', 'priority', 'equipment_id', 'problem_type', 'found_date'];
        foreach ($fields as $f) {
            $form->fields()->create([
                'field_key' => $f['field_key'],
                'label' => $f['label'],
                'field_type' => $f['field_type'],
                'is_required' => $f['is_required'],
                'is_searchable' => in_array($f['field_key'], $searchableDefaults, true),
                'sort_order' => $f['sort_order'],
                'col_span' => $f['col_span'] ?? 0,
                'placeholder' => $f['placeholder'] ?? null,
                'options' => $f['options'] ?? null,
                'visibility_rules' => $f['visibility_rules'] ?? null,
                'validation_rules' => $f['validation_rules'] ?? null,
            ]);
        }

        // Create dedicated submission table. Drop first so re-running the seeder
        // picks up any field changes (createTable is idempotent and skips if the
        // table already exists).
        if ($form->submission_table) {
            Schema::dropIfExists($form->submission_table);
        }
        app(FormSchemaService::class)->createTable($form->load('fields'));

        // Global workflow policy for the form
        DocumentFormWorkflowPolicy::updateOrCreate(
            ['form_id' => $form->id, 'department_id' => null],
            ['use_amount_condition' => false, 'workflow_id' => $wfMaint->id]
        );

        // ── 12. Running Number ─────────────────────────────
        RunningNumberConfig::updateOrCreate(
            ['document_type' => 'maintenance_request'],
            [
                'prefix' => 'MR',
                'digit_count' => 5,
                'reset_mode' => 'yearly',
                'include_year' => true,
                'include_month' => true,
                'is_active' => true,
            ]
        );
        $this->command?->info('Running Number: MR + year + month + 5 digits (e.g. MR202504-00001)');

        $this->command?->info('Form: nteq_maintenance — 18 fields, fdata table created, workflow policy set');

        // Dashboard with CMMS-flavored widgets (repair_requests data source)
        $this->call(FactoryDashboardSeeder::class);

        $this->command?->info('');
        $this->command?->info('✓ NTEQ Polymer demo ready!');
        $this->command?->info('  Login accounts:');
        $this->command?->info('    admin@example.com   (super-admin / ผจก.โรงงาน)');
        $this->command?->info('    somchai@nteq.test   (พนักงานผลิต — requester)');
        $this->command?->info('    somsri@nteq.test    (หัวหน้ากะ — approver step 1)');
        $this->command?->info('    pranee@nteq.test    (ผจก.แผนก — approver step 2)');
        $this->command?->info('    somkit@nteq.test    (ผจก.โรงงาน — approver step 3)');
        $this->command?->info('  Password: Nteq1234!');
    }
}
