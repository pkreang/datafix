<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const OLD = 'manage companies';

    private const NEW = 'manage profile';

    public function up(): void
    {
        $table = config('permission.table_names.permissions');
        $navTable = 'navigation_menus';

        $updated = DB::table($table)
            ->where('name', self::OLD)
            ->where('guard_name', 'web')
            ->update(['name' => self::NEW]);

        if ($updated && Schema::hasTable($navTable)) {
            DB::table($navTable)
                ->where('permission', self::OLD)
                ->update(['permission' => self::NEW]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $table = config('permission.table_names.permissions');
        $navTable = 'navigation_menus';

        DB::table($table)
            ->where('name', self::NEW)
            ->where('guard_name', 'web')
            ->update(['name' => self::OLD]);

        if (Schema::hasTable($navTable)) {
            DB::table($navTable)
                ->where('permission', self::NEW)
                ->update(['permission' => self::OLD]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
