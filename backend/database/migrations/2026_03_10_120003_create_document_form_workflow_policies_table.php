<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_form_workflow_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('document_forms')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->boolean('use_amount_condition')->default(false);
            $table->foreignId('workflow_id')->nullable()->constrained('approval_workflows')->nullOnDelete();
            $table->timestamps();

            $table->unique(['form_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_form_workflow_policies');
    }
};
