<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50)->unique();
            $table->enum('status', ['due', 'in_progress', 'done', 'skipped', 'overdue', 'cancelled'])->default('due');
            $table->date('due_date');
            $table->timestamp('generated_at');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('findings')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_work_orders');
    }
};
