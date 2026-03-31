<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'package_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropConstrainedForeignId('package_id');
            });
        }

        Schema::dropIfExists('packages');
    }

    public function down(): void
    {
        // Intentionally empty: packages removed; redeploy model is 1 customer = 1 company.
    }
};
