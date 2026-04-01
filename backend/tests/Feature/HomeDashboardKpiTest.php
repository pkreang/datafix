<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HomeDashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_own_dashboard_permission_exists(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertTrue(
            Permission::where('name', 'manage_own_dashboard')->where('guard_name', 'web')->exists()
        );

        foreach (['admin', 'viewer', 'approver'] as $roleName) {
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
            $this->assertNotNull($role, "Role {$roleName} should exist");
            $this->assertTrue(
                $role->hasPermissionTo('manage_own_dashboard'),
                "Role {$roleName} should have manage_own_dashboard permission"
            );
        }
    }
}
