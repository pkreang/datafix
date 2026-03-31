<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApprovalInstance;
use App\Models\DocumentForm;
use App\Support\LookupRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DocumentFormController extends Controller
{
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

        return view('settings.document-forms.create', compact('lookupSources'));
    }

    public function edit(DocumentForm $documentForm): View
    {
        $documentForm->load('fields');
        $lookupSources = LookupRegistry::sources();

        return view('settings.document-forms.edit', compact('documentForm', 'lookupSources'));
    }

    private function fieldRules(): array
    {
        $sourceKeys = implode(',', LookupRegistry::sourceKeys());

        return [
            'fields' => 'required|array|min:1',
            'fields.*.field_key' => 'required|string|max:100|alpha_dash',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.field_type' => 'required|in:text,textarea,number,date,select,checkbox,radio,file,time,datetime,email,phone,signature,currency,lookup,table,section,user_lookup,equipment_lookup',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.options_raw' => 'nullable|string',
            'fields.*.lookup_source' => "nullable|string|in:{$sourceKeys}",
            'fields.*.depends_on' => 'nullable|string|max:100',
            'fields.*.foreign_key' => 'nullable|string|max:100',
            'fields.*.table_columns' => 'nullable|string',
            'fields.*.col_span' => 'nullable|integer|min:0|max:4',
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'form_key' => 'required|string|max:100|alpha_dash|unique:document_forms,form_key',
            'name' => 'required|string|max:255',
            'document_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'layout_columns' => 'nullable|integer|in:1,2,3,4',
        ], $this->fieldRules()));

        DB::transaction(function () use ($validated) {
            $form = DocumentForm::create([
                'form_key' => $validated['form_key'],
                'name' => $validated['name'],
                'document_type' => $validated['document_type'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'layout_columns' => (int) ($validated['layout_columns'] ?? 1),
            ]);

            foreach ($validated['fields'] as $index => $field) {
                $form->fields()->create([
                    'field_key' => $field['field_key'],
                    'label' => $field['label'],
                    'field_type' => $field['field_type'],
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'sort_order' => $index + 1,
                    'col_span' => (int) ($field['col_span'] ?? 0),
                    'placeholder' => $field['placeholder'] ?? null,
                    'options' => $this->parseOptions($field),
                ]);
            }
        });

        return redirect()->route('settings.document-forms.index')->with('success', __('common.saved'));
    }

    public function update(Request $request, DocumentForm $documentForm): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'form_key' => "required|string|max:100|alpha_dash|unique:document_forms,form_key,{$documentForm->id}",
            'name' => 'required|string|max:255',
            'document_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'layout_columns' => 'nullable|integer|in:1,2,3,4',
        ], $this->fieldRules()));

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
                    'sort_order' => $index + 1,
                    'col_span' => (int) ($field['col_span'] ?? 0),
                    'placeholder' => $field['placeholder'] ?? null,
                    'options' => $this->parseOptions($field),
                ]);
            }
        });

        return redirect()->route('settings.document-forms.edit', $documentForm)->with('success', __('common.updated'));
    }

    public function destroy(DocumentForm $documentForm): RedirectResponse
    {
        if (ApprovalInstance::where('document_type', $documentForm->document_type)->exists()) {
            return redirect()->route('settings.document-forms.index')
                ->with('error', __('common.cannot_delete_document_form'));
        }

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
}
