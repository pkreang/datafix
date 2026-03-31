<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_number_configs', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->unique();
            $table->string('prefix');
            $table->unsignedTinyInteger('digit_count')->default(5);
            $table->string('reset_mode', 20)->default('none'); // none, yearly, monthly
            $table->boolean('include_year')->default(true);
            $table->boolean('include_month')->default(false);
            $table->unsignedInteger('last_number')->default(0);
            $table->date('last_reset_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_number_configs');
    }
};
