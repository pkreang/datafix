<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStage;
use App\Models\DocumentForm;
use App\Models\DocumentFormWorkflowPolicy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Seeds a minimal repair-request approval chain for fresh installs / sales demos.
 * Logins: approver@example.com / password (can approve), requester@example.com / password (submit only).
 */
class RepairApprovalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $form = DocumentForm::query()->where('form_key', 'repair_request_default')->first();
        if (! $form) {
            $this->command?->warn('RepairApprovalDemoSeeder: repair_request_default form missing; run DocumentFormSeeder first.');

            return;
        }

        $workflow = ApprovalWorkflow::query()->updateOrCreate(
            ['name' => 'Default Repair Approval'],
            [
                'document_type' => 'repair_request',
                'description' => 'Demo: single-step approval by Approver role.',
                'is_active' => true,
            ]
        );

        $workflow->stages()->delete();
        ApprovalWorkflowStage::query()->create([
            'workflow_id' => $workflow->id,
            'step_no' => 1,
            'name' => 'Approver review',
            'approver_type' => 'role',
            'approver_ref' => 'approver',
            'min_approvals' => 1,
            'is_active' => true,
        ]);

        DocumentFormWorkflowPolicy::query()->updateOrCreate(
            [
                'form_id' => $form->id,
                'department_id' => null,
            ],
            [
                'use_amount_condition' => false,
                'workflow_id' => $workflow->id,
            ]
        );

        $approverRole = Role::query()->where('name', 'approver')->where('guard_name', 'web')->first();
        $viewerRole = Role::query()->where('name', 'viewer')->where('guard_name', 'web')->first();

        $approver = User::query()->updateOrCreate(
            ['email' => 'approver@example.com'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Approver',
                'password' => 'password',
                'is_active' => true,
                'is_super_admin' => false,
            ]
        );
        if ($approverRole && ! $approver->hasRole('approver')) {
            $approver->syncRoles(['approver']);
        }

        $requester = User::query()->updateOrCreate(
            ['email' => 'requester@example.com'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Requester',
                'password' => 'password',
                'is_active' => true,
                'is_super_admin' => false,
            ]
        );
        if ($viewerRole && ! $requester->hasRole('viewer')) {
            $requester->syncRoles(['viewer']);
        }

        // Align with BranchSeeder (company / branch) when present
        $admin = User::query()->where('email', 'admin@example.com')->first();
        if ($admin) {
            foreach ([$approver, $requester] as $u) {
                if ($u->company_id === null && $admin->company_id) {
                    $u->forceFill([
                        'company_id' => $admin->company_id,
                        'branch_id' => $admin->branch_id,
                    ])->save();
                }
            }
        }

        $this->command?->info('RepairApprovalDemoSeeder: workflow + policy + approver@ / requester@ users ready.');
    }
}
