<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Optional demo / pilot dataset (not run from DatabaseSeeder).
 *
 *   php artisan db:seed --class=DevelopmentDemoSeeder
 */
class DevelopmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            DepartmentSeeder::class,
            PositionDemoSeeder::class,
            EquipmentCategorySeeder::class,
            EquipmentLocationSeeder::class,
            EquipmentSeeder::class,
            SparePartSeeder::class,
            ApprovalWorkflowDemoSeeder::class,
            DashboardSeeder::class,
        ]);
    }
}
