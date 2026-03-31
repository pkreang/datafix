<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentLocation;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = EquipmentCategory::pluck('id', 'code');
        $locations = EquipmentLocation::pluck('id', 'code');

        $items = [
            [
                'code' => 'PMP-001',
                'name' => 'Main Water Pump #1',
                'serial_number' => 'WP-2024-0001',
                'category' => 'PUMP',
                'location' => 'A1F',
                'status' => 'active',
            ],
            [
                'code' => 'PMP-002',
                'name' => 'Hydraulic Pump #2',
                'serial_number' => 'HP-2024-0012',
                'category' => 'PUMP',
                'location' => 'B1F',
                'status' => 'active',
            ],
            [
                'code' => 'MTR-001',
                'name' => 'Production Line Motor A',
                'serial_number' => 'MT-2023-0100',
                'category' => 'MOTOR',
                'location' => 'A1F',
                'status' => 'active',
            ],
            [
                'code' => 'CNV-001',
                'name' => 'Belt Conveyor Line 1',
                'serial_number' => 'CV-2023-0050',
                'category' => 'CONV',
                'location' => 'A1F',
                'status' => 'under_maintenance',
            ],
            [
                'code' => 'CMP-001',
                'name' => 'Air Compressor Unit',
                'serial_number' => 'AC-2022-0033',
                'category' => 'COMP',
                'location' => 'UTIL',
                'status' => 'active',
            ],
            [
                'code' => 'HVAC-001',
                'name' => 'Office HVAC System',
                'serial_number' => 'HV-2024-0005',
                'category' => 'HVAC',
                'location' => 'A2F',
                'status' => 'active',
            ],
        ];

        foreach ($items as $item) {
            Equipment::updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'serial_number' => $item['serial_number'],
                    'equipment_category_id' => $categories[$item['category']] ?? 1,
                    'equipment_location_id' => $locations[$item['location']] ?? 1,
                    'status' => $item['status'],
                    'is_active' => true,
                ]
            );
        }
    }
}
