<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_provider', 32)->nullable()->after('email');
            $table->string('external_id', 191)->nullable()->after('auth_provider');
            $table->string('ldap_dn', 512)->nullable()->after('external_id');
            $table->index(['auth_provider', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['auth_provider', 'external_id']);
            $table->dropColumn(['auth_provider', 'external_id', 'ldap_dn']);
        });
    }
};
