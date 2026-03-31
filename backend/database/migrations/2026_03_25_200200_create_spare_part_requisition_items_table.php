<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_part_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_instance_id')->constrained('approval_instances')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            $table->decimal('quantity_requested', 12, 2);
            $table->decimal('quantity_issued', 12, 2)->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('approval_instance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_part_requisition_items');
    }
};
