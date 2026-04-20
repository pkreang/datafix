<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_form_fields', function (Blueprint $table) {
            $table->json('visibility_rules')->nullable()->after('editable_by');
            $table->json('validation_rules')->nullable()->after('visibility_rules');
        });
    }

    public function down(): void
    {
        Schema::table('document_form_fields', function (Blueprint $table) {
            $table->dropColumn(['visibility_rules', 'validation_rules']);
        });
    }
};
