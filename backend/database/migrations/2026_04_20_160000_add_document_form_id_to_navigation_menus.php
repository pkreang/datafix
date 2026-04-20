<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('navigation_menus', function (Blueprint $table) {
            $table->foreignId('document_form_id')
                ->nullable()
                ->after('permission')
                ->constrained('document_forms')
                ->cascadeOnDelete();
            $table->index('document_form_id');
        });
    }

    public function down(): void
    {
        Schema::table('navigation_menus', function (Blueprint $table) {
            $table->dropForeign(['document_form_id']);
            $table->dropIndex(['document_form_id']);
            $table->dropColumn('document_form_id');
        });
    }
};
