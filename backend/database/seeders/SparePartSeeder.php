<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\SparePart;
use Illuminate\Database\Seeder;

class SparePartSeeder extends Seeder
{
    public function run(): void
    {
        $pumpCat = EquipmentCategory::where('code', 'PUMP')->first();
        $motorCat = EquipmentCategory::where('code', 'MOTOR')->first();
        $convCat = EquipmentCategory::where('code', 'CONV')->first();
        $compCat = EquipmentCategory::where('code', 'COMP')->first();
        $hvacCat = EquipmentCategory::where('code', 'HVAC')->first();

        $parts = [
            ['code' => 'SP-BRG-001', 'name' => 'Ball Bearing 6205', 'unit' => 'ชิ้น', 'category' => $pumpCat, 'min_stock' => 10, 'current_stock' => 25, 'unit_cost' => 350],
            ['code' => 'SP-BRG-002', 'name' => 'Ball Bearing 6308', 'unit' => 'ชิ้น', 'category' => $motorCat, 'min_stock' => 8, 'current_stock' => 15, 'unit_cost' => 580],
            ['code' => 'SP-BLT-001', 'name' => 'V-Belt A68', 'unit' => 'เส้น', 'category' => $convCat, 'min_stock' => 5, 'current_stock' => 12, 'unit_cost' => 280],
            ['code' => 'SP-BLT-002', 'name' => 'Timing Belt HTD 8M', 'unit' => 'เส้น', 'category' => $motorCat, 'min_stock' => 3, 'current_stock' => 6, 'unit_cost' => 1200],
            ['code' => 'SP-FLT-001', 'name' => 'Oil Filter Element', 'unit' => 'ชิ้น', 'category' => $compCat, 'min_stock' => 5, 'current_stock' => 10, 'unit_cost' => 450],
            ['code' => 'SP-FLT-002', 'name' => 'Air Filter Element', 'unit' => 'ชิ้น', 'category' => $hvacCat, 'min_stock' => 10, 'current_stock' => 20, 'unit_cost' => 320],
            ['code' => 'SP-SEL-001', 'name' => 'Mechanical Seal 25mm', 'unit' => 'ชิ้น', 'category' => $pumpCat, 'min_stock' => 4, 'current_stock' => 8, 'unit_cost' => 2500],
            ['code' => 'SP-SEL-002', 'name' => 'O-Ring Kit (Pump)', 'unit' => 'ชุด', 'category' => $pumpCat, 'min_stock' => 5, 'current_stock' => 15, 'unit_cost' => 180],
            ['code' => 'SP-LUB-001', 'name' => 'Grease EP2 (400g)', 'unit' => 'หลอด', 'category' => null, 'min_stock' => 20, 'current_stock' => 50, 'unit_cost' => 85],
            ['code' => 'SP-LUB-002', 'name' => 'Hydraulic Oil ISO 46 (20L)', 'unit' => 'ถัง', 'category' => $pumpCat, 'min_stock' => 3, 'current_stock' => 5, 'unit_cost' => 1800],
            ['code' => 'SP-ELC-001', 'name' => 'Contactor 3P 25A', 'unit' => 'ชิ้น', 'category' => $motorCat, 'min_stock' => 3, 'current_stock' => 7, 'unit_cost' => 950],
            ['code' => 'SP-ELC-002', 'name' => 'Thermal Overload Relay 12-18A', 'unit' => 'ชิ้น', 'category' => $motorCat, 'min_stock' => 3, 'current_stock' => 5, 'unit_cost' => 720],
        ];

        foreach ($parts as $part) {
            SparePart::updateOrCreate(
                ['code' => $part['code']],
                [
                    'name' => $part['name'],
                    'unit' => $part['unit'],
                    'equipment_category_id' => $part['category']?->id,
                    'min_stock' => $part['min_stock'],
                    'current_stock' => $part['current_stock'],
                    'unit_cost' => $part['unit_cost'],
                    'is_active' => true,
                ]
            );
        }
    }
}
