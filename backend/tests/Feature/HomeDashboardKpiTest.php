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

    public function test_kpi_endpoint_returns_value_for_school_pending_approvals(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::where('email', 'admin@example.com')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/dashboard/kpi/school_pending_approvals');

        $response->assertOk()->assertJsonStructure(['value']);
    }

    public function test_kpi_endpoint_returns_404_for_unknown_card(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::where('email', 'admin@example.com')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/dashboard/kpi/unknown_card');

        $response->assertNotFound();
    }

    public function test_kpi_config_save_stores_preference(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::where('email', 'admin@example.com')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/dashboard/kpi-config', [
                'cards' => ['school_pending_approvals', 'school_draft_forms'],
            ]);

        $response->assertOk();
        $user->refresh();
        $this->assertEquals(['school_pending_approvals', 'school_draft_forms'], $user->dashboard_config['cards']);
    }

    public function test_dashboard_seeder_creates_school_overview(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\DashboardSeeder::class);

        $this->assertDatabaseCount('report_dashboards', 1);
        $this->assertDatabaseHas('report_dashboards', ['name' => 'School eForm Overview']);
    }

    public function test_school_overview_dashboard_widgets(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\DashboardSeeder::class);

        $dashboard = \App\Models\ReportDashboard::where('name', 'School eForm Overview')->first();
        $this->assertNotNull($dashboard);
        $dashboard->load('widgets');
        $this->assertCount(2, $dashboard->widgets);
        $sources = $dashboard->widgets->pluck('data_source')->sort()->values()->all();
        $this->assertSame(['school_eforms', 'school_eforms_pending'], $sources);
    }
}
