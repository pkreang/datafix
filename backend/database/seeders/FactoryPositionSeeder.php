<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

/**
 * CMMS / factory approval positions (MAINT_SUP, DEPT_MGR, …).
 *
 * Not run from DatabaseSeeder (school product). Use for:
 *   - PurchaseWorkflowSeeder / ApprovalWorkflowDemoSeeder / FactoryCmmsTemplateSeeder
 *   - tests that assert purchase or spare-parts workflows
 *
 *   php artisan db:seed --class=FactoryPositionSeeder
 */
class FactoryPositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['code' => 'MAINT_SUP', 'name' => 'หัวหน้าช่างซ่อมบำรุง', 'description' => 'Maintenance Supervisor — first-level approver for repair/PM/spare parts'],
            ['code' => 'DEPT_MGR', 'name' => 'ผู้จัดการแผนก', 'description' => 'Department Manager — mid-level approver'],
            ['code' => 'PLANT_MGR', 'name' => 'ผู้จัดการโรงงาน', 'description' => 'Plant Manager — top-level approver for high-value items'],
            ['code' => 'WH_KEEPER', 'name' => 'หัวหน้าคลังอะไหล่', 'description' => 'Spare Parts Warehouse Keeper — confirms stock issuance'],
            ['code' => 'TECH', 'name' => 'ช่างซ่อมบำรุง', 'description' => 'Maintenance Technician'],
        ];

        foreach ($positions as $pos) {
            Position::updateOrCreate(
                ['code' => $pos['code']],
                ['name' => $pos['name'], 'description' => $pos['description'], 'is_active' => true]
            );
        }

        $this->command?->info('FactoryPositionSeeder: '.count($positions).' CMMS positions.');
    }
}
