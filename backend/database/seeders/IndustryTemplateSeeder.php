<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * School eForm playbook (default product mode).
 *
 *   php artisan db:seed --class=IndustryTemplateSeeder
 *
 * Factory CMMS templates: run `FactoryCmmsTemplateSeeder` separately if needed.
 */
class IndustryTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SchoolEFormTemplateSeeder::class,
        ]);
    }
}
