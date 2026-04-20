<?php

namespace Database\Seeders;

use App\Models\ReportDashboard;
use App\Models\ReportDashboardWidget;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Factory (CMMS-flavored) demo dashboard. Mirrors DashboardSeeder's shape but uses
 * repair_requests and equipment data sources so a fresh `composer switch:factory`
 * shows populated reports out-of-box.
 */
class FactoryDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $adminId = $admin?->id ?? 1;

        $dashboard = ReportDashboard::updateOrCreate(
            ['name' => 'CMMS — Repair Requests Overview'],
            [
                'description' => 'ภาพรวมใบแจ้งซ่อม + เครื่องจักรยอดแจ้ง',
                'layout_columns' => 2,
                'visibility' => 'all',
                'required_permission' => null,
                'is_active' => true,
                'created_by' => $adminId,
            ]
        );

        // Rebuild widgets each run so schema tweaks propagate on `switch:factory`.
        $dashboard->widgets()->delete();

        $widgets = [
            [
                'title' => 'ใบแจ้งซ่อมทั้งหมด',
                'widget_type' => 'metric',
                'data_source' => 'repair_requests',
                'config' => ['aggregation' => 'count', 'field' => 'id'],
                'col_span' => 1,
                'sort_order' => 1,
            ],
            [
                'title' => 'ตามสถานะ',
                'widget_type' => 'chart',
                'data_source' => 'repair_requests',
                'config' => ['chart_type' => 'donut', 'group_by' => 'status', 'aggregation' => 'count'],
                'col_span' => 1,
                'sort_order' => 2,
            ],
            [
                'title' => 'ตามแผนกผู้ขอ',
                'widget_type' => 'chart',
                'data_source' => 'repair_requests',
                'config' => ['chart_type' => 'bar', 'group_by' => 'department_id', 'aggregation' => 'count'],
                'col_span' => 2,
                'sort_order' => 3,
            ],
            [
                'title' => 'รายการล่าสุด',
                'widget_type' => 'table',
                'data_source' => 'repair_requests',
                'config' => [
                    'columns' => ['reference_no', 'status', 'department_id', 'created_at'],
                    'per_page' => 10,
                ],
                'col_span' => 2,
                'sort_order' => 4,
            ],
        ];

        foreach ($widgets as $w) {
            ReportDashboardWidget::create(array_merge($w, ['dashboard_id' => $dashboard->id]));
        }
    }
}
