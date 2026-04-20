<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_form_column_annotations', function (Blueprint $t) {
            $t->id();
            $t->string('table_name', 64);
            $t->string('column_name', 64);
            $t->string('label_en', 255)->nullable();
            $t->string('label_th', 255)->nullable();
            $t->string('ui_type', 30);
            $t->integer('sort_order')->default(0);
            $t->integer('col_span')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->boolean('is_required')->default(false);
            $t->text('placeholder')->nullable();
            $t->json('options')->nullable();
            $t->json('visibility_rules')->nullable();
            $t->json('validation_rules')->nullable();
            $t->timestamps();

            $t->unique(['table_name', 'column_name'], 'dfca_table_column_unique');
            $t->index(['table_name', 'is_visible', 'sort_order'], 'dfca_table_visible_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_form_column_annotations');
    }
};
