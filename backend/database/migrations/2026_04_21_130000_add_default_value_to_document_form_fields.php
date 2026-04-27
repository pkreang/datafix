<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_form_fields', function (Blueprint $table) {
            $table->string('default_value')->nullable()->after('placeholder');
        });
    }

    public function down(): void
    {
        Schema::table('document_form_fields', function (Blueprint $table) {
            $table->dropColumn('default_value');
        });
    }
};
