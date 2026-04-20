<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookup_lists', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('label_en', 100);
            $table->string('label_th', 100);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('lookup_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lookup_lists')->cascadeOnDelete();
            $table->string('value', 100);
            $table->string('label_en', 255);
            $table->string('label_th', 255);
            $table->foreignId('parent_id')->nullable()->constrained('lookup_list_items')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->unique(['list_id', 'value']);
            $table->index(['list_id', 'is_active', 'sort_order']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lookup_list_items');
        Schema::dropIfExists('lookup_lists');
    }
};
