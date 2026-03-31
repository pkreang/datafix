<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStage;
use App\Models\DocumentForm;
use App\Models\DocumentFormWorkflowPolicy;
use App\Models\DocumentFormWorkflowRange;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Seeds position-based approval workflows for all 3 document types:
 * repair_request, pm_am_plan, spare_parts_requisition.
 *
 * Amount-based routing with realistic Thai factory thresholds.
 */
class ApprovalWorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        $maintSup = Position::where('code', 'MAINT_SUP')->first();
        $deptMgr = Position::where('code', 'DEPT_MGR')->first();
        $plantMgr = Position::where('code', 'PLANT_MGR')->first();

        if (! $maintSup || ! $deptMgr || ! $plantMgr) {
            $this->command?->warn('ApprovalWorkflowDemoSeeder: positions missing; run PositionDemoSeeder first.');

            return;
        }

        // ─── Repair Request Workflows ──────────────────────────

        $repairSmall = $this->createWorkflow('Repair - Small (<10k)', 'repair_request', 'งานซ่อมมูลค่าต่ำ', [
            ['step_no' => 1, 'name' => 'หัวหน้าช่างอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $maintSup->id],
        ]);

        $repairMedium = $this->createWorkflow('Repair - Medium (10k-100k)', 'repair_request', 'งานซ่อมมูลค่าปานกลาง', [
            ['step_no' => 1, 'name' => 'หัวหน้าช่างอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $maintSup->id],
            ['step_no' => 2, 'name' => 'ผจก.แผนกอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $deptMgr->id],
        ]);

        $repairLarge = $this->createWorkflow('Repair - Large (>100k)', 'repair_request', 'งานซ่อมมูลค่าสูง', [
            ['step_no' => 1, 'name' => 'หัวหน้าช่างอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $maintSup->id],
            ['step_no' => 2, 'name' => 'ผจก.แผนกอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $deptMgr->id],
            ['step_no' => 3, 'name' => 'ผจก.โรงงานอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $plantMgr->id],
        ]);

        // ─── PM/AM Plan Workflows ──────────────────────────────

        $pmStandard = $this->createWorkflow('PM/AM - Standard (≤50k)', 'pm_am_plan', 'แผน PM/AM มาตรฐาน', [
            ['step_no' => 1, 'name' => 'หัวหน้าช่างอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $maintSup->id],
            ['step_no' => 2, 'name' => 'ผจก.แผนกอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $deptMgr->id],
        ]);

        $pmHighValue = $this->createWorkflow('PM/AM - High Value (>50k)', 'pm_am_plan', 'แผน PM/AM มูลค่าสูง', [
            ['step_no' => 1, 'name' => 'หัวหน้าช่างอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $maintSup->id],
            ['step_no' => 2, 'name' => 'ผจก.แผนกอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $deptMgr->id],
            ['step_no' => 3, 'name' => 'ผจก.โรงงานอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $plantMgr->id],
        ]);

        // ─── Spare Parts Requisition Workflows ─────────────────

        $spSmall = $this->createWorkflow('Spare Parts - Low (<5k)', 'spare_parts_requisition', 'เบิกอะไหล่มูลค่าต่ำ', [
            ['step_no' => 1, 'name' => 'หัวหน้าช่างอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $maintSup->id],
        ]);

        $spMedium = $this->createWorkflow('Spare Parts - Medium (5k-50k)', 'spare_parts_requisition', 'เบิกอะไหล่มูลค่าปานกลาง', [
            ['step_no' => 1, 'name' => 'หัวหน้าช่างอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $maintSup->id],
            ['step_no' => 2, 'name' => 'ผจก.แผนกอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $deptMgr->id],
        ]);

        $spLarge = $this->createWorkflow('Spare Parts - High (>50k)', 'spare_parts_requisition', 'เบิกอะไหล่มูลค่าสูง', [
            ['step_no' => 1, 'name' => 'หัวหน้าช่างอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $maintSup->id],
            ['step_no' => 2, 'name' => 'ผจก.แผนกอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $deptMgr->id],
            ['step_no' => 3, 'name' => 'ผจก.โรงงานอนุมัติ', 'approver_type' => 'position', 'approver_ref' => $plantMgr->id],
        ]);

        // ─── Document Form Workflow Policies (amount-based) ────

        $this->createAmountPolicy('repair_request_default', [
            ['min' => 0, 'max' => 9999.99, 'workflow' => $repairSmall],
            ['min' => 10000, 'max' => 100000, 'workflow' => $repairMedium],
            ['min' => 100000.01, 'max' => null, 'workflow' => $repairLarge],
        ]);

        $this->createAmountPolicy('pm_am_plan_default', [
            ['min' => 0, 'max' => 50000, 'workflow' => $pmStandard],
            ['min' => 50000.01, 'max' => null, 'workflow' => $pmHighValue],
        ]);

        $this->createAmountPolicy('spare_parts_requisition_default', [
            ['min' => 0, 'max' => 4999.99, 'workflow' => $spSmall],
            ['min' => 5000, 'max' => 50000, 'workflow' => $spMedium],
            ['min' => 50000.01, 'max' => null, 'workflow' => $spLarge],
        ]);

        // ─── Demo Users with positions ─────────────────────────

        $this->seedDemoUsers($maintSup, $deptMgr, $plantMgr);

        $this->command?->info('ApprovalWorkflowDemoSeeder: 8 workflows + 3 amount-based policies + demo users ready.');
    }

    private function createWorkflow(string $name, string $documentType, string $description, array $stages): ApprovalWorkflow
    {
        $workflow = ApprovalWorkflow::updateOrCreate(
            ['name' => $name],
            [
                'document_type' => $documentType,
                'description' => $description,
                'is_active' => true,
            ]
        );

        $workflow->stages()->delete();

        foreach ($stages as $stage) {
            ApprovalWorkflowStage::create([
                'workflow_id' => $workflow->id,
                'step_no' => $stage['step_no'],
                'name' => $stage['name'],
                'approver_type' => $stage['approver_type'],
                'approver_ref' => (string) $stage['approver_ref'],
                'min_approvals' => 1,
                'is_active' => true,
            ]);
        }

        return $workflow;
    }

    private function createAmountPolicy(string $formKey, array $ranges): void
    {
        $form = DocumentForm::where('form_key', $formKey)->first();
        if (! $form) {
            $this->command?->warn("ApprovalWorkflowDemoSeeder: form {$formKey} not found; skipping policy.");

            return;
        }

        $policy = DocumentFormWorkflowPolicy::updateOrCreate(
            ['form_id' => $form->id, 'department_id' => null],
            [
                'use_amount_condition' => true,
                'workflow_id' => $ranges[0]['workflow']->id, // fallback
            ]
        );

        $policy->ranges()->delete();

        foreach ($ranges as $i => $range) {
            DocumentFormWorkflowRange::create([
                'policy_id' => $policy->id,
                'min_amount' => $range['min'],
                'max_amount' => $range['max'],
                'workflow_id' => $range['workflow']->id,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function seedDemoUsers(Position $maintSup, Position $deptMgr, Position $plantMgr): void
    {
        $approverRole = Role::where('name', 'approver')->where('guard_name', 'web')->first();
        $viewerRole = Role::where('name', 'viewer')->where('guard_name', 'web')->first();

        $admin = User::where('email', 'admin@example.com')->first();

        $demoUsers = [
            [
                'email' => 'approver@example.com',
                'first_name' => 'Demo',
                'last_name' => 'Approver (Supervisor)',
                'position_id' => $maintSup->id,
                'position' => $maintSup->name,
                'role' => $approverRole,
            ],
            [
                'email' => 'manager@example.com',
                'first_name' => 'Demo',
                'last_name' => 'Manager',
                'position_id' => $deptMgr->id,
                'position' => $deptMgr->name,
                'role' => $approverRole,
            ],
            [
                'email' => 'plant-manager@example.com',
                'first_name' => 'Demo',
                'last_name' => 'Plant Manager',
                'position_id' => $plantMgr->id,
                'position' => $plantMgr->name,
                'role' => $approverRole,
            ],
            [
                'email' => 'requester@example.com',
                'first_name' => 'Demo',
                'last_name' => 'Requester',
                'position_id' => null,
                'position' => null,
                'role' => $viewerRole,
            ],
        ];

        foreach ($demoUsers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'password' => 'password',
                    'is_active' => true,
                    'is_super_admin' => false,
                    'position_id' => $data['position_id'],
                    'position' => $data['position'],
                ]
            );

            if ($data['role'] && ! $user->hasRole($data['role']->name)) {
                $user->syncRoles([$data['role']->name]);
            }

            if ($admin && $user->company_id === null && $admin->company_id) {
                $user->forceFill([
                    'company_id' => $admin->company_id,
                    'branch_id' => $admin->branch_id,
                ])->save();
            }
        }
    }
}
