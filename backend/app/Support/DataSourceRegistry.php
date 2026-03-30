<?php

namespace App\Support;

use App\Models\ApprovalInstance;
use App\Models\Company;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\SparePart;
use App\Models\SparePartTransaction;
use App\Models\User;

class DataSourceRegistry
{
    /**
     * All queryable data source definitions for report dashboard widgets.
     */
    public static function sources(): array
    {
        return [
            'repair_requests' => [
                'label_en'         => 'Repair Requests',
                'label_th'         => 'ใบแจ้งซ่อม',
                'model'            => null,
                'base_query'       => fn () => ApprovalInstance::where('document_type', 'repair_request'),
                'aggregate_fields' => [
                    'id' => 'Count',
                ],
                'group_by_fields'  => [
                    'status'        => 'Status',
                    'department_id' => 'Department',
                ],
                'filter_fields'    => [
                    'status'        => 'Status',
                    'department_id' => 'Department',
                ],
                'date_fields'      => [
                    'created_at' => 'Created At',
                    'updated_at' => 'Updated At',
                ],
                'display_columns'  => [
                    'reference_no'  => 'Ref No',
                    'status'        => 'Status',
                    'department_id' => 'Department',
                    'created_at'    => 'Created At',
                ],
            ],

            'pm_am_plans' => [
                'label_en'         => 'PM/AM Plans',
                'label_th'         => 'แผน PM/AM',
                'model'            => null,
                'base_query'       => fn () => ApprovalInstance::where('document_type', 'pm_am_plan'),
                'aggregate_fields' => [
                    'id' => 'Count',
                ],
                'group_by_fields'  => [
                    'status'        => 'Status',
                    'department_id' => 'Department',
                ],
                'filter_fields'    => [
                    'status'        => 'Status',
                    'department_id' => 'Department',
                ],
                'date_fields'      => [
                    'created_at' => 'Created At',
                    'updated_at' => 'Updated At',
                ],
                'display_columns'  => [
                    'reference_no'  => 'Ref No',
                    'status'        => 'Status',
                    'department_id' => 'Department',
                    'created_at'    => 'Created At',
                ],
            ],

            'equipment' => [
                'label_en'         => 'Equipment',
                'label_th'         => 'อุปกรณ์',
                'model'            => Equipment::class,
                'base_query'       => null,
                'aggregate_fields' => [
                    'id' => 'Count',
                ],
                'group_by_fields'  => [
                    'status'                => 'Status',
                    'equipment_category_id' => 'Category',
                    'equipment_location_id' => 'Location',
                    'company_id'            => 'Company',
                ],
                'filter_fields'    => [
                    'status'                => 'Status',
                    'is_active'             => 'Active',
                    'equipment_category_id' => 'Category',
                    'company_id'            => 'Company',
                ],
                'date_fields'      => [
                    'created_at'     => 'Created At',
                    'installed_date' => 'Installed Date',
                ],
                'display_columns'  => [
                    'name'                  => 'Name',
                    'code'                  => 'Code',
                    'status'                => 'Status',
                    'equipment_category_id' => 'Category',
                    'equipment_location_id' => 'Location',
                    'created_at'            => 'Created At',
                ],
            ],

            'spare_parts' => [
                'label_en'         => 'Spare Parts',
                'label_th'         => 'อะไหล่',
                'model'            => SparePart::class,
                'base_query'       => null,
                'aggregate_fields' => [
                    'id'            => 'Count',
                    'current_stock' => 'Current Stock',
                    'unit_cost'     => 'Unit Cost',
                    'min_stock'     => 'Min Stock',
                ],
                'group_by_fields'  => [
                    'equipment_category_id' => 'Category',
                    'company_id'            => 'Company',
                ],
                'filter_fields'    => [
                    'is_active'             => 'Active',
                    'equipment_category_id' => 'Category',
                    'company_id'            => 'Company',
                ],
                'date_fields'      => [
                    'created_at' => 'Created At',
                ],
                'display_columns'  => [
                    'code'          => 'Code',
                    'name'          => 'Name',
                    'current_stock' => 'Stock',
                    'min_stock'     => 'Min Stock',
                    'unit_cost'     => 'Unit Cost',
                    'created_at'    => 'Created At',
                ],
            ],

            'spare_part_transactions' => [
                'label_en'         => 'Spare Part Transactions',
                'label_th'         => 'รายการอะไหล่',
                'model'            => SparePartTransaction::class,
                'base_query'       => null,
                'aggregate_fields' => [
                    'id'       => 'Count',
                    'quantity' => 'Quantity',
                    'unit_cost' => 'Unit Cost',
                ],
                'group_by_fields'  => [
                    'transaction_type' => 'Transaction Type',
                    'spare_part_id'    => 'Spare Part',
                ],
                'filter_fields'    => [
                    'transaction_type' => 'Transaction Type',
                    'spare_part_id'    => 'Spare Part',
                ],
                'date_fields'      => [
                    'created_at' => 'Created At',
                ],
                'display_columns'  => [
                    'spare_part_id'    => 'Spare Part',
                    'transaction_type' => 'Type',
                    'quantity'         => 'Quantity',
                    'unit_cost'        => 'Unit Cost',
                    'created_at'       => 'Date',
                ],
            ],

            'users' => [
                'label_en'         => 'Users',
                'label_th'         => 'ผู้ใช้',
                'model'            => User::class,
                'base_query'       => null,
                'aggregate_fields' => [
                    'id' => 'Count',
                ],
                'group_by_fields'  => [
                    'department_id' => 'Department',
                    'company_id'    => 'Company',
                ],
                'filter_fields'    => [
                    'is_active'     => 'Active',
                    'department_id' => 'Department',
                    'company_id'    => 'Company',
                ],
                'date_fields'      => [
                    'created_at' => 'Created At',
                ],
                'display_columns'  => [
                    'name'          => 'Name',
                    'email'         => 'Email',
                    'department_id' => 'Department',
                    'created_at'    => 'Created At',
                ],
            ],

            'departments' => [
                'label_en'         => 'Departments',
                'label_th'         => 'แผนก',
                'model'            => Department::class,
                'base_query'       => null,
                'aggregate_fields' => [
                    'id' => 'Count',
                ],
                'group_by_fields'  => [
                    'company_id' => 'Company',
                ],
                'filter_fields'    => [
                    'company_id' => 'Company',
                ],
                'date_fields'      => [
                    'created_at' => 'Created At',
                ],
                'display_columns'  => [
                    'name'       => 'Name',
                    'company_id' => 'Company',
                    'created_at' => 'Created At',
                ],
            ],

            'companies' => [
                'label_en'         => 'Companies',
                'label_th'         => 'บริษัท',
                'model'            => Company::class,
                'base_query'       => null,
                'aggregate_fields' => [
                    'id' => 'Count',
                ],
                'group_by_fields'  => [],
                'filter_fields'    => [],
                'date_fields'      => [
                    'created_at' => 'Created At',
                ],
                'display_columns'  => [
                    'name'       => 'Name',
                    'tax_id'     => 'Tax ID',
                    'created_at' => 'Created At',
                ],
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
     * Returns a base Eloquent Builder for the given source.
     */
    public static function query(string $source): \Illuminate\Database\Eloquent\Builder
    {
        $config = static::sources()[$source] ?? null;

        if (! $config) {
            return User::query()->whereRaw('0=1');
        }

        if (isset($config['base_query']) && $config['base_query'] instanceof \Closure) {
            return ($config['base_query'])();
        }

        if (! empty($config['model'])) {
            return $config['model']::query();
        }

        return User::query()->whereRaw('0=1');
    }

    /**
     * Returns source config array or null if not found.
     */
    public static function get(string $source): ?array
    {
        return static::sources()[$source] ?? null;
    }
}
