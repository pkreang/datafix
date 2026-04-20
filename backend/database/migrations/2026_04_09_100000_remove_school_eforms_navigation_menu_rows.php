<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove “School eForms” sidebar group (submit form / my submissions).
 * Routes /forms remain reachable by URL if needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('navigation_menus')) {
            return;
        }

        DB::table('navigation_menus')->where('parent_id', 30)->delete();
        DB::table('navigation_menus')->where('id', 30)->delete();
        DB::table('navigation_menus')->where('label', 'School eForms')->delete();

        Cache::forget('navigation_menus_tree');
    }

    public function down(): void
    {
        // Irreversible — restored via NavigationMenuSeeder on fresh seed only.
    }
};
