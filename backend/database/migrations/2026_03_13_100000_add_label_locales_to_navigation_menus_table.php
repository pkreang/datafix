<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('navigation_menus', function (Blueprint $table) {
            if (! Schema::hasColumn('navigation_menus', 'label_en')) {
                $table->string('label_en', 255)->nullable()->after('label');
            }
            if (! Schema::hasColumn('navigation_menus', 'label_th')) {
                $table->string('label_th', 255)->nullable()->after('label_en');
            }
        });
    }

    public function down(): void
    {
        $cols = array_filter(['label_en', 'label_th'], fn ($c) => Schema::hasColumn('navigation_menus', $c));
        if (! empty($cols)) {
            Schema::table('navigation_menus', function (Blueprint $table) use ($cols) {
                $table->dropColumn($cols);
            });
        }
    }
};
