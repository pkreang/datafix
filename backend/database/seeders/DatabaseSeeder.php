<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database — vertical-neutral baseline.
     *
     * Includes RBAC, settings, navigation, document types, positions, layout demo forms,
     * and dashboards. Single login: admin@example.com (RolePermissionSeeder).
     *
     * Vertical-specific data (school eForm template, NTEQ CMMS) is NOT in this base —
     * run `IndustryTemplateSeeder` for school or `FactoryCmmsTemplateSeeder` for factory
     * explicitly. The `composer switch:school|switch:factory` scripts handle that.
     *
     * Optional demo dataset: php artisan db:seed --class=DevelopmentDemoSeeder
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            SettingSeeder::class,
            NavigationMenuSeeder::class,
            DocumentTypeSeeder::class,
            PositionDemoSeeder::class,
            DocumentFormSeeder::class,
            // DashboardSeeder is vertical-specific — invoked from per-vertical demo seeders
        ]);
    }
}
