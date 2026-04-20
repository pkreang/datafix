<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('navigation_menus')->where('id', 2)->exists()) {
            return;
        }

        $now = now();

        DB::table('navigation_menus')->updateOrInsert(
            ['id' => 43],
            [
                'parent_id' => 2,
                'label' => 'Branch scoping',
                'label_en' => 'Branch scoping',
                'label_th' => 'สาขา',
                'icon' => 'building-office',
                'route' => '/settings/branch-scoping',
                'permission' => 'manage_settings',
                'sort_order' => 17,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        Cache::forget('navigation_menus_tree');
    }

    public function down(): void
    {
        DB::table('navigation_menus')->where('id', 43)->delete();
        Cache::forget('navigation_menus_tree');
    }
};
