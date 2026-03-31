<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApprovalInstance;
use App\Models\ApprovalInstanceStep;
use App\Models\Department;
use App\Models\DocumentForm;
use App\Models\SparePart;
use App\Models\SparePartRequisitionItem;
use App\Models\SparePartTransaction;
use App\Models\User;
use App\Services\ApprovalFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SparePartsController extends Controller
{
    // ─── Inventory ──────────────────────────────────────────

    public function stock(Request $request): View
    {
        $search = $request->query('search');

        $parts = SparePart::query()
            ->with('category')
            ->where('is_active', true)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('spare-parts.stock', compact('parts', 'search'));
    }

    public function withdrawalHistory(Request $request): View
    {
        $transactions = SparePartTransaction::query()
            ->with(['sparePart', 'performedBy'])
            ->where('transaction_type', 'issue')
            ->latest()
            ->paginate(20);

        return view('spare-parts.withdrawal-history', compact('transactions'));
    }

    // ─── Requisition Flow ───────────────────────────────────

    public function requisitionIndex(Request $request): View
    {
        $userId = (int) (session('user.id') ?? 0);
        $status = $request->query('status');
        if ($status !== null && $status !== '' && ! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = null;
        }

        $myInstances = ApprovalInstance::query()
            ->where('document_type', 'spare_parts_requisition')
            ->where('requester_user_id', $userId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['department'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('spare-parts.requisition-index', compact('myInstances', 'status'));
    }

    public function requisitionCreate(Request $request): View
    {
        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();
        $form = DocumentForm::query()
            ->with('fields')
            ->where('document_type', 'spare_parts_requisition')
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
        $spareParts = SparePart::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'unit', 'unit_cost', 'current_stock']);

        $parentType = $request->query('parent_type');
        $parentId = $request->query('parent_id');

        $userId = (int) (session('user.id') ?? 0);
        $userModel = $userId > 0 ? User::with(['company', 'branch'])->find($userId) : null;
        $company = $userModel?->company;
        $branch = null;
        if ($userModel && $userModel->branch && $userModel->branch->is_active
            && (int) $userModel->branch->company_id === (int) $userModel->company_id) {
            $branch = $userModel->branch;
        }

        return view('spare-parts.requisition-create', compact('departments', 'form', 'spareParts', 'parentType', 'parentId', 'company', 'branch'));
    }

    public function requisitionSubmit(Request $request, ApprovalFlowService $approvalFlowService): RedirectResponse
    {
        $validated = $request->validate([
            'reference_no' => 'nullable|string|max:100',
            'department_id' => 'nullable|integer|exists:departments,id',
            'form_key' => 'nullable|string|max:100',
            'form_payload' => 'nullable|array',
            'amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.spare_part_id' => 'required|integer|exists:spare_parts,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.note' => 'nullable|string|max:500',
        ]);

        $payload = $validated['form_payload'] ?? [];

        // Add parent reference if provided
        if (! empty($payload['parent_reference'])) {
            // kept as-is in payload
        }

        // Calculate total amount from line items if not provided
        $totalAmount = $validated['amount'] ?? null;
        if ($totalAmount === null) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $part = SparePart::find($item['spare_part_id']);
                if ($part) {
                    $totalAmount += $part->unit_cost * $item['quantity'];
                }
            }
        }

        try {
            $instance = $approvalFlowService->start(
                'spare_parts_requisition',
                $validated['department_id'] ?? null,
                (int) (session('user.id') ?? 1),
                $validated['reference_no'] ?? null,
                $payload,
                $validated['form_key'] ?? null,
                $totalAmount > 0 ? (float) $totalAmount : null
            );
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['workflow' => $this->workflowErrorMessage($e)])
                ->withInput();
        }

        // Create line items
        foreach ($validated['items'] as $item) {
            $part = SparePart::find($item['spare_part_id']);
            SparePartRequisitionItem::create([
                'approval_instance_id' => $instance->id,
                'spare_part_id' => $item['spare_part_id'],
                'quantity_requested' => $item['quantity'],
                'unit_cost' => $part?->unit_cost ?? 0,
                'note' => $item['note'] ?? null,
            ]);
        }

        return redirect()
            ->route('spare-parts.requisition.show', $instance)
            ->with('success', __('common.saved'));
    }

    public function requisitionShow(ApprovalInstance $instance): View
    {
        abort_unless($instance->document_type === 'spare_parts_requisition', 404);
        $this->authorizeViewInstance($instance);

        $instance->load(['steps.actor', 'workflow', 'requester.company', 'requester.branch', 'department']);

        $lineItems = SparePartRequisitionItem::query()
            ->where('approval_instance_id', $instance->id)
            ->with('sparePart')
            ->get();

        $formForLabels = DocumentForm::query()
            ->with('fields')
            ->where('document_type', 'spare_parts_requisition')
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

        $canIssue = $instance->status === 'approved'
            && $lineItems->contains(fn ($i) => $i->quantity_issued < $i->quantity_requested)
            && (session('user.is_super_admin', false) || in_array('spare_parts.manage', session('user_permissions', []), true));

        $requester = $instance->requester;
        $company = $requester?->company;
        $branch = null;
        if ($requester && $requester->branch && $requester->branch->is_active
            && (int) $requester->branch->company_id === (int) $requester->company_id) {
            $branch = $requester->branch;
        }

        return view('spare-parts.requisition-show', compact('instance', 'lineItems', 'canAct', 'canIssue', 'fieldLabels', 'company', 'branch'));
    }

    public function issueItems(Request $request, ApprovalInstance $instance): RedirectResponse
    {
        abort_unless($instance->document_type === 'spare_parts_requisition', 404);
        abort_unless($instance->status === 'approved', 403);

        $validated = $request->validate([
            'issue' => 'required|array',
            'issue.*.item_id' => 'required|integer|exists:spare_part_requisition_items,id',
            'issue.*.quantity' => 'required|numeric|min:0',
        ]);

        $userId = (int) (session('user.id') ?? 1);

        foreach ($validated['issue'] as $row) {
            $item = SparePartRequisitionItem::find($row['item_id']);
            if (! $item || $item->approval_instance_id !== $instance->id) {
                continue;
            }

            $qtyToIssue = min((float) $row['quantity'], $item->quantity_requested - $item->quantity_issued);
            if ($qtyToIssue <= 0) {
                continue;
            }

            $item->increment('quantity_issued', $qtyToIssue);

            // Decrement stock
            $item->sparePart()->decrement('current_stock', $qtyToIssue);

            // Record transaction
            SparePartTransaction::create([
                'spare_part_id' => $item->spare_part_id,
                'transaction_type' => 'issue',
                'quantity' => $qtyToIssue,
                'unit_cost' => $item->unit_cost,
                'reference_type' => 'approval_instance',
                'reference_id' => $instance->id,
                'note' => "Requisition #{$instance->id}",
                'performed_by_user_id' => $userId,
            ]);
        }

        return redirect()
            ->route('spare-parts.requisition.show', $instance)
            ->with('success', __('common.saved'));
    }

    // ─── Shared helpers ─────────────────────────────────────

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
