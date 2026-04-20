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
        if (ReportDashboard::count() > 0) {
            return;
        }

        $admin = User::where('email', 'admin@example.com')->first();
        $adminId = $admin?->id ?? 1;

        $dashboard = ReportDashboard::create([
            'name' => 'School eForm Overview',
            'description' => 'ภาพรวมคำขออนุมัติแบบฟอร์มโรงเรียน',
            'layout_columns' => 2,
            'visibility' => 'all',
            'required_permission' => null,
            'is_active' => true,
            'created_by' => $adminId,
        ]);

        $widgets = [
            [
                'title' => 'Pending approvals (school forms)',
                'widget_type' => 'metric',
                'data_source' => 'school_eforms_pending',
                'config' => ['aggregation' => 'count', 'field' => 'id'],
                'col_span' => 1,
                'sort_order' => 1,
            ],
            [
                'title' => 'School form requests by status',
                'widget_type' => 'chart',
                'data_source' => 'school_eforms',
                'config' => ['chart_type' => 'bar', 'group_by' => 'status', 'aggregation' => 'count'],
                'col_span' => 2,
                'sort_order' => 2,
            ],
        ];

        foreach ($widgets as $w) {
            ReportDashboardWidget::create(array_merge($w, ['dashboard_id' => $dashboard->id]));
        }
    }
}
