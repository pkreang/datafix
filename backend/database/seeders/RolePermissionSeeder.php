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

        // Super Admin - bypass all, no permissions needed
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => $guard],
            [
                'display_name' => 'Super Administrator',
                'description'  => 'Full system access',
                'is_system'    => true,
            ]
        );

        // Admin - full access to all modules
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => $guard],
            [
                'display_name' => 'Administrator',
                'description'  => 'Full access to all modules',
                'is_system'    => true,
            ]
        );
        $admin->syncPermissions(\Spatie\Permission\Models\Permission::all());

        // Viewer - read-only all modules
        $viewer = Role::firstOrCreate(
            ['name' => 'viewer', 'guard_name' => $guard],
            [
                'display_name' => 'Viewer',
                'description'  => 'Read-only access to all modules',
                'is_system'    => false,
            ]
        );
        $viewer->syncPermissions(
            \Spatie\Permission\Models\Permission::whereIn('action', ['read', 'export'])->pluck('name')
        );

        // Super admin user (admin@example.com)
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'           => 'Super Admin',
                'password'       => 'password',
                'is_active'      => true,
                'is_super_admin' => true,
            ]
        );
        if (!$user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }
    }
}
