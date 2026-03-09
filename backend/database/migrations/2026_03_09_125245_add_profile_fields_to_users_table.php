<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->default('')->after('id');
            $table->string('last_name')->default('')->after('first_name');
            $table->string('department')->nullable()->after('email');
            $table->string('position')->nullable()->after('department');
            $table->text('remark')->nullable()->after('position');
        });

        DB::table('users')->whereNotNull('name')->update([
            'first_name' => DB::raw('name'),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->default('')->after('id');
        });

        DB::table('users')->update([
            'name' => DB::raw("first_name || ' ' || last_name"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'department', 'position', 'remark']);
        });
    }
};
