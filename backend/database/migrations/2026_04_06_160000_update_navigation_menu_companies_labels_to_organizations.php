<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('navigation_menus')) {
            return;
        }

        DB::table('navigation_menus')
            ->where('label_en', 'Companies')
            ->update([
                'label' => 'Organizations',
                'label_en' => 'Organizations',
                'label_th' => 'องค์กร',
            ]);

        Cache::forget('navigation_menus_tree');
    }

    public function down(): void
    {
        if (! Schema::hasTable('navigation_menus')) {
            return;
        }

        DB::table('navigation_menus')
            ->where('label_en', 'Organizations')
            ->where('route', '/prpfile')
            ->update([
                'label' => 'Companies',
                'label_en' => 'Companies',
                'label_th' => 'บริษัท',
            ]);

        Cache::forget('navigation_menus_tree');
    }
};
