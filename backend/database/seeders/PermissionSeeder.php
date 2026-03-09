<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'dashboard'       => ['read'],
            'product'         => ['create', 'read', 'update', 'delete'],
            'sales'           => ['create', 'read', 'update', 'delete', 'export'],
            'purchase'        => ['create', 'read', 'update', 'delete', 'export'],
            'expense'         => ['create', 'read', 'update', 'delete'],
            'report'          => ['read', 'export'],
            'loan'            => ['create', 'read', 'update', 'delete'],
            'company_profile' => ['read', 'update'],
            'user_access'     => ['create', 'read', 'update', 'delete'],
            'integrations'    => ['read', 'update'],
        ];

        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action) {
                Permission::updateOrCreate(
                    [
                        'name'       => "{$module}.{$action}",
                        'guard_name' => 'web',
                    ],
                    [
                        'module' => $module,
                        'action' => $action,
                    ]
                );
            }
        }
    }
}
