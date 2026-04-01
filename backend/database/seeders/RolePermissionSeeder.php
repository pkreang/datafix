<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guard = 'web';

        // Ensure manage_own_dashboard permission exists and is granted to all roles
        $manageDashboardPerm = \Spatie\Permission\Models\Permission::firstOrCreate(
            ['name' => 'manage_own_dashboard', 'guard_name' => $guard],
            ['module' => 'dashboard', 'action' => 'manage_own']
        );

        // Super Admin - bypass all, no permissions needed
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => $guard],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Full system access',
                'is_system' => true,
            ]
        );

        // Admin - full access to all modules
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => $guard],
            [
                'display_name' => 'Administrator',
                'description' => 'Full access to all modules',
                'is_system' => true,
            ]
        );
        $admin->syncPermissions(\Spatie\Permission\Models\Permission::all());

        // Viewer - read-only all modules
        $viewer = Role::firstOrCreate(
            ['name' => 'viewer', 'guard_name' => $guard],
            [
                'display_name' => 'Viewer',
                'description' => 'Read-only access to all modules',
                'is_system' => false,
            ]
        );
        $viewer->syncPermissions(
            \Spatie\Permission\Models\Permission::whereIn('action', ['read', 'export'])->pluck('name')
        );

        // Approver - can approve/reject and audit approval history
        $approver = Role::firstOrCreate(
            ['name' => 'approver', 'guard_name' => $guard],
            [
                'display_name' => 'Approver',
                'description' => 'Can approve workflow tasks',
                'is_system' => false,
            ]
        );
        $approver->syncPermissions(
            \Spatie\Permission\Models\Permission::whereIn('name', [
                'approval.approve',
            ])->pluck('name')
        );

        // Sole bootstrap user for fresh installs (DatabaseSeeder does not create any other logins).
        // Bootstrap super-admin (admin@example.com) — updateOrCreate so re-seed fixes drift:
        // wrong password, auth_provider set by SSO JIT, or firstOrCreate having skipped updates.
        $user = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => 'password',
                'is_active' => true,
                'is_super_admin' => true,
                'auth_provider' => null,
                'external_id' => null,
                'ldap_dn' => null,
            ]
        );
        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        // Grant manage_own_dashboard to admin and viewer roles by default
        foreach (['admin', 'viewer', 'approver'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role && !$role->hasPermissionTo('manage_own_dashboard')) {
                $role->givePermissionTo('manage_own_dashboard');
            }
        }

        // Assign manage companies to super-admin
        $manageCompanies = \Spatie\Permission\Models\Permission::where('name', 'manage companies')->first();
        if ($manageCompanies && ! $superAdmin->hasPermissionTo('manage companies')) {
            $superAdmin->givePermissionTo('manage companies');
        }
    }
}
