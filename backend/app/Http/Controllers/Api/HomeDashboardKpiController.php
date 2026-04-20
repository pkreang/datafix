<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalInstance;
use App\Models\DocumentFormSubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeDashboardKpiController extends Controller
{
    private const SCHOOL_DOCUMENT_TYPES = [
        'school_leave_request',
        'school_procurement',
        'school_activity',
    ];

    private const ALLOWED_CARDS = [
        'school_pending_approvals',
        'school_submissions_this_month',
        'school_my_submissions_this_month',
        'school_my_pending_requests',
        'school_draft_forms',
        'active_users',
    ];

    /** GET /api/v1/dashboard/kpi/{card} */
    public function show(Request $request, string $card): JsonResponse
    {
        $user = $request->user();

        $value = match ($card) {
            'school_pending_approvals' => $this->schoolPendingApprovals($user),
            'school_submissions_this_month' => $this->schoolSubmissionsThisMonth($user),
            'school_my_submissions_this_month' => $this->schoolMySubmissionsThisMonth($user),
            'school_my_pending_requests' => $this->schoolMyPendingRequests($user),
            'school_draft_forms' => $this->schoolDraftForms($user),
            'active_users' => $this->activeUsers($user),
            default => null,
        };

        if ($value === null) {
            return response()->json(['error' => __('api.unknown_kpi_card')], 404);
        }

        return response()->json($value);
    }

    /** POST /api/v1/dashboard/kpi-config */
    public function saveConfig(Request $request): JsonResponse
    {
        $allowed = implode(',', self::ALLOWED_CARDS);
        $validated = $request->validate([
            'cards' => 'required|array',
            'cards.*' => 'string|in:'.$allowed,
        ]);

        $user = $request->user();
        $user->dashboard_config = ['cards' => $validated['cards']];
        $user->save();

        return response()->json(['ok' => true]);
    }

    private function schoolPendingApprovals($user): array
    {
        $value = ApprovalInstance::query()
            ->whereIn('document_type', self::SCHOOL_DOCUMENT_TYPES)
            ->where('status', 'pending')
            ->count();

        return ['value' => $value];
    }

    private function schoolSubmissionsThisMonth($user): array
    {
        $now = Carbon::now();
        $thisMonth = $this->schoolSubmittedQuery()
            ->whereYear('updated_at', $now->year)
            ->whereMonth('updated_at', $now->month)
            ->count();
        $lastMonth = $this->schoolSubmittedQuery()
            ->whereYear('updated_at', $now->copy()->subMonth()->year)
            ->whereMonth('updated_at', $now->copy()->subMonth()->month)
            ->count();

        $delta = $thisMonth - $lastMonth;
        $direction = $delta >= 0 ? 'up' : 'down';

        return ['value' => $thisMonth, 'delta' => abs($delta), 'delta_direction' => $direction];
    }

    private function schoolMySubmissionsThisMonth($user): array
    {
        $now = Carbon::now();
        $value = $this->schoolSubmittedQuery()
            ->where('user_id', $user->id)
            ->whereYear('updated_at', $now->year)
            ->whereMonth('updated_at', $now->month)
            ->count();

        return ['value' => $value];
    }

    private function schoolMyPendingRequests($user): array
    {
        $value = ApprovalInstance::query()
            ->whereIn('document_type', self::SCHOOL_DOCUMENT_TYPES)
            ->where('status', 'pending')
            ->where('requester_user_id', $user->id)
            ->count();

        return ['value' => $value];
    }

    private function schoolDraftForms($user): array
    {
        $value = DocumentFormSubmission::query()
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->whereHas('form', fn ($q) => $q->whereIn('document_type', self::SCHOOL_DOCUMENT_TYPES))
            ->count();

        return ['value' => $value];
    }

    private function activeUsers($user): array
    {
        $value = User::query()->where('is_active', true)->count();

        return ['value' => $value];
    }

    private function schoolSubmittedQuery()
    {
        return DocumentFormSubmission::query()
            ->where('status', 'submitted')
            ->whereHas('form', fn ($q) => $q->whereIn('document_type', self::SCHOOL_DOCUMENT_TYPES));
    }
}
