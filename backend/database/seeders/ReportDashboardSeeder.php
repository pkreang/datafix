<?php

namespace Database\Seeders;

use App\Models\ReportDashboard;
use App\Models\ReportDashboardWidget;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('is_super_admin', true)->first();
        $createdBy = $admin?->id;

        $dashboards = [
            [
                'name' => 'Equipment Status',
                'description' => 'Summary of equipment registry — counts by status and category.',
                'layout_columns' => 2,
                'visibility' => 'all',
                'required_permission' => null,
                'is_active' => true,
                'widgets' => [
                    [
                        'title' => 'Total Equipment',
                        'widget_type' => 'metric',
                        'data_source' => 'equipment',
                        'config' => ['aggregation' => 'count', 'field' => null, 'date_field' => 'created_at', 'filters' => []],
                        'col_span' => 0,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Equipment by Status',
                        'widget_type' => 'chart',
                        'data_source' => 'equipment',
                        'config' => ['chart_type' => 'donut', 'aggregation' => 'count', 'field' => null, 'group_by' => 'status', 'date_field' => 'created_at', 'filters' => []],
                        'col_span' => 0,
                        'sort_order' => 2,
                    ],
                    [
                        'title' => 'Equipment List',
                        'widget_type' => 'table',
                        'data_source' => 'equipment',
                        'config' => ['columns' => ['name', 'status', 'category_id', 'location_id'], 'date_field' => 'created_at', 'filters' => [], 'per_page' => 10],
                        'col_span' => 0,
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Spare Parts Inventory',
                'description' => 'Spare parts stock levels and transaction history.',
                'layout_columns' => 2,
                'visibility' => 'all',
                'required_permission' => null,
                'is_active' => true,
                'widgets' => [
                    [
                        'title' => 'Total Spare Parts',
                        'widget_type' => 'metric',
                        'data_source' => 'spare_parts',
                        'config' => ['aggregation' => 'count', 'field' => null, 'date_field' => 'created_at', 'filters' => []],
                        'col_span' => 0,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Recent Transactions',
                        'widget_type' => 'table',
                        'data_source' => 'spare_part_transactions',
                        'config' => ['columns' => ['spare_part_id', 'type', 'quantity', 'created_at'], 'date_field' => 'created_at', 'filters' => [], 'per_page' => 10],
                        'col_span' => 0,
                        'sort_order' => 2,
                    ],
                ],
            ],
        ];

        foreach ($dashboards as $data) {
            $widgets = $data['widgets'];
            unset($data['widgets']);
            $data['created_by'] = $createdBy;

            $dashboard = ReportDashboard::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            foreach ($widgets as $widget) {
                ReportDashboardWidget::updateOrCreate(
                    ['dashboard_id' => $dashboard->id, 'title' => $widget['title']],
                    array_merge($widget, ['dashboard_id' => $dashboard->id])
                );
            }
        }
    }
}
