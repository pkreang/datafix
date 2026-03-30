<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportDashboard;
use App\Models\ReportDashboardWidget;
use App\Support\DataSourceRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardWidgetDataController extends Controller
{
    public function show(Request $request, ReportDashboard $dashboard, ReportDashboardWidget $widget): JsonResponse
    {
        // 1. Verify widget belongs to dashboard
        if ($widget->dashboard_id !== $dashboard->id) {
            abort(404);
        }

        // 2. Permission check
        if ($dashboard->visibility === 'permission' && $dashboard->required_permission) {
            $user = $request->user();
            if (!$user) abort(401);
            // Super admin check: look at users.is_super_admin column OR hasRole('super-admin')
            $isSuperAdmin = $user->is_super_admin ?? false;
            if (!$isSuperAdmin && !$user->hasPermissionTo($dashboard->required_permission)) {
                abort(403);
            }
        }

        // 3. Validate and get global filters from request
        $request->validate([
            'date_from'     => 'nullable|date',
            'date_to'       => 'nullable|date',
            'department_id' => 'nullable|integer',
        ]);

        $dateFrom     = $request->query('date_from');     // Y-m-d string or null
        $dateTo       = $request->query('date_to');       // Y-m-d string or null
        $departmentId = $request->query('department_id'); // int or null

        // 4. Build query
        $config = $widget->config ?? [];
        $source = DataSourceRegistry::get($widget->data_source);
        if (!$source) {
            return response()->json(['error' => 'Unknown data source'], 422);
        }

        try {
            $query = DataSourceRegistry::query($widget->data_source);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => 'Unknown data source'], 422);
        }

        // Apply global date filter (if source has date_fields and date_from/to set)
        $dateField = $config['date_field'] ?? null;
        if ($dateField && isset($source['date_fields'][$dateField])) {
            if ($dateFrom) $query->whereDate($dateField, '>=', $dateFrom);
            if ($dateTo)   $query->whereDate($dateField, '<=', $dateTo);
        }

        // Apply global department filter (only for sources that have department_id)
        if ($departmentId && isset($source['filter_fields']['department_id'])) {
            $query->where('department_id', $departmentId);
        }

        // 5. Build response based on widget_type
        return match ($widget->widget_type) {
            'metric' => $this->metricData($query, $config, $source),
            'chart'  => $this->chartData($query, $config, $source),
            'table'  => $this->tableData($query, $config, $source, $request),
            default  => response()->json(['error' => 'Unknown widget type'], 422),
        };
    }

    private function metricData($query, array $config, array $source): JsonResponse
    {
        $aggregation = $config['aggregation'] ?? 'count';
        $field       = $config['field'] ?? 'id';

        // Whitelist field against source's aggregate_fields
        $allowedFields = array_keys($source['aggregate_fields'] ?? []);
        if (!in_array($field, $allowedFields, true)) {
            $field       = 'id';
            $aggregation = 'count';
        }

        $value = match ($aggregation) {
            'count' => $query->count(),
            'sum'   => $query->sum($field),
            'avg'   => round((float) $query->avg($field), 2),
            default => $query->count(),
        };

        return response()->json(['value' => $value]);
    }

    private function chartData($query, array $config, array $source): JsonResponse
    {
        $groupBy     = $config['group_by'] ?? null;
        $aggregation = $config['aggregation'] ?? 'count';
        $field       = $config['field'] ?? 'id';

        // Whitelist field and groupBy against source definition
        $allowedFields = array_keys($source['aggregate_fields'] ?? []);
        if (!in_array($field, $allowedFields, true)) {
            $field = 'id';
        }

        $allowedGroupBy = array_keys($source['group_by_fields'] ?? []);
        if ($groupBy && !in_array($groupBy, $allowedGroupBy, true)) {
            return response()->json(['labels' => [], 'datasets' => [['data' => []]]]);
        }

        if (!$groupBy) {
            return response()->json(['labels' => [], 'datasets' => [['data' => []]]]);
        }

        $results = $query
            ->select($groupBy, DB::raw(match ($aggregation) {
                'sum'   => "SUM({$field}) as agg_value",
                'avg'   => "AVG({$field}) as agg_value",
                default => 'COUNT(*) as agg_value',
            }))
            ->groupBy($groupBy)
            ->orderByDesc('agg_value')
            ->limit(20)
            ->get();

        $labels = $results->pluck($groupBy)->map(fn ($v) => (string) ($v ?? 'N/A'))->toArray();
        $data   = $results->pluck('agg_value')->map(fn ($v) => (float) $v)->toArray();

        return response()->json([
            'labels'   => $labels,
            'datasets' => [['data' => $data]],
        ]);
    }

    private function tableData($query, array $config, array $source, Request $request): JsonResponse
    {
        $columns = $config['columns'] ?? array_keys($source['display_columns'] ?? []);
        $perPage = min(max(1, (int) ($config['per_page'] ?? 10)), 100);
        $page    = max(1, (int) ($request->query('page', 1)));

        // Select only configured columns (whitelist via source display_columns)
        $allowedColumns = array_keys($source['display_columns'] ?? []);
        $selectColumns  = count($columns)
            ? array_intersect($columns, $allowedColumns)
            : $allowedColumns;

        if (empty($selectColumns)) {
            $selectColumns = ['id'];
        }

        // Add primary key if not present (needed for pagination)
        if (!in_array('id', $selectColumns)) {
            array_unshift($selectColumns, 'id');
        }

        $total = $query->count();
        $rows  = (clone $query)
            ->select($selectColumns)
            ->orderByDesc('id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn ($row) => $row->only($selectColumns))
            ->toArray();

        return response()->json([
            'columns'       => $selectColumns,
            'column_labels' => array_map(
                fn ($col) => $source['display_columns'][$col] ?? $col,
                $selectColumns
            ),
            'rows'          => $rows,
            'pagination'    => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => (int) ceil($total / max($perPage, 1)),
            ],
        ]);
    }
}
