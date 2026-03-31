<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::query()->updateOrCreate(
            ['code' => 'DFIX'],
            [
                'name' => 'DataFix Co., Ltd.',
                'logo' => 'system/demo-company-logo.svg',
                'is_active' => true,
            ]
        );
    }
}
