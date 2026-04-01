<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\SparePart;
use App\Models\ApprovalInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeDashboardKpiController extends Controller
{
    /** GET /api/v1/dashboard/kpi/{card} */
    public function show(Request $request, string $card): JsonResponse
    {
        $user = $request->user();

        $value = match ($card) {
            'repair_pending'      => $this->repairPending($user),
            'repair_this_month'   => $this->repairThisMonth($user),
            'pm_pending'          => $this->pmPending($user),
            'pm_this_week'        => $this->pmThisWeek($user),
            'spare_low_stock'     => $this->spareLowStock($user),
            'equipment_active'    => $this->equipmentActive($user),
            'my_pending_repairs'  => $this->myPendingRepairs($user),
            default               => null,
        };

        if ($value === null) {
            return response()->json(['error' => 'Unknown KPI card'], 404);
        }

        return response()->json($value);
    }

    /** POST /api/v1/dashboard/kpi-config */
    public function saveConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cards'   => 'required|array',
            'cards.*' => 'string|in:repair_pending,repair_this_month,pm_pending,pm_this_week,spare_low_stock,equipment_active,my_pending_repairs',
        ]);

        $user = $request->user();
        $user->dashboard_config = ['cards' => $validated['cards']];
        $user->save();

        return response()->json(['ok' => true]);
    }

    private function repairPending($user): array
    {
        $value = ApprovalInstance::where('document_type', 'repair_request')
            ->where('status', 'pending')
            ->count();

        return ['value' => $value];
    }

    private function repairThisMonth($user): array
    {
        $now       = Carbon::now();
        $thisMonth = ApprovalInstance::where('document_type', 'repair_request')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();
        $lastMonth = ApprovalInstance::where('document_type', 'repair_request')
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->whereMonth('created_at', $now->copy()->subMonth()->month)
            ->count();

        $delta     = $thisMonth - $lastMonth;
        $direction = $delta >= 0 ? 'up' : 'down';

        return ['value' => $thisMonth, 'delta' => abs($delta), 'delta_direction' => $direction];
    }

    private function pmPending($user): array
    {
        $value = ApprovalInstance::where('document_type', 'pm_am_plan')
            ->where('status', 'pending')
            ->count();

        return ['value' => $value];
    }

    private function pmThisWeek($user): array
    {
        $value = ApprovalInstance::where('document_type', 'pm_am_plan')
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->count();

        return ['value' => $value];
    }

    private function spareLowStock($user): array
    {
        $value = SparePart::whereColumn('current_stock', '<', 'min_stock')
            ->where('is_active', true)
            ->count();

        return ['value' => $value];
    }

    private function equipmentActive($user): array
    {
        $value = Equipment::where('is_active', true)->count();

        return ['value' => $value];
    }

    private function myPendingRepairs($user): array
    {
        $value = ApprovalInstance::where('document_type', 'repair_request')
            ->where('status', 'pending')
            ->where('requester_user_id', $user->id)
            ->count();

        return ['value' => $value];
    }
}
