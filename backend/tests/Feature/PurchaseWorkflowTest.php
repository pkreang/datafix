<?php
namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DocumentFormSeeder;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PurchaseWorkflowSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\PositionDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PurchaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_permissions_exist_after_seeding(): void
    {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);

        foreach (['view_purchase_requests', 'view_purchase_orders', 'purchase_order.create'] as $perm) {
            $this->assertTrue(
                Permission::where('name', $perm)->where('guard_name', 'web')->exists(),
                "Permission {$perm} should exist"
            );
        }
    }

    public function test_approver_role_has_view_purchase_permissions(): void
    {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);

        $role = \Spatie\Permission\Models\Role::where('name', 'approver')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('view_purchase_requests'));
        $this->assertTrue($role->hasPermissionTo('view_purchase_orders'));
    }

    public function test_document_types_seeded(): void
    {
        $this->seed([DocumentTypeSeeder::class]);

        $this->assertDatabaseHas('document_types', ['code' => 'purchase_request']);
        $this->assertDatabaseHas('document_types', ['code' => 'purchase_order']);
    }

    public function test_document_forms_seeded(): void
    {
        $this->seed([DocumentFormSeeder::class]);

        $this->assertDatabaseHas('document_forms', ['form_key' => 'purchase_request_default']);
        $this->assertDatabaseHas('document_forms', ['form_key' => 'purchase_order_default']);
    }

    public function test_purchase_workflow_seeder_creates_workflows(): void
    {
        $this->seed([
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            DocumentTypeSeeder::class,
            DocumentFormSeeder::class,
            PositionDemoSeeder::class,
            PurchaseWorkflowSeeder::class,
        ]);

        $this->assertDatabaseHas('approval_workflows', ['name' => 'PR - Small (≤50k)', 'document_type' => 'purchase_request']);
        $this->assertDatabaseHas('approval_workflows', ['name' => 'PR - Large (>50k)', 'document_type' => 'purchase_request']);
        $this->assertDatabaseHas('approval_workflows', ['name' => 'PO - Standard',     'document_type' => 'purchase_order']);
    }

    public function test_purchase_request_index_requires_auth(): void
    {
        $response = $this->get('/purchase-requests');
        $response->assertRedirect('/login');
    }

    public function test_purchase_request_items_table_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('purchase_request_items'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('purchase_order_items'));
    }
}
