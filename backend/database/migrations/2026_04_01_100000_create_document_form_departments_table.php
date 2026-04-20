<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_form_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('document_forms')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->unique(['form_id', 'department_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_form_departments');
    }
};
