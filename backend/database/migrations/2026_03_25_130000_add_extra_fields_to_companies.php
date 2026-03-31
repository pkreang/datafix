<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('tax_id', 20)->nullable()->after('code');
            $table->string('business_type', 100)->nullable()->after('tax_id');
            $table->string('fax', 20)->nullable()->after('phone');
            $table->string('website')->nullable()->after('fax');
            $table->text('description')->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['tax_id', 'business_type', 'fax', 'website', 'description']);
        });
    }
};
