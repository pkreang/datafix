<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Customer-style install: RBAC, settings, navigation, document types/forms only.
     * Single login: admin@example.com (RolePermissionSeeder). No demo company, equipment, workflows, etc.
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
            DocumentFormSeeder::class,
            DashboardSeeder::class,
        ]);
    }
}
