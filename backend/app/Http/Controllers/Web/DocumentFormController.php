<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApprovalInstance;
use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\DocumentForm;
use App\Models\DocumentFormSubmission;
use App\Models\RunningNumberConfig;
use App\Services\FormSchemaService;
use App\Support\LookupRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentFormController extends Controller
{
    public function __construct(private readonly FormSchemaService $schemaService) {}

    private const TABLE_COLUMN_TYPES = ['text', 'number', 'select', 'checkbox', 'date', 'lookup'];

    private const MAX_TABLE_COLUMNS = 40;

    /**
     * Field types exposed in the form builder UI (no legacy *_lookup types).
     *
     * @return list<string>
     */
    private static function allowedFieldTypes(): array
    {
        return [
            'text', 'textarea', 'number', 'date', 'select', 'checkbox', 'radio', 'file',
            'time', 'datetime', 'email', 'phone', 'signature', 'currency', 'lookup', 'table', 'section',
            'auto_number', 'image', 'multi_select',
        ];
    }

    public function index(): View
    {
        $forms = DocumentForm::query()
            ->withCount('fields')
            ->with(['workflowPolicies.workflow', 'workflowPolicies.ranges'])
            ->orderBy('name')
            ->get();

        return view('settings.document-forms.index', compact('forms'));
    }

    public function create(): View
    {
        $lookupSources = LookupRegistry::sources();
        $workflowStepsByDocType = $this->workflowStepsByDocType();
        $departments = Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('settings.document-forms.create', compact('lookupSources', 'workflowStepsByDocType', 'departments'));
    }

    public function edit(DocumentForm $documentForm): View
    {
        $documentForm->load('fields');
        $lookupSources = LookupRegistry::sources();
        $workflowStepsByDocType = $this->workflowStepsByDocType();
        $departments = Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('settings.document-forms.edit', compact('documentForm', 'lookupSources', 'workflowStepsByDocType', 'departments'));
    }

    /**
     * Map each document_type to the step labels its workflows expose. Admins use
     * this list to decide which roles can edit each field (`editable_by`).
     *
     * Returns: ['maintenance_request' => [['step_no' => 1, 'name' => 'Supervisor'], ...], ...]
     * When multiple workflows exist for one document_type, step names are taken
     * from the first workflow that defines that step_no (admin sees one label per step).
     *
     * @return array<string, list<array{step_no:int,name:string}>>
     */
    private function workflowStepsByDocType(): array
    {
        $rows = DB::table('approval_workflow_stages')
            ->join('approval_workflows', 'approval_workflow_stages.workflow_id', '=', 'approval_workflows.id')
            ->where('approval_workflow_stages.is_active', true)
            ->select(
                'approval_workflows.document_type',
                'approval_workflow_stages.step_no',
                'approval_workflow_stages.name'
            )
            ->orderBy('approval_workflow_stages.step_no')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $bucket = &$result[$row->document_type];
            $bucket ??= [];
            if (! array_key_exists((int) $row->step_no, $bucket)) {
                $bucket[(int) $row->step_no] = [
                    'step_no' => (int) $row->step_no,
                    'name' => (string) $row->name,
                ];
            }
        }
        foreach ($result as $docType => $byStep) {
            ksort($byStep);
            $result[$docType] = array_values($byStep);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedDocumentFormPayload(Request $request, ?DocumentForm $existing = null): array
    {
        $sourceKeys = LookupRegistry::sourceKeys();

        $formKeyRule = Rule::unique('document_forms', 'form_key');
        if ($existing !== null) {
            $formKeyRule = $formKeyRule->ignore($existing->id);
        }

        $validator = Validator::make($request->all(), [
            'form_key' => ['required', 'string', 'max:100', 'alpha_dash', $formKeyRule],
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:50', Rule::exists('document_types', 'code')->where('is_active', true)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'layout_columns' => ['nullable', 'integer', Rule::in([1, 2, 3, 4])],
            'table_name' => [
                'required', 'string', 'max:64',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('document_forms', 'submission_table')->ignore($existing?->id),
            ],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.field_key' => ['required', 'string', 'max:100', 'alpha_dash'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.field_type' => ['required', Rule::in(self::allowedFieldTypes())],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.is_searchable' => ['nullable', 'boolean'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.options_raw' => ['nullable', 'string'],
            'fields.*.lookup_source' => ['nullable', 'string', 'max:100'],
            'fields.*.depends_on' => ['nullable', 'string', 'max:100', 'alpha_dash'],
            'fields.*.foreign_key' => ['nullable', 'string', 'max:100'],
            'fields.*.table_columns' => ['nullable', 'string', 'max:65535'],
            'fields.*.col_span' => ['nullable', 'integer', 'min:0', 'max:4'],
            'fields.*.visibility_rules' => ['nullable', 'string', 'max:65535'],
            'fields.*.validation_rules' => ['nullable', 'string', 'max:65535'],
            'fields.*.editable_by' => ['nullable', 'string', 'max:2000'],
            'fields.*.visible_to_departments' => ['nullable', 'string', 'max:2000'],
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $v) use ($request, $sourceKeys): void {
            $fields = $request->input('fields');
            if (! is_array($fields)) {
                return;
            }

            $seenKeys = [];
            foreach ($fields as $i => $field) {
                if (! is_array($field)) {
                    continue;
                }
                $key = isset($field['field_key']) ? (string) $field['field_key'] : '';
                if ($key === '') {
                    continue;
                }
                if (isset($seenKeys[$key])) {
                    $v->errors()->add("fields.{$i}.field_key", __('validation.document_form.duplicate_field_key'));
                    $v->errors()->add("fields.{$seenKeys[$key]}.field_key", __('validation.document_form.duplicate_field_key'));

                    continue;
                }
                if (in_array($key, FormSchemaService::RESERVED_COLUMNS, true)) {
                    $v->errors()->add("fields.{$i}.field_key", __('validation.document_form.field_key_reserved'));

                    continue;
                }
                $seenKeys[$key] = $i;
            }

            $tableName = (string) ($request->input('table_name') ?? '');
            if ($tableName !== '' && Schema::hasTable($tableName)) {
                $ownedByOther = DocumentForm::where('submission_table', $tableName)
                    ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
                    ->exists();
                $ownedByThis = $existing && $existing->submission_table === $tableName;
                if (! $ownedByOther && ! $ownedByThis) {
                    $v->errors()->add('table_name', __('validation.document_form.table_name_conflicts_system'));
                }
            }

            foreach ($fields as $i => $field) {
                if (! is_array($field)) {
                    continue;
                }
                $type = $field['field_type'] ?? '';

                if ($type === 'lookup') {
                    if (empty($field['lookup_source']) || ! in_array($field['lookup_source'], $sourceKeys, true)) {
                        $v->errors()->add("fields.{$i}.lookup_source", __('validation.document_form.lookup_source_required'));
                    }
                    if (! empty($field['depends_on'])) {
                        $parentKey = (string) $field['depends_on'];
                        $parent = null;
                        foreach ($fields as $pi => $f) {
                            if ($pi === $i || ! is_array($f)) {
                                continue;
                            }
                            if (($f['field_key'] ?? null) === $parentKey) {
                                $parent = $f;
                                break;
                            }
                        }
                        if (! is_array($parent) || ($parent['field_type'] ?? '') !== 'lookup') {
                            $v->errors()->add("fields.{$i}.depends_on", __('validation.document_form.depends_on_invalid'));
                        }
                        if (empty($field['foreign_key'])) {
                            $v->errors()->add("fields.{$i}.foreign_key", __('validation.document_form.foreign_key_required'));
                        } elseif (! preg_match('/^[a-z_]+$/', (string) $field['foreign_key'])) {
                            $v->errors()->add("fields.{$i}.foreign_key", __('validation.document_form.foreign_key_invalid'));
                        }
                    }
                }

                if (in_array($type, ['select', 'radio', 'checkbox', 'multi_select'], true)) {
                    $raw = $field['options_raw'] ?? '';
                    $lines = array_values(array_filter(array_map('trim', explode("\n", (string) $raw))));
                    if (count($lines) < 1) {
                        $v->errors()->add("fields.{$i}.options_raw", __('validation.document_form.options_required'));
                    }
                }

                if ($type === 'table') {
                    $raw = $field['table_columns'] ?? '';
                    if ($raw === '' || $raw === null) {
                        $v->errors()->add("fields.{$i}.table_columns", __('validation.document_form.table_columns_required'));

                        continue;
                    }
                    $decoded = json_decode((string) $raw, true);
                    if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                        $v->errors()->add("fields.{$i}.table_columns", __('validation.document_form.table_columns_invalid_json'));

                        continue;
                    }
                    if (count($decoded) < 1) {
                        $v->errors()->add("fields.{$i}.table_columns", __('validation.document_form.table_columns_required'));

                        continue;
                    }
                    if (count($decoded) > self::MAX_TABLE_COLUMNS) {
                        $v->errors()->add("fields.{$i}.table_columns", __('validation.document_form.table_columns_too_many'));

                        continue;
                    }
                    $colKeys = [];
                    foreach ($decoded as $col) {
                        if (! is_array($col)) {
                            $v->errors()->add("fields.{$i}.table_columns", __('validation.document_form.table_column_invalid'));

                            continue 2;
                        }
                        $ck = isset($col['key']) ? (string) $col['key'] : '';
                        if ($ck === '' || ! preg_match('/^[a-zA-Z0-9_-]+$/', $ck)) {
                            $v->errors()->add("fields.{$i}.table_columns", __('validation.document_form.table_column_key_invalid'));

                            continue 2;
                        }
                        $colKeys[] = $ck;
                        $ct = isset($col['type']) ? (string) $col['type'] : 'text';
                        if (! in_array($ct, self::TABLE_COLUMN_TYPES, true)) {
                            $v->errors()->add("fields.{$i}.table_columns", __('validation.document_form.table_column_type_invalid'));

                            continue 2;
                        }
                    }
                    if (count($colKeys) !== count(array_unique($colKeys))) {
                        $v->errors()->add("fields.{$i}.table_columns", __('validation.document_form.table_column_keys_duplicate'));
                    }
                }
            }
        });

        return $validator->validate();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedDocumentFormPayload($request);

        DB::transaction(function () use ($validated) {
            $form = DocumentForm::create([
                'form_key' => $validated['form_key'],
                'name' => $validated['name'],
                'document_type' => $validated['document_type'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'layout_columns' => (int) ($validated['layout_columns'] ?? 1),
                'submission_table' => $validated['table_name'],
            ]);

            foreach ($validated['fields'] as $index => $field) {
                $form->fields()->create([
                    'field_key' => $field['field_key'],
                    'label' => $field['label'],
                    'field_type' => $field['field_type'],
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'is_searchable' => $this->resolveIsSearchable($field),
                    'sort_order' => $index + 1,
                    'col_span' => (int) ($field['col_span'] ?? 0),
                    'placeholder' => $field['placeholder'] ?? null,
                    'options' => $this->parseOptions($field),
                    'visibility_rules' => $this->parseJsonField($field['visibility_rules'] ?? null),
                    'validation_rules' => $this->parseJsonField($field['validation_rules'] ?? null),
                    'editable_by' => $this->parseEditableBy($field, $validated['document_type']),
                    'visible_to_departments' => $this->parseDepartmentIds($field),
                ]);
            }

            $this->schemaService->createTable($form->load('fields'));
        });

        $message = __('common.saved');
        if ($warning = $this->autoNumberWarning($validated['fields'], $validated['document_type'])) {
            $message .= ' — ' . $warning;
        }

        return redirect()->route('settings.document-forms.index')->with('success', $message);
    }

    public function update(Request $request, DocumentForm $documentForm): RedirectResponse
    {
        $validated = $this->validatedDocumentFormPayload($request, $documentForm);

        DB::transaction(function () use ($validated, $documentForm) {
            $documentForm->update([
                'form_key' => $validated['form_key'],
                'name' => $validated['name'],
                'document_type' => $validated['document_type'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'layout_columns' => (int) ($validated['layout_columns'] ?? 1),
            ]);

            $documentForm->fields()->delete();
            foreach ($validated['fields'] as $index => $field) {
                $documentForm->fields()->create([
                    'field_key' => $field['field_key'],
                    'label' => $field['label'],
                    'field_type' => $field['field_type'],
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'is_searchable' => $this->resolveIsSearchable($field),
                    'sort_order' => $index + 1,
                    'col_span' => (int) ($field['col_span'] ?? 0),
                    'placeholder' => $field['placeholder'] ?? null,
                    'options' => $this->parseOptions($field),
                    'visibility_rules' => $this->parseJsonField($field['visibility_rules'] ?? null),
                    'validation_rules' => $this->parseJsonField($field['validation_rules'] ?? null),
                    'editable_by' => $this->parseEditableBy($field, $validated['document_type']),
                    'visible_to_departments' => $this->parseDepartmentIds($field),
                ]);
            }

            // First-time table creation for forms that had no dedicated table yet
            if (! $documentForm->hasDedicatedTable() && ! empty($validated['table_name'])) {
                $documentForm->update(['submission_table' => $validated['table_name']]);
                $this->schemaService->createTable($documentForm->load('fields'));
            } else {
                $this->schemaService->syncTable($documentForm, $documentForm->fields()->get());
            }
        });

        $message = __('common.updated');
        if ($warning = $this->autoNumberWarning($validated['fields'], $validated['document_type'])) {
            $message .= ' — ' . $warning;
        }

        return redirect()->route('settings.document-forms.edit', $documentForm)->with('success', $message);
    }

    private function resolveIsSearchable(array $field): bool
    {
        if (! in_array($field['field_type'] ?? '', \App\Models\DocumentFormField::SEARCHABLE_TYPES, true)) {
            return false;
        }

        return (bool) ($field['is_searchable'] ?? false);
    }

    private function autoNumberWarning(array $fields, string $documentType): ?string
    {
        $hasAutoNumber = collect($fields)
            ->contains(fn ($f) => ($f['field_type'] ?? null) === 'auto_number');

        if (! $hasAutoNumber) {
            return null;
        }

        $hasConfig = RunningNumberConfig::where('document_type', $documentType)
            ->where('is_active', true)
            ->exists();

        return $hasConfig
            ? null
            : __('common.document_form_auto_number_no_config', ['type' => $documentType]);
    }

    /**
     * One-click "create report from this form" — builds a dashboard with 3 default
     * widgets (total count, breakdown by status, recent submissions table) pointing
     * at the `form:{form_key}` data source. Admin can then edit/extend in /settings/dashboards.
     */
    public function createReport(DocumentForm $documentForm): RedirectResponse
    {
        $sourceKey = 'form:'.$documentForm->form_key;
        $userId = (int) (session('user.id') ?? 1);

        $dashboard = \App\Models\ReportDashboard::create([
            'name' => __('common.form_report_dashboard_name', ['form' => $documentForm->name]),
            'description' => __('common.form_report_dashboard_desc', ['form' => $documentForm->name]),
            'layout_columns' => 2,
            'visibility' => 'all',
            'is_active' => true,
            'created_by' => $userId,
        ]);

        $widgets = [
            [
                'title' => __('common.form_report_widget_total'),
                'widget_type' => 'metric',
                'data_source' => $sourceKey,
                'config' => ['aggregation' => 'count', 'field' => 'id'],
                'col_span' => 1,
                'sort_order' => 1,
            ],
            [
                'title' => __('common.form_report_widget_by_status'),
                'widget_type' => 'chart',
                'data_source' => $sourceKey,
                'config' => ['chart_type' => 'donut', 'group_by' => 'status', 'aggregation' => 'count'],
                'col_span' => 1,
                'sort_order' => 2,
            ],
            [
                'title' => __('common.form_report_widget_recent'),
                'widget_type' => 'table',
                'data_source' => $sourceKey,
                'config' => [
                    'columns' => ['reference_no', 'status', 'department_id', 'created_at'],
                    'per_page' => 10,
                ],
                'col_span' => 2,
                'sort_order' => 3,
            ],
        ];
        foreach ($widgets as $w) {
            \App\Models\ReportDashboardWidget::create(array_merge($w, ['dashboard_id' => $dashboard->id]));
        }

        return redirect()
            ->route('reports.dashboards.show', $dashboard)
            ->with('success', __('common.form_report_created'));
    }

    public function clone(DocumentForm $documentForm): RedirectResponse
    {
        $baseKey = $documentForm->form_key . '_copy';
        $newKey = $baseKey;
        $counter = 1;
        while (DocumentForm::where('form_key', $newKey)->exists()) {
            $counter++;
            $newKey = $baseKey . '_' . $counter;
        }

        $clone = DB::transaction(function () use ($documentForm, $newKey) {
            $clone = DocumentForm::create([
                'form_key' => $newKey,
                'name' => $documentForm->name . ' (copy)',
                'document_type' => $documentForm->document_type,
                'description' => $documentForm->description,
                'is_active' => false,
                'layout_columns' => $documentForm->layout_columns,
                'submission_table' => null,
            ]);

            foreach ($documentForm->fields as $field) {
                $clone->fields()->create([
                    'field_key' => $field->field_key,
                    'label' => $field->label,
                    'field_type' => $field->field_type,
                    'is_required' => $field->is_required,
                    'is_searchable' => $field->is_searchable,
                    'sort_order' => $field->sort_order,
                    'col_span' => $field->col_span,
                    'placeholder' => $field->placeholder,
                    'options' => $field->options,
                    'visible_to_departments' => $field->visible_to_departments,
                    'editable_by' => $field->editable_by,
                    'visibility_rules' => $field->visibility_rules,
                    'validation_rules' => $field->validation_rules,
                ]);
            }

            return $clone;
        });

        return redirect()->route('settings.document-forms.edit', $clone)
            ->with('success', __('common.cloned'));
    }

    public function destroy(DocumentForm $documentForm): RedirectResponse
    {
        if (DocumentFormSubmission::where('form_id', $documentForm->id)->exists()) {
            return redirect()->route('settings.document-forms.index')
                ->with('error', __('common.cannot_delete_document_form'));
        }

        $this->schemaService->dropTable($documentForm);
        $documentForm->fields()->delete();
        $documentForm->workflowPolicies()->delete();
        $documentForm->delete();

        return redirect()->route('settings.document-forms.index')->with('success', __('common.deleted'));
    }

    private function parseOptions(array $field): ?array
    {
        $fieldType = $field['field_type'];

        // Lookup fields store source and optional dependency config
        if ($fieldType === 'lookup') {
            $opts = ['source' => $field['lookup_source'] ?? null];
            if (! empty($field['depends_on'])) {
                $opts['depends_on'] = $field['depends_on'];
                $opts['foreign_key'] = $field['foreign_key'] ?? null;
            }

            return $opts;
        }

        // Table fields store column definitions
        if ($fieldType === 'table') {
            $raw = $field['table_columns'] ?? null;
            if ($raw) {
                $columns = json_decode($raw, true);
                if (is_array($columns) && count($columns)) {
                    return ['columns' => $columns];
                }
            }

            return null;
        }

        // Select/radio/checkbox store text-based options
        if (in_array($fieldType, ['select', 'radio', 'checkbox'])) {
            $raw = $field['options_raw'] ?? null;
            $lines = array_values(array_filter(array_map('trim', explode("\n", (string) $raw))));

            return count($lines) ? $lines : null;
        }

        return null;
    }

    /**
     * Parse a JSON string field from the form builder into an array or null.
     */
    private function parseJsonField(?string $raw): ?array
    {
        if ($raw === null || $raw === '' || $raw === '[]' || $raw === '{}') {
            return null;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Filter out empty visibility rules (no field selected)
        if (isset($decoded[0]['field'])) {
            $decoded = array_values(array_filter($decoded, fn ($r) => ! empty($r['field'])));
        }

        // Filter out empty validation rules (all values falsy)
        if (! isset($decoded[0])) {
            $decoded = array_filter($decoded, fn ($v) => $v !== null && $v !== '' && $v !== 0);
        }

        return count($decoded) ? $decoded : null;
    }

    /**
     * Normalise the `editable_by` field from the form builder into a clean
     * list of allowed role strings. Roles are restricted to 'requester' plus
     * 'step_N' values that actually exist in any workflow for the form's
     * document_type (so admins can't save stale step_5 if the workflow was
     * shortened later).
     *
     * Returns null when the submitted value equals the implicit default
     * `['requester']`, so DB column stays null for unchanged/default fields.
     */
    private function parseEditableBy(array $field, string $documentType): ?array
    {
        $decoded = $this->decodeJsonList($field['editable_by'] ?? null);
        if ($decoded === null) {
            return null;
        }

        $allowedSteps = array_map(
            fn ($row) => 'step_'.$row['step_no'],
            $this->workflowStepsByDocType()[$documentType] ?? []
        );
        $allowed = array_merge(['requester'], $allowedSteps);

        $clean = array_values(array_unique(array_intersect($decoded, $allowed)));

        // default `['requester']` → null so we don't bloat the column
        if ($clean === ['requester']) {
            return null;
        }

        return $clean ?: null;
    }

    /**
     * Normalise `visible_to_departments` into a list of integer IDs. Unknown
     * IDs are dropped silently. Empty list → null (visible to everyone).
     */
    private function parseDepartmentIds(array $field): ?array
    {
        $decoded = $this->decodeJsonList($field['visible_to_departments'] ?? null);
        if ($decoded === null) {
            return null;
        }

        $ids = array_values(array_unique(array_map('intval', $decoded)));
        $ids = array_values(array_filter($ids, fn ($id) => $id > 0));
        if (! $ids) {
            return null;
        }

        $valid = Department::whereIn('id', $ids)->pluck('id')->all();
        $valid = array_values(array_intersect($ids, $valid));

        return $valid ?: null;
    }

    /**
     * Decode a JSON-string field into an array of scalars. Returns null for
     * empty/invalid payloads; callers can distinguish "not set" vs "empty list".
     */
    private function decodeJsonList(?string $raw): ?array
    {
        if ($raw === null || $raw === '' || $raw === '[]') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return array_values(array_filter($decoded, fn ($v) => $v !== null && $v !== ''));
    }
}
