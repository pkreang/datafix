<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_instance_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_instance_id')->constrained('approval_instances')->cascadeOnDelete();
            $table->unsignedSmallInteger('step_no');
            $table->string('stage_name');
            $table->string('approver_type');
            $table->string('approver_ref');
            $table->unsignedBigInteger('acted_by_user_id')->nullable();
            $table->enum('action', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comment')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(['approval_instance_id', 'step_no']);
            $table->index(['acted_by_user_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_instance_steps');
    }
};
