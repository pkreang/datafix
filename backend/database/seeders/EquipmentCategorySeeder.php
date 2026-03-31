<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

class EquipmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'PUMP', 'name' => 'Pumps', 'description' => 'Water pumps, hydraulic pumps, etc.'],
            ['code' => 'MOTOR', 'name' => 'Motors', 'description' => 'Electric motors and drives'],
            ['code' => 'CONV', 'name' => 'Conveyors', 'description' => 'Belt conveyors, roller conveyors'],
            ['code' => 'COMP', 'name' => 'Compressors', 'description' => 'Air compressors and gas compressors'],
            ['code' => 'HVAC', 'name' => 'HVAC', 'description' => 'Heating, ventilation, and air conditioning'],
        ];

        foreach ($categories as $cat) {
            EquipmentCategory::updateOrCreate(
                ['code' => $cat['code']],
                ['name' => $cat['name'], 'description' => $cat['description'], 'is_active' => true]
            );
        }
    }
}
