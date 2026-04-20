<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Removes legacy factory demo departments (MAINT, PROD, WH, …).
 * School workflows use SCH_* departments from SchoolEFormTemplateSeeder only.
 *
 *   php artisan db:seed --class=DepartmentSeeder
 *
 * Purge also runs automatically when SchoolEFormTemplateSeeder runs (IndustryTemplateSeeder).
 */
class DepartmentSeeder extends Seeder
{
    /** @var list<string> */
    public const LEGACY_FACTORY_DEPARTMENT_CODES = [
        'MAINT',
        'PROD',
        'WH',
        'FAC',
        'IT',
        'GA',
    ];

    public function run(): void
    {
        $n = self::purgeLegacyFactoryDepartments();
        $this->command?->info(
            $n > 0
                ? "DepartmentSeeder: removed {$n} legacy factory department(s)."
                : 'DepartmentSeeder: no legacy factory departments to remove.'
        );
    }

    /**
     * Delete factory CMMS demo departments and clear user links. Other FKs use nullOnDelete or cascade.
     */
    public static function purgeLegacyFactoryDepartments(): int
    {
        $ids = Department::query()
            ->whereIn('code', self::LEGACY_FACTORY_DEPARTMENT_CODES)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        User::query()->whereIn('department_id', $ids)->update(['department_id' => null]);

        $count = $ids->count();
        Department::query()->whereIn('id', $ids)->delete();

        return $count;
    }
}
