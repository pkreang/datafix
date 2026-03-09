<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar', 500)->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->boolean('is_super_admin')->default(false)->after('is_active');
            $table->timestamp('last_active_at')->nullable()->after('is_super_admin');
            $table->softDeletes();

            $table->index('is_active');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['deleted_at']);
            $table->dropColumn([
                'avatar',
                'is_active',
                'is_super_admin',
                'last_active_at',
                'deleted_at',
            ]);
        });
    }
};
