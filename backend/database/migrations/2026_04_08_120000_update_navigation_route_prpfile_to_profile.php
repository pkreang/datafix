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
            ->where('route', '/prpfile')
            ->update(['route' => '/profile']);

        Cache::forget('navigation_menus_tree');
    }

    public function down(): void
    {
        if (! Schema::hasTable('navigation_menus')) {
            return;
        }

        DB::table('navigation_menus')->where('id', 8)->update(['route' => '/prpfile']);

        Cache::forget('navigation_menus_tree');
    }
};
