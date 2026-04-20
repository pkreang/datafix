<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_form_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('fdata_row_id')->nullable()->after('reference_no');
        });
    }

    public function down(): void
    {
        Schema::table('document_form_submissions', function (Blueprint $table) {
            $table->dropColumn('fdata_row_id');
        });
    }
};
