<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedSmallInteger('step_no');
            $table->string('name');
            $table->string('approver_type', 32)->default('role');
            $table->string('approver_ref');
            $table->unsignedSmallInteger('min_approvals')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['workflow_id', 'step_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_stages');
    }
};
