<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_workflow_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('document_type');
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['department_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_workflow_bindings');
    }
};
