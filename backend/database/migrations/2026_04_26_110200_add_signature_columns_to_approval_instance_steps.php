<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_instance_steps', function (Blueprint $table) {
            $table->boolean('require_signature')->default(false)->after('approved_by');
            $table->text('signature_image')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('approval_instance_steps', function (Blueprint $table) {
            $table->dropColumn(['require_signature', 'signature_image']);
        });
    }
};
