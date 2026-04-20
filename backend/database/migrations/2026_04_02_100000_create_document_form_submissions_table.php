<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('document_forms')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->unsignedBigInteger('approval_instance_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['form_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_form_submissions');
    }
};
