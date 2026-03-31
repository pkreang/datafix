<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApprovalInstance;
use App\Models\User;
use App\Services\ApprovalFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ApprovalController extends Controller
{
    public function myApprovals(): View
    {
        $user = session('user');
        $userId = (int) ($user['id'] ?? 0);
        $rawRoles = $user['roles'] ?? [];
        $roles = collect($rawRoles)
            ->map(fn ($r) => is_array($r) ? ($r['name'] ?? '') : $r)
            ->filter()
            ->values()
            ->all();

        $actorPositionId = User::query()->whereKey($userId)->value('position_id');

        $instances = ApprovalInstance::query()
            ->from('approval_instances')
            ->with(['steps', 'workflow', 'requester'])
            ->where('approval_instances.status', 'pending')
            ->whereHas('steps', function ($q) use ($userId, $roles, $actorPositionId) {
                $q->where('approval_instance_steps.action', 'pending')
                    ->whereRaw('approval_instance_steps.step_no = approval_instances.current_step_no')
                    ->where(function ($sq) use ($userId, $roles, $actorPositionId) {
                        $sq->where(function ($uq) use ($userId) {
                            $uq->where('approver_type', 'user')
                                ->where('approver_ref', (string) $userId);
                        });
                        if (! empty($roles)) {
                            $sq->orWhere(function ($rq) use ($roles) {
                                $rq->where('approver_type', 'role')
                                    ->whereIn('approver_ref', $roles);
                            });
                        }
                        if ($actorPositionId) {
                            $sq->orWhere(function ($pq) use ($actorPositionId) {
                                $pq->where('approver_type', 'position')
                                    ->where('approver_ref', (string) $actorPositionId);
                            });
                        }
                    });
            })
            ->latest()
            ->get();

        return view('approvals.my-approvals', compact('instances'));
    }

    public function act(Request $request, ApprovalInstance $instance, ApprovalFlowService $approvalFlowService): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:1000',
        ]);

        try {
            $approvalFlowService->act(
                $instance->id,
                (int) (session('user.id') ?? 1),
                $validated['action'],
                $validated['comment'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['approval' => $e->getMessage()]);
        }

        $fresh = $instance->fresh();

        return match ($fresh->document_type) {
            'repair_request' => redirect()->route('repair-requests.show', $fresh)->with('success', __('common.saved')),
            'pm_am_plan' => redirect()->route('maintenance.show', $fresh)->with('success', __('common.saved')),
            'spare_parts_requisition' => redirect()->route('spare-parts.requisition.show', $fresh)->with('success', __('common.saved')),
            default => redirect()->route('approvals.my')->with('success', __('common.saved')),
        };
    }
}
