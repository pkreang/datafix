<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_form_fields', function (Blueprint $table) {
            $table->json('visible_to_departments')->nullable()->after('options');
            $table->json('editable_by')->nullable()->after('visible_to_departments');
        });
    }

    public function down(): void
    {
        Schema::table('document_form_fields', function (Blueprint $table) {
            $table->dropColumn(['visible_to_departments', 'editable_by']);
        });
    }
};
