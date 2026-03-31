<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_form_workflow_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('document_form_workflow_policies')->cascadeOnDelete();
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['policy_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_form_workflow_ranges');
    }
};
