<?php

namespace Database\Seeders;

use App\Models\ReportDashboard;
use App\Models\ReportDashboardWidget;
use App\Models\User;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent — skip if dashboards already exist
        if (ReportDashboard::count() > 0) {
            return;
        }

        $admin   = User::where('email', 'admin@example.com')->first();
        $adminId = $admin?->id ?? 1;

        $this->seedCmmsOverview($adminId);
        $this->seedMaintenanceDashboard($adminId);
        $this->seedInventoryDashboard($adminId);
    }

    private function seedCmmsOverview(int $adminId): void
    {
        $dashboard = ReportDashboard::create([
            'name'                => 'CMMS Overview',
            'description'         => 'ภาพรวม CMMS สำหรับผู้บริหาร',
            'layout_columns'      => 2,
            'visibility'          => 'all',
            'required_permission' => null,
            'is_active'           => true,
            'created_by'          => $adminId,
        ]);

        $widgets = [
            ['title' => 'Repair Requests by Status',          'widget_type' => 'chart',  'data_source' => 'repair_requests',        'config' => ['chart_type' => 'pie',  'group_by' => 'status',                  'aggregation' => 'count'],                                'col_span' => 1, 'sort_order' => 1],
            ['title' => 'Repair Requests – Monthly Trend',    'widget_type' => 'chart',  'data_source' => 'repair_requests',        'config' => ['chart_type' => 'line', 'group_by' => 'status',                  'aggregation' => 'count', 'date_field' => 'created_at'], 'col_span' => 1, 'sort_order' => 2],
            ['title' => 'PM/AM Plans by Status',              'widget_type' => 'chart',  'data_source' => 'pm_am_plans',            'config' => ['chart_type' => 'bar',  'group_by' => 'status',                  'aggregation' => 'count'],                                'col_span' => 1, 'sort_order' => 3],
            ['title' => 'Equipment by Category',              'widget_type' => 'chart',  'data_source' => 'equipment',              'config' => ['chart_type' => 'bar',  'group_by' => 'equipment_category_id',   'aggregation' => 'count'],                                'col_span' => 1, 'sort_order' => 4],
            ['title' => 'Top Departments by Repair Requests', 'widget_type' => 'table',  'data_source' => 'repair_requests',        'config' => ['columns' => ['department_id', 'status', 'reference_no', 'created_at'], 'per_page' => 5],                               'col_span' => 2, 'sort_order' => 5],
        ];

        foreach ($widgets as $w) {
            ReportDashboardWidget::create(array_merge($w, ['dashboard_id' => $dashboard->id]));
        }
    }

    private function seedMaintenanceDashboard(int $adminId): void
    {
        $dashboard = ReportDashboard::create([
            'name'                => 'Maintenance Dashboard',
            'description'         => 'งานบำรุงรักษาสำหรับช่างเทคนิค',
            'layout_columns'      => 2,
            'visibility'          => 'all',
            'required_permission' => null,
            'is_active'           => true,
            'created_by'          => $adminId,
        ]);

        $widgets = [
            ['title' => 'Pending PM/AM Plans',       'widget_type' => 'table', 'data_source' => 'pm_am_plans',    'config' => ['columns' => ['reference_no', 'status', 'department_id', 'created_at'], 'per_page' => 10], 'col_span' => 2, 'sort_order' => 1],
            ['title' => 'Pending Repair Requests',   'widget_type' => 'table', 'data_source' => 'repair_requests','config' => ['columns' => ['reference_no', 'status', 'department_id', 'created_at'], 'per_page' => 10], 'col_span' => 2, 'sort_order' => 2],
            ['title' => 'Low Stock Spare Parts',     'widget_type' => 'table', 'data_source' => 'spare_parts',    'config' => ['columns' => ['code', 'name', 'current_stock', 'min_stock'],            'per_page' => 10], 'col_span' => 2, 'sort_order' => 3],
        ];

        foreach ($widgets as $w) {
            ReportDashboardWidget::create(array_merge($w, ['dashboard_id' => $dashboard->id]));
        }
    }

    private function seedInventoryDashboard(int $adminId): void
    {
        $dashboard = ReportDashboard::create([
            'name'                => 'Inventory Dashboard',
            'description'         => 'ภาพรวมอะไหล่และคลังสินค้า',
            'layout_columns'      => 2,
            'visibility'          => 'all',
            'required_permission' => null,
            'is_active'           => true,
            'created_by'          => $adminId,
        ]);

        $widgets = [
            ['title' => 'Total Inventory Value',          'widget_type' => 'metric', 'data_source' => 'spare_parts',            'config' => ['aggregation' => 'sum',   'field' => 'unit_cost'],                                                                             'col_span' => 1, 'sort_order' => 1],
            ['title' => 'Low Stock Count',                'widget_type' => 'metric', 'data_source' => 'spare_parts',            'config' => ['aggregation' => 'count', 'field' => 'id'],                                                                                    'col_span' => 1, 'sort_order' => 2],
            ['title' => 'Stock Level by Category',        'widget_type' => 'chart',  'data_source' => 'spare_parts',            'config' => ['chart_type' => 'bar', 'group_by' => 'equipment_category_id', 'aggregation' => 'sum', 'field' => 'current_stock'],             'col_span' => 2, 'sort_order' => 3],
            ['title' => 'Transactions – Receive vs Issue','widget_type' => 'chart',  'data_source' => 'spare_part_transactions', 'config' => ['chart_type' => 'bar', 'group_by' => 'transaction_type',       'aggregation' => 'sum', 'field' => 'quantity'],                'col_span' => 2, 'sort_order' => 4],
            ['title' => 'Low Stock Items',                'widget_type' => 'table',  'data_source' => 'spare_parts',            'config' => ['columns' => ['code', 'name', 'current_stock', 'min_stock', 'unit_cost'], 'per_page' => 10],                                  'col_span' => 2, 'sort_order' => 5],
        ];

        foreach ($widgets as $w) {
            ReportDashboardWidget::create(array_merge($w, ['dashboard_id' => $dashboard->id]));
        }
    }
}
