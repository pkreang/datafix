<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'password_min_length'              => '8',
            'password_max_length'              => '255',
            'password_require_uppercase'       => '1',
            'password_require_lowercase'       => '1',
            'password_require_number'          => '1',
            'password_require_special'         => '1',
            'password_expires_days'            => '0',
            'password_force_change_first_login' => '1',
            'password_prevent_reuse'           => '0',
            'lockout_max_attempts'             => '5',
            'lockout_duration_minutes'         => '30',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
