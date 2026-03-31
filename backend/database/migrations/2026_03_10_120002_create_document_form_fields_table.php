<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('document_forms')->cascadeOnDelete();
            $table->string('field_key');
            $table->string('label');
            $table->string('field_type');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(1);
            $table->string('placeholder')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['form_id', 'field_key']);
            $table->index(['form_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_form_fields');
    }
};
