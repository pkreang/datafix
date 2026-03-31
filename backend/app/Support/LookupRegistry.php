<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentLocation;
use App\Models\Position;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Support\Collection;

class LookupRegistry
{
    /**
     * Available lookup sources with their configuration.
     */
    public static function sources(): array
    {
        return [
            'user' => [
                'model' => User::class,
                'value' => 'id',
                'display' => 'name',
                'label_en' => 'User',
                'label_th' => 'ผู้ใช้',
                'has_active' => true,
            ],
            'equipment' => [
                'model' => Equipment::class,
                'value' => 'id',
                'display' => 'code_name',
                'label_en' => 'Equipment',
                'label_th' => 'อุปกรณ์',
                'has_active' => true,
            ],
            'company' => [
                'model' => Company::class,
                'value' => 'id',
                'display' => 'name',
                'label_en' => 'Company',
                'label_th' => 'บริษัท',
                'has_active' => true,
            ],
            'branch' => [
                'model' => Branch::class,
                'value' => 'id',
                'display' => 'name',
                'label_en' => 'Branch',
                'label_th' => 'สาขา',
                'has_active' => true,
            ],
            'department' => [
                'model' => Department::class,
                'value' => 'id',
                'display' => 'name',
                'label_en' => 'Department',
                'label_th' => 'แผนก',
                'has_active' => true,
            ],
            'position' => [
                'model' => Position::class,
                'value' => 'id',
                'display' => 'name',
                'label_en' => 'Position',
                'label_th' => 'ตำแหน่ง',
                'has_active' => true,
            ],
            'spare_part' => [
                'model' => SparePart::class,
                'value' => 'id',
                'display' => 'code_name',
                'label_en' => 'Spare Part',
                'label_th' => 'อะไหล่',
                'has_active' => true,
            ],
            'equipment_category' => [
                'model' => EquipmentCategory::class,
                'value' => 'id',
                'display' => 'name',
                'label_en' => 'Equipment Category',
                'label_th' => 'หมวดอุปกรณ์',
                'has_active' => true,
            ],
            'equipment_location' => [
                'model' => EquipmentLocation::class,
                'value' => 'id',
                'display' => 'name',
                'label_en' => 'Equipment Location',
                'label_th' => 'สถานที่อุปกรณ์',
                'has_active' => true,
            ],
        ];
    }

    /**
     * Get the list of valid source keys.
     */
    public static function sourceKeys(): array
    {
        return array_keys(static::sources());
    }

    /**
     * Get items for a given source, optionally filtered.
     *
     * @return Collection<int, array{value: mixed, display: string}>
     */
    public static function getItems(string $source, ?array $filters = null): Collection
    {
        $config = static::sources()[$source] ?? null;

        if (! $config) {
            return collect();
        }

        $query = $config['model']::query()->orderBy('name');

        if ($config['has_active']) {
            $query->where('is_active', true);
        }

        if ($filters) {
            foreach ($filters as $column => $val) {
                if (preg_match('/^[a-z_]+$/', $column) && $val !== null && $val !== '') {
                    $query->where($column, $val);
                }
            }
        }

        $displayField = $config['display'];

        return $query->get()->map(function ($item) use ($config, $displayField) {
            $display = $displayField === 'code_name'
                ? "[{$item->code}] {$item->name}"
                : $item->{$displayField};

            return [
                'value' => $item->{$config['value']},
                'display' => $display,
            ];
        });
    }

    /**
     * Known foreign key relationships for cascading lookups.
     */
    public static function cascadingRelations(): array
    {
        return [
            'branch' => ['company' => 'company_id'],
            'equipment' => [
                'equipment_category' => 'equipment_category_id',
                'equipment_location' => 'equipment_location_id',
                'company' => 'company_id',
                'branch' => 'branch_id',
            ],
            'spare_part' => [
                'equipment_category' => 'equipment_category_id',
                'company' => 'company_id',
                'branch' => 'branch_id',
            ],
            'user' => [
                'company' => 'company_id',
                'branch' => 'branch_id',
                'department' => 'department_id',
            ],
        ];
    }

    /**
     * Suggest the foreign key when a child source depends on a parent source.
     */
    public static function suggestForeignKey(string $childSource, string $parentSource): ?string
    {
        return static::cascadingRelations()[$childSource][$parentSource] ?? null;
    }
}
