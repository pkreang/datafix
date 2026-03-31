<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'password_min_length' => '8',
            'password_max_length' => '255',
            'password_require_uppercase' => '1',
            'password_require_lowercase' => '1',
            'password_require_number' => '1',
            'password_require_special' => '1',
            'password_expires_days' => '0',
            'password_force_change_first_login' => '1',
            'password_prevent_reuse' => '0',
            'lockout_max_attempts' => '5',
            'lockout_duration_minutes' => '30',
            /** hybrid | department_scoped | organization_wide — see ApprovalFlowService */
            'approval_workflow_routing_mode' => 'hybrid',
            'auth_local_enabled' => '1',
            'auth_entra_enabled' => '0',
            'auth_ldap_enabled' => '0',
            'auth_local_super_admin_only' => '0',
            'auth_default_role' => 'viewer',
            'entra_tenant_id' => '',
            'entra_client_id' => '',
            'ldap_host' => '',
            'ldap_port' => '389',
            'ldap_base_dn' => '',
            'ldap_bind_dn' => '',
            'ldap_user_filter' => '(mail=%s)',
            'ldap_use_tls' => '0',
            'auth_password_help_url' => '',
            /** JSON array of {"pattern":"substring","role":"spatie_role_name"} for LDAP memberOf / Entra groups */
            'auth_directory_group_role_map' => '[]',
            /** single | multi — single hides "Add Company" button when 1 company exists */
            'company_mode' => 'single',
            /** Notification settings */
            'notifications.email_enabled' => '1',
            'notifications.approval_pending_email' => '1',
            'notifications.workflow_approved_email' => '1',
            'notifications.workflow_rejected_email' => '1',
            'notifications.line_enabled' => '1',
            'notifications.approval_pending_line' => '1',
            'notifications.workflow_approved_line' => '1',
            'notifications.workflow_rejected_line' => '1',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
