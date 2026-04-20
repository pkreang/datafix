<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Strip CMMS / factory menu rows from existing databases (school product).
 * Fresh installs rely on NavigationMenuSeeder; this cleans legacy rows from older seeds/migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('navigation_menus')) {
            return;
        }

        $prefixes = [
            '/repair-requests',
            '/maintenance',
            '/spare-parts',
            '/purchase-requests',
            '/purchase-orders',
            '/equipment-registry',
            '/equipment-locations',
            '/settings/equipment-locations',
            '/settings/equipment',
            '/reports/repair-history',
            '/reports/pm-am-history',
        ];

        foreach ($prefixes as $prefix) {
            DB::table('navigation_menus')
                ->where('route', 'like', $prefix.'%')
                ->delete();
        }

        foreach (['Spare Parts', 'Equipment Registry', 'Purchasing'] as $label) {
            DB::table('navigation_menus')->where('label', $label)->delete();
        }

        Cache::forget('navigation_menus_tree');
    }

    public function down(): void
    {
        // Irreversible — menu rows are not restored.
    }
};
