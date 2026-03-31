<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_instance_steps', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_approvals')->default(1)->after('approver_ref');
            $table->json('approved_by')->nullable()->after('min_approvals');
        });
    }

    public function down(): void
    {
        Schema::table('approval_instance_steps', function (Blueprint $table) {
            $table->dropColumn(['min_approvals', 'approved_by']);
        });
    }
};
