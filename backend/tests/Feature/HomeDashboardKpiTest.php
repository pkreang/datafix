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
    }
}
