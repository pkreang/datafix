<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_form_fields', function (Blueprint $table) {
            $table->string('label_en', 255)->nullable()->after('label');
            $table->string('label_th', 255)->nullable()->after('label_en');
        });

        // Backfill: copy existing `label` into both locales as a safe default so
        // old forms keep rendering. Admin can then split them in the form builder.
        DB::table('document_form_fields')
            ->whereNull('label_en')
            ->update(['label_en' => DB::raw('`label`')]);
        DB::table('document_form_fields')
            ->whereNull('label_th')
            ->update(['label_th' => DB::raw('`label`')]);
    }

    public function down(): void
    {
        Schema::table('document_form_fields', function (Blueprint $table) {
            $table->dropColumn(['label_en', 'label_th']);
        });
    }
};
