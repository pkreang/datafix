<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_instance_steps', function (Blueprint $table) {
            $table->mediumText('signature_image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('approval_instance_steps', function (Blueprint $table) {
            $table->text('signature_image')->nullable()->change();
        });
    }
};
