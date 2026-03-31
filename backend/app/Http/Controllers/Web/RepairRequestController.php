<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApprovalInstance;
use App\Models\ApprovalInstanceStep;
use App\Models\Department;
use App\Models\DocumentForm;
use App\Models\User;
use App\Services\ApprovalFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class RepairRequestController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) (session('user.id') ?? 0);
        $status = $request->query('status');
        if ($status !== null && $status !== '' && ! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = null;
        }

        $myInstances = ApprovalInstance::query()
            ->where('document_type', 'repair_request')
            ->where('requester_user_id', $userId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['department'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();
        $form = DocumentForm::query()
            ->with('fields')
            ->where('document_type', 'repair_request')
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        $showAdminHints = (bool) session('user.is_super_admin', false);

        $userModel = $userId > 0 ? User::with(['company', 'branch'])->find($userId) : null;
        $company = $userModel?->company;
        $branch = null;
        if ($userModel && $userModel->branch && $userModel->branch->is_active
            && (int) $userModel->branch->company_id === (int) $userModel->company_id) {
            $branch = $userModel->branch;
        }

        return view('repair-requests.index', compact('myInstances', 'departments', 'form', 'status', 'showAdminHints', 'company', 'branch'));
    }

    public function show(ApprovalInstance $instance): View
    {
        abort_unless($instance->document_type === 'repair_request', 404);
        $this->authorizeViewInstance($instance);

        $instance->load(['steps.actor', 'workflow', 'requester.company', 'requester.branch', 'department']);

        $formForLabels = DocumentForm::query()
            ->with('fields')
            ->where('document_type', 'repair_request')
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
        $fieldLabels = $formForLabels
            ? $formForLabels->fields->mapWithKeys(fn ($f) => [$f->field_key => $f->label])
            : collect();

        $canAct = false;
        if ($instance->status === 'pending' && in_array('approval.approve', session('user_permissions', []), true)) {
            $userId = (int) (session('user.id') ?? 0);
            $currentStep = $instance->steps->firstWhere('step_no', $instance->current_step_no);
            if ($currentStep && $currentStep->action === 'pending') {
                $canAct = $this->userCanActStep($currentStep, $userId);
            }
        }

        $requester = $instance->requester;
        $company = $requester?->company;
        $branch = null;
        if ($requester && $requester->branch && $requester->branch->is_active
            && (int) $requester->branch->company_id === (int) $requester->company_id) {
            $branch = $requester->branch;
        }

        return view('repair-requests.show', compact('instance', 'canAct', 'fieldLabels', 'company', 'branch'));
    }

    public function myJobs(): View
    {
        return view('repair-requests.my-jobs');
    }

    public function assign(): View
    {
        return view('repair-requests.assign');
    }

    public function evaluate(): View
    {
        return view('repair-requests.evaluate');
    }

    public function submit(Request $request, ApprovalFlowService $approvalFlowService): RedirectResponse
    {
        $validated = $request->validate([
            'reference_no' => 'nullable|string|max:100',
            'department_id' => 'nullable|integer|exists:departments,id',
            'form_key' => 'nullable|string|max:100',
            'form_payload' => 'nullable|array',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $payload = $validated['form_payload'] ?? [];
        if (isset($payload['title']) && trim((string) $payload['title']) === '') {
            return back()
                ->withErrors(['form_payload.title' => __('common.validation_title_required')])
                ->withInput();
        }

        try {
            $instance = $approvalFlowService->start(
                'repair_request',
                $validated['department_id'] ?? null,
                (int) (session('user.id') ?? 1),
                $validated['reference_no'] ?? null,
                $payload,
                $validated['form_key'] ?? null,
                isset($validated['amount']) ? (float) $validated['amount'] : null
            );
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['workflow' => $this->workflowErrorMessage($e)])
                ->withInput();
        }

        return redirect()
            ->route('repair-requests.show', $instance)
            ->with('success', __('common.saved'));
    }

    private function authorizeViewInstance(ApprovalInstance $instance): void
    {
        if (session('user.is_super_admin', false)) {
            return;
        }
        $uid = (int) (session('user.id') ?? 0);
        if ($instance->requester_user_id === $uid) {
            return;
        }
        if (in_array('approval.approve', session('user_permissions', []), true)) {
            return;
        }
        abort(403);
    }

    private function userCanActStep(ApprovalInstanceStep $step, int $userId): bool
    {
        $user = User::find($userId);
        if (! $user) {
            return false;
        }
        if ($step->approver_type === 'user') {
            return (string) $step->approver_ref === (string) $userId;
        }

        if ($step->approver_type === 'position') {
            return $user->position_id
                && (string) $step->approver_ref === (string) $user->position_id;
        }

        return $user->hasRole($step->approver_ref);
    }

    private function workflowErrorMessage(RuntimeException $e): string
    {
        $msg = $e->getMessage();

        return match (true) {
            str_contains($msg, 'Amount is required for amount-based') => __('common.workflow_error_amount_required'),
            str_contains($msg, 'No matching amount range') => __('common.workflow_error_no_amount_range'),
            str_contains($msg, 'Department is required for workflow binding') => __('common.workflow_error_department_required'),
            str_contains($msg, 'No workflow binding found') => __('common.workflow_error_no_binding'),
            str_contains($msg, 'Workflow is not configured') => __('common.workflow_error_not_configured'),
            default => __('common.workflow_error_generic'),
        };
    }
}
