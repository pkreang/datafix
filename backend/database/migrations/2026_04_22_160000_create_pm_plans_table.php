<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('frequency_type', ['date', 'runtime']);
            $table->unsignedInteger('interval_days')->nullable();
            $table->decimal('interval_hours', 10, 2)->nullable();
            $table->timestamp('last_executed_at')->nullable();
            $table->decimal('last_executed_runtime', 12, 2)->nullable();
            $table->date('next_due_at')->nullable();
            $table->decimal('next_due_runtime', 12, 2)->nullable();
            $table->foreignId('assigned_to_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'next_due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_plans');
    }
};
