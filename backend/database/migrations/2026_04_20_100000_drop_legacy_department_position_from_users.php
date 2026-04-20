<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the legacy denormalized `users.department` and `users.position` text columns.
 * The app already uses `department_id` / `position_id` FKs everywhere — these text
 * caches cause drift when admins rename master rows. Display code now resolves via
 * the relation instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('users', 'position')) {
                $table->dropColumn('position');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('external_id');
            $table->string('position')->nullable()->after('department_id');
        });
    }
};
