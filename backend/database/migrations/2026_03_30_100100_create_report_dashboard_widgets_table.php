<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('report_dashboards')->cascadeOnDelete();
            $table->string('title', 255);
            $table->enum('widget_type', ['metric', 'chart', 'table']);
            $table->string('data_source', 100);
            $table->json('config')->nullable();
            $table->unsignedTinyInteger('col_span')->default(0);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['dashboard_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_dashboard_widgets');
    }
};
