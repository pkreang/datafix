<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            SettingSeeder::class,
            NavigationMenuSeeder::class,
            DocumentTypeSeeder::class,
            DocumentFormSeeder::class,
            CompanySeeder::class,
            DepartmentSeeder::class,
            PositionDemoSeeder::class,
            EquipmentCategorySeeder::class,
            EquipmentLocationSeeder::class,
            EquipmentSeeder::class,
            SparePartSeeder::class,
            ApprovalWorkflowDemoSeeder::class,
            ReportDashboardSeeder::class,
        ]);
    }
}
