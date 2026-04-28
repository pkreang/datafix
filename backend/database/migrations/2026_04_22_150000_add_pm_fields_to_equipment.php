<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 of PM/AM enablement — add lifecycle + PM-trigger columns to equipment.
 * - manufacturer / model: vendor reference, standard PM procedure library key
 * - criticality: A/B/C (TPM convention) — drives PM intensity + escalation
 * - runtime_hours: cumulative runtime for hour-based PM triggers
 * - purchase_date: separate from installed_date — used for useful-life calc
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('manufacturer', 255)->nullable()->after('serial_number');
            $table->string('model', 255)->nullable()->after('manufacturer');
            $table->char('criticality', 1)->nullable()->after('status');
            $table->decimal('runtime_hours', 12, 2)->nullable()->after('warranty_expiry');
            $table->date('purchase_date')->nullable()->after('installed_date');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['manufacturer', 'model', 'criticality', 'runtime_hours', 'purchase_date']);
        });
    }
};
