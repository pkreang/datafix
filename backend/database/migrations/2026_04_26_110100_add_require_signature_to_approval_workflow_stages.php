<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_workflow_stages', function (Blueprint $table) {
            $table->boolean('require_signature')->default(false)->after('min_approvals');
        });
    }

    public function down(): void
    {
        Schema::table('approval_workflow_stages', function (Blueprint $table) {
            $table->dropColumn('require_signature');
        });
    }
};
