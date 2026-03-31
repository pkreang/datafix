<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['companies', 'branches'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('address_no', 50)->nullable()->after('address');
                $table->string('address_building', 255)->nullable()->after('address_no');
                $table->string('address_street', 255)->nullable()->after('address_building');
                $table->string('address_subdistrict', 120)->nullable()->after('address_street');
                $table->string('address_district', 120)->nullable()->after('address_subdistrict');
                $table->string('address_province', 120)->nullable()->after('address_district');
                $table->string('address_postal_code', 10)->nullable()->after('address_province');
            });
        }
    }

    public function down(): void
    {
        foreach (['companies', 'branches'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn([
                    'address_no',
                    'address_building',
                    'address_street',
                    'address_subdistrict',
                    'address_district',
                    'address_province',
                    'address_postal_code',
                ]);
            });
        }
    }
};
