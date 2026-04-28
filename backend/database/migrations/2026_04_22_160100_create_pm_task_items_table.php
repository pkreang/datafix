<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_task_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_no');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('description', 500);
            $table->enum('task_type', ['visual', 'measurement', 'lubrication', 'replacement', 'cleaning', 'tightening', 'other']);
            $table->string('expected_value', 255)->nullable();
            $table->string('unit', 50)->nullable();
            $table->boolean('requires_photo')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->foreignId('spare_part_id')->nullable()->constrained('spare_parts')->nullOnDelete();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->boolean('loto_required')->default(false);
            $table->boolean('is_critical')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_task_items');
    }
};
