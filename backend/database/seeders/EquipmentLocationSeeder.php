<?php

namespace Database\Seeders;

use App\Models\EquipmentLocation;
use Illuminate\Database\Seeder;

class EquipmentLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['code' => 'A1F', 'name' => 'Building A - 1st Floor', 'building' => 'Building A', 'floor' => '1', 'zone' => 'Production'],
            ['code' => 'A2F', 'name' => 'Building A - 2nd Floor', 'building' => 'Building A', 'floor' => '2', 'zone' => 'Office'],
            ['code' => 'B1F', 'name' => 'Building B - 1st Floor', 'building' => 'Building B', 'floor' => '1', 'zone' => 'Warehouse'],
            ['code' => 'UTIL', 'name' => 'Utility Area', 'building' => 'Outdoor', 'floor' => null, 'zone' => 'Utilities'],
        ];

        foreach ($locations as $loc) {
            EquipmentLocation::updateOrCreate(
                ['code' => $loc['code']],
                [
                    'name' => $loc['name'],
                    'building' => $loc['building'],
                    'floor' => $loc['floor'],
                    'zone' => $loc['zone'],
                    'is_active' => true,
                ]
            );
        }
    }
}
