<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_forms', function (Blueprint $table) {
            $table->string('submission_table')->nullable()->after('layout_columns');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_forms', function (Blueprint $table) {
            $table->dropColumn('submission_table');
        });
    }
};
