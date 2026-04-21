<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DocumentForm;
use App\Models\DocumentFormField;
use App\Models\DocumentFormSubmission;
use App\Models\SubmissionActivityLog;
use App\Models\User;
use App\Services\ApprovalFlowService;
use App\Services\FormSchemaService;
use App\Support\DateExpressionResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class DocumentFormSubmissionController extends Controller
{
    public function __construct(private readonly FormSchemaService $schemaService) {}

    public function index(): View
    {
        $userId = (int) (session('user.id') ?? 0);
        $userDeptId = session('user.department_id') ?? User::find($userId)?->department_id;

        $forms = DocumentForm::query()
            ->where('is_active', true)
            ->visibleToUser($userDeptId)
            ->orderBy('name')
            ->get()
            ->groupBy('document_type');

        return view('forms.index', compact('forms'));
    }

    public function mySubmissions(): View
    {
        $userId = (int) (session('user.id') ?? 0);

        $submissions = DocumentFormSubmission::query()
            ->where('user_id', $userId)
            ->with(['form', 'instance'])
            ->latest()
            ->get()
            ->groupBy(fn ($s) => $s->form?->name ?? '—');

        return view('forms.my-submissions', compact('submissions'));
    }

    public function listByForm(DocumentForm $documentForm, Request $request): View
    {
        $userId = (int) (session('user.id') ?? 0);
        $userDeptId = session('user.department_id') ?? User::find($userId)?->department_id;
        $isSuperAdmin = (bool) session('user.is_super_admin', false);

        abort_if(! $documentForm->is_active, 404);
        // Super-admins bypass the department-scope check so they can monitor /
        // support submissions on every form regardless of their own department.
        abort_unless(
            $isSuperAdmin
                || DocumentForm::query()->whereKey($documentForm->id)->visibleToUser($userDeptId)->exists(),
            404
        );

        $searchable = $documentForm->fields()->where('is_searchable', true)->orderBy('sort_order')->get();
        $filters = $this->extractFilters($request, $searchable);

        $showCancelled = (bool) $request->query('show_cancelled');
        $query = DocumentFormSubmission::query()
            ->when($showCancelled, fn ($q) => $q->withTrashed())
            ->where('document_form_submissions.form_id', $documentForm->id)
            // Super-admins see every submission for the form (monitoring/support).
            // Everyone else is scoped to their own submissions.
            ->when(! $isSuperAdmin, fn ($q) => $q->where('document_form_submissions.user_id', $userId));

        $referenceNoFilter = trim((string) $request->query('reference_no', ''));
        if ($referenceNoFilter !== '') {
            $query->whereRaw('LOWER(document_form_submissions.reference_no) LIKE ?', [
                '%'.mb_strtolower($referenceNoFilter).'%',
            ]);
            $filters['reference_no'] = $referenceNoFilter;
        }

        $this->applyFieldFilters($query, $documentForm, $searchable, $filters);

        $submissions = $query->select('document_form_submissions.*')
            ->with(['instance', 'latestActivity.user'])
            ->latest('document_form_submissions.id')
            ->paginate(20)
            ->withQueryString();

        return view('forms.list-by-form', [
            'form' => $documentForm,
            'submissions' => $submissions,
            'searchable' => $searchable,
            'filters' => $filters,
            'showCancelled' => $showCancelled,
        ]);
    }

    /**
     * Pull filter values from the request keyed by field_key. Date/datetime fields
     * read `{key}_from` and `{key}_to`; everything else reads the key directly.
     */
    private function extractFilters(Request $request, Collection $searchable): array
    {
        $filters = [];
        foreach ($searchable as $field) {
            $key = $field->field_key;
            if (in_array($field->field_type, ['date', 'datetime'], true)) {
                $from = $request->query($key.'_from');
                $to = $request->query($key.'_to');
                if (filled($from)) {
                    $filters[$key.'_from'] = (string) $from;
                }
                if (filled($to)) {
                    $filters[$key.'_to'] = (string) $to;
                }
            } else {
                $val = $request->query($key);
                if (filled($val)) {
                    $filters[$key] = is_array($val) ? $val : (string) $val;
                }
            }
        }

        return $filters;
    }

    private function applyFieldFilters(Builder $query, DocumentForm $form, Collection $searchable, array $filters): void
    {
        if (empty($filters)) {
            return;
        }

        $hasFdata = $form->hasDedicatedTable();
        if ($hasFdata) {
            $query->leftJoin($form->submission_table.' as ft', 'ft.id', '=', 'document_form_submissions.fdata_row_id');
        }

        foreach ($searchable as $field) {
            $key = $field->field_key;
            $type = $field->field_type;

            if (in_array($type, ['date', 'datetime'], true)) {
                $from = $filters[$key.'_from'] ?? null;
                $to = $filters[$key.'_to'] ?? null;
                if ($from === null && $to === null) {
                    continue;
                }
                if ($hasFdata) {
                    $col = 'ft.'.$key;
                    if ($from !== null) {
                        $query->where($col, '>=', $from);
                    }
                    if ($to !== null) {
                        $query->where($col, '<=', $to);
                    }
                } else {
                    $expr = "json_extract(document_form_submissions.payload, '$.".$key."')";
                    if ($from !== null) {
                        $query->whereRaw("$expr >= ?", [$from]);
                    }
                    if ($to !== null) {
                        $query->whereRaw("$expr <= ?", [$to]);
                    }
                }

                continue;
            }

            if (! array_key_exists($key, $filters)) {
                continue;
            }
            $val = $filters[$key];

            if ($hasFdata) {
                $col = 'ft.'.$key;
                match ($type) {
                    'select', 'radio', 'lookup', 'number', 'email', 'phone' => $query->where($col, (string) $val),
                    default => $query->whereRaw('LOWER('.$col.') LIKE ?', ['%'.mb_strtolower((string) $val).'%']),
                };
            } else {
                $expr = "json_extract(document_form_submissions.payload, '$.".$key."')";
                match ($type) {
                    'select', 'radio', 'lookup', 'number', 'email', 'phone' => $query->whereRaw("$expr = ?", [(string) $val]),
                    default => $query->whereRaw("LOWER($expr) LIKE ?", ['%'.mb_strtolower((string) $val).'%']),
                };
            }
        }
    }

    public function create(DocumentForm $documentForm): View
    {
        abort_if(! $documentForm->is_active, 404);
        $documentForm->load('fields');

        return view('forms.create', ['form' => $documentForm]);
    }

    public function storeDraft(Request $request, DocumentForm $documentForm): RedirectResponse
    {
        abort_if(! $documentForm->is_active, 404);
        $documentForm->load('fields');

        $spec = $this->buildPayloadRules($documentForm);
        $validated = $request->validate($spec['rules'], [], $spec['attributes']);
        $payload = $validated['fields'] ?? [];

        $userId = (int) (session('user.id') ?? 0);
        $userDeptId = session('user.department_id') ?? User::find($userId)?->department_id;

        $submission = DocumentFormSubmission::create([
            'form_id' => $documentForm->id,
            'user_id' => $userId,
            'department_id' => $userDeptId,
            'payload' => $payload,
            'status' => 'draft',
        ]);

        // Dual-write: insert into fdata_* table
        $fdataRowId = $this->writeFdataRow($documentForm, $payload, [
            'user_id' => $userId,
            'department_id' => $userDeptId,
            'status' => 'draft',
        ]);

        if ($fdataRowId) {
            $submission->update(['fdata_row_id' => $fdataRowId]);
        }

        SubmissionActivityLog::record($submission->id, $userId, 'created');

        return redirect()->route('forms.draft.edit', $submission)->with('success', __('common.saved'));
    }

    public function editDraft(DocumentFormSubmission $submission): View
    {
        $this->authorizeOwnerDraft($submission);
        $submission->load('form.fields');

        return view('forms.edit-draft', compact('submission'));
    }

    public function updateDraft(Request $request, DocumentFormSubmission $submission): RedirectResponse
    {
        $this->authorizeOwnerDraft($submission);
        $submission->load('form.fields');

        $spec = $this->buildPayloadRules($submission->form);
        $validated = $request->validate($spec['rules'], [], $spec['attributes']);
        $payload = $validated['fields'] ?? [];

        $submission->update(['payload' => $payload]);

        // Dual-write: update fdata_* row
        if ($submission->fdata_row_id && $submission->form->hasDedicatedTable()) {
            $this->schemaService->updateRow($submission->form, $submission->fdata_row_id, $payload);
        }

        SubmissionActivityLog::record($submission->id, (int) session('user.id'), 'updated');

        return redirect()->route('forms.draft.edit', $submission)->with('success', __('common.saved'));
    }

    public function destroyDraft(DocumentFormSubmission $submission): RedirectResponse
    {
        $this->authorizeOwnerDraft($submission);

        // Dual-write: delete fdata_* row
        if ($submission->fdata_row_id && $submission->form->hasDedicatedTable()) {
            $this->schemaService->deleteRow($submission->form, $submission->fdata_row_id);
        }

        SubmissionActivityLog::record($submission->id, (int) session('user.id'), 'cancelled', ['reference_no' => $submission->reference_no]);
        $submission->delete();

        return redirect()->route('forms.my-submissions')->with('success', __('common.deleted'));
    }

    /**
     * Super-admin-only recovery of a cancelled submission. Rebuilds the
     * fdata_* row that was hard-deleted during cancellation so reports and
     * list queries see the row again.
     *
     * The route parameter is a plain string (not bound to the model) so the
     * default soft-delete global scope can't filter it out before we call
     * `withTrashed()`.
     */
    public function restore(string $submission): RedirectResponse
    {
        abort_unless(session('user.is_super_admin', false), 403);

        $trashed = DocumentFormSubmission::withTrashed()->findOrFail((int) $submission);
        if (! $trashed->trashed()) {
            return redirect()->route('forms.submission.show', $trashed);
        }

        $userId = (int) (session('user.id') ?? 0);

        $trashed->restore();
        $trashed->forceFill(['deleted_by' => null])->saveQuietly();

        $trashed->load('form');
        $form = $trashed->form;
        if ($form?->hasDedicatedTable()) {
            $newId = $this->schemaService->insertRow($form, $trashed->payload ?? [], [
                'user_id' => $trashed->user_id,
                'department_id' => $trashed->department_id,
                'status' => $trashed->status,
                'reference_no' => $trashed->reference_no,
                'approval_instance_id' => $trashed->approval_instance_id,
            ]);
            if ($newId) {
                $trashed->forceFill(['fdata_row_id' => $newId])->saveQuietly();
            }
        }

        SubmissionActivityLog::record($trashed->id, $userId, 'restored', [
            'reference_no' => $trashed->reference_no,
        ]);

        return redirect()->route('forms.submission.show', $trashed)
            ->with('success', __('common.restored'));
    }

    /**
     * Rejected → draft: lets the owner re-edit and resubmit the same submission
     * without losing reference_no or approval history.
     *
     * We keep `approval_instance_id` linked so the rejection trail stays visible
     * on the edit page; `submit()` overwrites the reference_no and instance id
     * when the owner resubmits, so no cleanup is needed here.
     */
    public function returnToDraft(DocumentFormSubmission $submission): RedirectResponse
    {
        $this->authorizeReturnToDraft($submission);
        $submission->load('form');

        $userId = (int) (session('user.id') ?? 0);
        $form = $submission->form;

        $submission->update(['status' => 'draft']);

        if ($submission->fdata_row_id && $form?->hasDedicatedTable()) {
            $this->schemaService->updateRow($form, $submission->fdata_row_id, $submission->payload ?? [], [
                'status' => 'draft',
            ]);
        }

        SubmissionActivityLog::record($submission->id, $userId, 'returned_to_draft', [
            'from_approval_instance_id' => $submission->approval_instance_id,
        ]);

        return redirect()->route('forms.draft.edit', $submission)
            ->with('success', __('common.returned_to_draft'));
    }

    public function submit(DocumentFormSubmission $submission, ApprovalFlowService $approvalFlowService): RedirectResponse
    {
        $this->authorizeOwnerDraft($submission);
        $submission->load('form');

        $form = $submission->form;
        $userId = (int) (session('user.id') ?? 0);

        try {
            $instance = $approvalFlowService->start(
                documentType: $form->document_type,
                departmentId: $submission->department_id,
                requesterUserId: $userId,
                referenceNo: null,
                payload: $submission->payload ?? [],
                formKey: $form->form_key,
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['submit' => $e->getMessage()]);
        }

        $submission->update([
            'status' => 'submitted',
            'approval_instance_id' => $instance->id,
            'reference_no' => $instance->reference_no,
        ]);

        // Dual-write: update fdata_* row with submission metadata
        if ($submission->fdata_row_id && $form->hasDedicatedTable()) {
            $this->schemaService->updateRow($form, $submission->fdata_row_id, $submission->payload ?? [], [
                'status' => 'submitted',
                'reference_no' => $instance->reference_no,
                'approval_instance_id' => $instance->id,
            ]);
        }

        SubmissionActivityLog::record($submission->id, $userId, 'submitted', ['reference_no' => $instance->reference_no]);

        return redirect()->route('forms.submission.show', $submission)->with('success', __('common.saved'));
    }

    public function showSubmission(DocumentFormSubmission $submission): View
    {
        $this->authorizeView($submission);

        $submission->load(['form.fields', 'instance.steps', 'instance.workflow', 'department']);
        $activity = SubmissionActivityLog::with('user')
            ->where('submission_id', $submission->id)
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('forms.show-submission', compact('submission', 'activity'));
    }

    /**
     * Dedicated audit view: full activity history (no 20-row cap) for a
     * single submission. Same authorization as show — anyone who can see
     * the submission can see its audit trail.
     */
    public function history(DocumentFormSubmission $submission): View
    {
        $this->authorizeView($submission);

        $submission->load(['form', 'instance.workflow']);
        $activities = SubmissionActivityLog::with('user')
            ->where('submission_id', $submission->id)
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('forms.submission-history', compact('submission', 'activities'));
    }

    public function print(DocumentFormSubmission $submission): View
    {
        $this->authorizeView($submission);
        abort_if($submission->status === 'draft', 404);

        $submission->load(['form.fields', 'instance.steps.approver', 'instance.workflow', 'department', 'user']);

        SubmissionActivityLog::record($submission->id, (int) session('user.id'), 'printed');

        return view('forms.print-submission', compact('submission'));
    }

    /**
     * Bulk delete — only processes submissions owned by the current user and still in
     * draft state. Silently skips anything else so URL-tampered IDs can't cause harm.
     */
    public function bulkDeleteDrafts(Request $request): RedirectResponse
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back();
        }
        $userId = (int) (session('user.id') ?? 0);

        $submissions = DocumentFormSubmission::whereIn('id', $ids)
            ->where('user_id', $userId)
            ->where('status', 'draft')
            ->with('form')
            ->get();

        foreach ($submissions as $submission) {
            if ($submission->fdata_row_id && $submission->form?->hasDedicatedTable()) {
                $this->schemaService->deleteRow($submission->form, $submission->fdata_row_id);
            }
            SubmissionActivityLog::record($submission->id, $userId, 'cancelled', ['reference_no' => $submission->reference_no, 'bulk' => true]);
            $submission->delete();
        }

        return back()->with('success', __('common.bulk_deleted', ['count' => $submissions->count()]));
    }

    public function duplicate(DocumentFormSubmission $submission): RedirectResponse
    {
        $userId = (int) (session('user.id') ?? 0);
        abort_unless((int) $submission->user_id === $userId, 403);

        $submission->load('form');
        $form = $submission->form;
        $userDeptId = session('user.department_id') ?? User::find($userId)?->department_id;

        $copy = DocumentFormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $userId,
            'department_id' => $userDeptId,
            'payload' => $submission->payload ?? [],
            'status' => 'draft',
            'reference_no' => null,
            'approval_instance_id' => null,
            'fdata_row_id' => null,
        ]);

        if ($form->hasDedicatedTable()) {
            $rowId = $this->schemaService->insertRow($form, $submission->payload ?? [], [
                'user_id' => $userId,
                'department_id' => $userDeptId,
                'status' => 'draft',
            ]);
            if ($rowId) {
                $copy->update(['fdata_row_id' => $rowId]);
            }
        }

        SubmissionActivityLog::record($copy->id, $userId, 'duplicated', [
            'source_submission_id' => $submission->id,
            'source_reference_no' => $submission->reference_no,
        ]);

        return redirect()
            ->route('forms.draft.edit', $copy)
            ->with('success', __('common.action_duplicate_success'));
    }

    // ── Private helpers ─────────────────────────────────────


    private function authorizeOwnerDraft(DocumentFormSubmission $submission): void
    {
        $userId = (int) (session('user.id') ?? 0);
        abort_unless((int) $submission->user_id === $userId, 403);
        abort_unless($submission->status === 'draft', 403);
    }

    private function authorizeReturnToDraft(DocumentFormSubmission $submission): void
    {
        $userId = (int) (session('user.id') ?? 0);
        abort_unless((int) $submission->user_id === $userId, 403);
        abort_unless($submission->effective_status === 'rejected', 403);
    }

    private function authorizeView(DocumentFormSubmission $submission): void
    {
        $userId = (int) (session('user.id') ?? 0);
        $isOwner = (int) $submission->user_id === $userId;
        $isSuperAdmin = (bool) session('user.is_super_admin', false);
        if ($isOwner || $isSuperAdmin) {
            return;
        }
        abort_unless($this->isApproverForSubmission($submission, $userId), 403);
    }

    /**
     * Approver scope: the user is listed as an approver on ANY step of this submission's
     * approval instance — past, current, or future. This is stricter than the legacy
     * blanket `approval.approve` permission (which exposed every pending submission to
     * every approver) while still letting auditors who handled the doc look back at it.
     */
    private function isApproverForSubmission(DocumentFormSubmission $submission, int $userId): bool
    {
        $instance = $submission->instance;
        if (! $instance) {
            return false;
        }

        $userIdStr = (string) $userId;
        $roleNames = collect(session('user.roles', []))
            ->map(fn ($r) => is_array($r) ? ($r['name'] ?? '') : $r)
            ->filter()
            ->values()
            ->all();
        $positionId = session('user.position_id') ?? User::find($userId)?->position_id;

        return $instance->steps()
            ->where(function ($q) use ($userIdStr, $roleNames, $positionId) {
                $q->where(function ($uq) use ($userIdStr) {
                    $uq->where('approver_type', 'user')->where('approver_ref', $userIdStr);
                });
                if (! empty($roleNames)) {
                    $q->orWhere(function ($rq) use ($roleNames) {
                        $rq->where('approver_type', 'role')->whereIn('approver_ref', $roleNames);
                    });
                }
                if ($positionId) {
                    $q->orWhere(function ($pq) use ($positionId) {
                        $pq->where('approver_type', 'position')->where('approver_ref', (string) $positionId);
                    });
                }
            })
            ->exists();
    }

    /**
     * Insert a row into fdata_* table (if the form has a dedicated table).
     * Returns the inserted row ID or null.
     */
    private function writeFdataRow(DocumentForm $form, array $payload, array $meta): ?int
    {
        if (! $form->hasDedicatedTable()) {
            return null;
        }

        $this->schemaService->ensureTableExists($form);

        return $this->schemaService->insertRow($form, $payload, $meta);
    }

    /**
     * @return array{rules: array, attributes: array}
     */
    private function buildPayloadRules(DocumentForm $form): array
    {
        $rules = [];
        $attributes = [];

        foreach ($form->fields as $field) {
            if (in_array($field->field_type, ['section', 'auto_number'])) {
                continue;
            }

            $key = "fields.{$field->field_key}";
            $attributes[$key] = $field->label;

            $fieldRules = $field->is_required ? ['required'] : ['nullable'];

            $fieldRules[] = match ($field->field_type) {
                'number', 'currency' => 'numeric',
                'date' => 'date',
                'email' => 'email',
                'checkbox', 'multi_select' => 'array',
                'image' => 'file|image|max:5120',
                default => 'string',
            };

            // Apply configurable validation_rules from field definition
            $vr = $field->validation_rules;
            if (is_array($vr) && count($vr)) {
                if (! empty($vr['min_length'])) {
                    $fieldRules[] = 'min:' . (int) $vr['min_length'];
                }
                if (! empty($vr['max_length'])) {
                    $fieldRules[] = 'max:' . (int) $vr['max_length'];
                }
                if (! empty($vr['regex'])) {
                    $fieldRules[] = 'regex:/' . str_replace('/', '\/', $vr['regex']) . '/';
                }
                if (isset($vr['min']) && $vr['min'] !== '' && in_array($field->field_type, ['number', 'currency'])) {
                    $fieldRules[] = 'min:' . $vr['min'];
                }
                if (isset($vr['max']) && $vr['max'] !== '' && in_array($field->field_type, ['number', 'currency'])) {
                    $fieldRules[] = 'max:' . $vr['max'];
                }
                if (! empty($vr['min_date']) && $field->field_type === 'date') {
                    $resolved = DateExpressionResolver::resolve($vr['min_date']);
                    if ($resolved) {
                        $fieldRules[] = 'after_or_equal:' . $resolved;
                    }
                }
                if (! empty($vr['max_date']) && $field->field_type === 'date') {
                    $resolved = DateExpressionResolver::resolve($vr['max_date']);
                    if ($resolved) {
                        $fieldRules[] = 'before_or_equal:' . $resolved;
                    }
                }
            }

            $rules[$key] = $fieldRules;
        }

        return ['rules' => $rules, 'attributes' => $attributes];
    }
}
