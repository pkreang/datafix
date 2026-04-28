<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pm_task_item_id')->nullable()->constrained('pm_task_items')->nullOnDelete();
            // Snapshot fields (copied from pm_task_items at WO generation time)
            $table->unsignedInteger('step_no');
            $table->string('description', 500);
            $table->string('task_type', 30);
            $table->string('expected_value', 255)->nullable();
            $table->string('unit', 50)->nullable();
            $table->boolean('requires_photo')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->foreignId('spare_part_id')->nullable()->constrained('spare_parts')->nullOnDelete();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->boolean('loto_required')->default(false);
            $table->boolean('is_critical')->default(false);
            // Execution data (populated by technician in Phase 2B UI)
            $table->enum('status', ['pending', 'done', 'skipped', 'fail'])->default('pending');
            $table->string('actual_value', 255)->nullable();
            $table->text('note')->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_work_order_items');
    }
};
