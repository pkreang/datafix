<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

/**
 * Master departments for repair routing, filters, and org structure (demo / pilot).
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'code' => 'MAINT',
                'name' => 'แผนกซ่อมบำรุง',
                'description' => 'งานซ่อมเชิงป้องกันและแก้ไข',
            ],
            [
                'code' => 'PROD',
                'name' => 'แผนกผลิต',
                'description' => 'สายการผลิตและเครื่องจักรหลัก',
            ],
            [
                'code' => 'WH',
                'name' => 'แผนกคลังสินค้า',
                'description' => 'คลัง ขนส่งภายใน',
            ],
            [
                'code' => 'FAC',
                'name' => 'แผนกอาคารสถานที่',
                'description' => 'อาคาร สาธารณูปโภค พื้นที่',
            ],
            [
                'code' => 'IT',
                'name' => 'แผนกเทคโนโลยีสารสนเทศ',
                'description' => 'ระบบคอมพิวเตอร์ อุปกรณ์ IT',
            ],
            [
                'code' => 'GA',
                'name' => 'สำนักงานทั่วไป',
                'description' => 'ธุรการ บุคคล ทั่วไป',
            ],
        ];

        foreach ($rows as $row) {
            Department::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('DepartmentSeeder: '.count($rows).' departments ready.');
    }
}
