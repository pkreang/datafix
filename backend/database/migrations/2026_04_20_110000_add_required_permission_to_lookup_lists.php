<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lookup_lists', function (Blueprint $table) {
            $table->string('required_permission', 100)->nullable()->after('is_system');
        });
    }

    public function down(): void
    {
        Schema::table('lookup_lists', function (Blueprint $table) {
            $table->dropColumn('required_permission');
        });
    }
};
