@php
    $documentForm = $documentForm ?? null;
    $isEdit = $documentForm !== null;
    $action = $isEdit ? route('settings.document-forms.update', $documentForm) : route('settings.document-forms.store');
    $cascadingRelations = \App\Support\LookupRegistry::cascadingRelations();
    $searchableTypes = \App\Models\DocumentFormField::SEARCHABLE_TYPES;
    $workflowStepsByDocType = $workflowStepsByDocType ?? [];
    $departments = $departments ?? collect();
    $initialFields = old('fields', $isEdit ? $documentForm->fields->map(function ($f) {
        $isLookup = $f->field_type === 'lookup';
        $isOldLookup = in_array($f->field_type, ['user_lookup', 'equipment_lookup']);
        $isTable = $f->field_type === 'table';
        return [
            'field_key' => $f->field_key,
            'label' => $f->label,
            'field_type' => $isOldLookup ? 'lookup' : $f->field_type,
            'is_required' => $f->is_required,
            'is_searchable' => $f->is_searchable,
            'placeholder' => $f->placeholder,
            'options_raw' => is_array($f->options) && !isset($f->options['source']) && !isset($f->options['columns']) ? implode("\n", $f->options) : '',
            'lookup_source' => $isLookup ? ($f->options['source'] ?? '') : ($isOldLookup ? str_replace('_lookup', '', $f->field_type) : ''),
            'depends_on' => $isLookup ? ($f->options['depends_on'] ?? '') : '',
            'foreign_key' => $isLookup ? ($f->options['foreign_key'] ?? '') : '',
            'col_span' => $f->col_span ?? 0,
            'table_columns' => $isTable ? ($f->options['columns'] ?? []) : [],
            'visibility_rules' => $f->visibility_rules ?? [],
            'validation_rules' => (object) ($f->validation_rules ?? []),
            'editable_by' => $f->editable_by ?? ['requester'],
            'visible_to_departments' => array_map('intval', $f->visible_to_departments ?? []),
        ];
    })->values() : [
        ['field_key' => 'title', 'label' => __('common.document_form_default_title'), 'field_type' => 'text', 'is_required' => true, 'is_searchable' => true, 'placeholder' => '', 'options_raw' => '', 'lookup_source' => '', 'depends_on' => '', 'foreign_key' => '', 'col_span' => 0, 'table_columns' => [], 'visibility_rules' => [], 'validation_rules' => new \stdClass, 'editable_by' => ['requester'], 'visible_to_departments' => []],
        ['field_key' => 'amount', 'label' => __('common.document_form_default_amount'), 'field_type' => 'number', 'is_required' => true, 'is_searchable' => true, 'placeholder' => '', 'options_raw' => '', 'lookup_source' => '', 'depends_on' => '', 'foreign_key' => '', 'col_span' => 0, 'table_columns' => [], 'visibility_rules' => [], 'validation_rules' => new \stdClass, 'editable_by' => ['requester'], 'visible_to_departments' => []],
    ]);
@endphp

@php
    $initialDocumentType = old('document_type', $documentForm?->document_type ?? '');
    $roleLabels = [
        'requester' => __('common.role_requester'),
        'step_prefix' => __('common.role_step_prefix'),
    ];
    $departmentsJs = $departments->map(fn ($d) => ['id' => (int) $d->id, 'name' => $d->name])->values()->all();
@endphp

<div x-data="formBuilder({{ Js::from($initialFields) }}, {{ Js::from($lookupSources) }}, {{ Js::from($cascadingRelations) }}, {{ Js::from($searchableTypes) }}, {{ Js::from($workflowStepsByDocType) }}, {{ Js::from($departmentsJs) }}, {{ Js::from($initialDocumentType) }}, {{ Js::from($roleLabels) }})">
    {{-- Preview Modal — teleported to <body> to escape stacking context --}}
    <template x-teleport="body">
    <div x-show="showPreview" x-cloak
         class="fixed inset-0 flex items-center justify-center overflow-hidden p-4 sm:p-6 md:p-8"
         style="z-index:9999"
         @keydown.escape.window="showPreview = false">

        {{-- Backdrop --}}
        <div x-show="showPreview" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-black/50 dark:bg-black/60" @click="showPreview = false"></div>

        {{-- Modal panel --}}
        <div x-show="showPreview" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-[0.98]"
             class="document-form-preview-frame relative flex flex-col min-h-0 overflow-hidden rounded-2xl shadow-2xl bg-white dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-700">

            {{-- Header --}}
            <div class="shrink-0 flex items-center justify-between px-6 sm:px-8 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="shrink-0 w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="previewTitle || '{{ __('common.document_form_preview') }}'"></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.document_form_preview_hint') }}</p>
                    </div>
                </div>
                <button type="button" @click="showPreview = false"
                        class="shrink-0 p-2 -mr-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        aria-label="{{ __('common.close') }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form preview body --}}
            <div class="document-form-preview-scroll flex-1 min-h-0 overflow-y-auto px-6 py-6 sm:px-10 sm:py-8 bg-white dark:bg-gray-900">
                <template x-if="fields.length === 0">
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-base text-gray-400 dark:text-gray-500">{{ __('common.document_form_preview_empty') }}</p>
                    </div>
                </template>

                <div class="grid gap-5 sm:gap-6" :style="`grid-template-columns: repeat(${layoutColumns}, minmax(0, 1fr))`">
                <template x-for="(field, idx) in fields" :key="'preview-'+field._rowId">
                    <div :style="field.field_type === 'section' ? `grid-column: span ${layoutColumns}` : previewGridStyle(field)">
                        {{-- section divider --}}
                        <template x-if="field.field_type === 'section'">
                            <div class="pt-4 pb-2 first:pt-0">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 pb-2 border-b-2 border-blue-500/30 dark:border-blue-400/30" x-text="field.label || '{{ __('common.document_form_type_section') }}'"></h4>
                            </div>
                        </template>

                        <template x-if="field.field_type !== 'section'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span x-text="field.label || '{{ __('common.document_form_field_untitled') }}'"></span>
                                <span x-show="field.is_required" class="text-red-500 ml-0.5">*</span>
                            </label>
                        </template>

                        {{-- textarea --}}
                        <template x-if="field.field_type === 'textarea'">
                            <textarea readonly rows="3" tabindex="-1"
                                      :placeholder="field.placeholder || ''"
                                      class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none"></textarea>
                        </template>

                        {{-- select --}}
                        <template x-if="field.field_type === 'select'">
                            <select tabindex="-1" class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none">
                                <option value="">{{ __('common.please_select') }}</option>
                                <template x-for="opt in (field.options_raw || '').split('\n').filter(o => o.trim())" :key="opt">
                                    <option x-text="opt.trim()"></option>
                                </template>
                            </select>
                        </template>

                        {{-- multi_select --}}
                        <template x-if="field.field_type === 'multi_select'">
                            <select multiple tabindex="-1" class="mt-1.5 w-full h-24 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none">
                                <template x-for="opt in (field.options_raw || '').split('\n').filter(o => o.trim())" :key="opt">
                                    <option x-text="opt.trim()"></option>
                                </template>
                            </select>
                        </template>

                        {{-- number --}}
                        <template x-if="field.field_type === 'number'">
                            <input type="number" step="0.01" readonly tabindex="-1"
                                   :placeholder="field.placeholder || '0.00'"
                                   class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none">
                        </template>

                        {{-- date --}}
                        <template x-if="field.field_type === 'date'">
                            <input type="date" readonly tabindex="-1" class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none">
                        </template>

                        {{-- time --}}
                        <template x-if="field.field_type === 'time'">
                            <input type="time" readonly tabindex="-1" class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none">
                        </template>

                        {{-- datetime --}}
                        <template x-if="field.field_type === 'datetime'">
                            <input type="datetime-local" readonly tabindex="-1" class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none">
                        </template>

                        {{-- email --}}
                        <template x-if="field.field_type === 'email'">
                            <input type="email" readonly tabindex="-1" :placeholder="field.placeholder || 'name@example.com'" class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none">
                        </template>

                        {{-- phone --}}
                        <template x-if="field.field_type === 'phone'">
                            <input type="tel" readonly tabindex="-1" :placeholder="field.placeholder || '0xx-xxx-xxxx'" class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none">
                        </template>

                        {{-- currency --}}
                        <template x-if="field.field_type === 'currency'">
                            <div class="relative mt-1.5">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm font-medium">฿</span>
                                <input type="number" step="0.01" readonly tabindex="-1" :placeholder="field.placeholder || '0.00'" class="w-full pl-8 pr-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none">
                            </div>
                        </template>

                        {{-- checkbox --}}
                        <template x-if="field.field_type === 'checkbox'">
                            <div class="mt-2 space-y-2">
                                <template x-for="opt in (field.options_raw || '').split('\n').filter(o => o.trim())" :key="opt">
                                    <label class="flex items-center gap-2.5 text-sm text-gray-700 dark:text-gray-300 pointer-events-none select-none">
                                        <input type="checkbox" tabindex="-1" class="h-4 w-4 rounded border-gray-300 dark:border-gray-500 text-blue-600 accent-blue-600">
                                        <span x-text="opt.trim()"></span>
                                    </label>
                                </template>
                                <template x-if="!(field.options_raw || '').trim()">
                                    <label class="flex items-center gap-2.5 text-sm text-gray-700 dark:text-gray-300 pointer-events-none select-none">
                                        <input type="checkbox" tabindex="-1" class="h-4 w-4 rounded border-gray-300 dark:border-gray-500 text-blue-600 accent-blue-600">
                                        <span x-text="field.label || '{{ __('common.document_form_field_untitled') }}'"></span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        {{-- radio --}}
                        <template x-if="field.field_type === 'radio'">
                            <div class="mt-2 space-y-2">
                                <template x-for="opt in (field.options_raw || '').split('\n').filter(o => o.trim())" :key="opt">
                                    <label class="flex items-center gap-2.5 text-sm text-gray-700 dark:text-gray-300 pointer-events-none select-none">
                                        <input type="radio" tabindex="-1" class="h-4 w-4 border-gray-300 dark:border-gray-500 text-blue-600 accent-blue-600">
                                        <span x-text="opt.trim()"></span>
                                    </label>
                                </template>
                                <template x-if="!(field.options_raw || '').trim()">
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('common.document_form_options_hint') }}</p>
                                </template>
                            </div>
                        </template>

                        {{-- file --}}
                        <template x-if="field.field_type === 'file'">
                            <div class="mt-1.5 flex items-center gap-3 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/60 px-4 py-5 pointer-events-none select-none">
                                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('common.document_form_type_file') }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ __('common.document_form_preview_file_hint') }}</p>
                                </div>
                            </div>
                        </template>

                        {{-- image --}}
                        <template x-if="field.field_type === 'image'">
                            <div class="mt-1.5 flex items-center gap-3 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/60 px-4 py-5 pointer-events-none select-none">
                                <svg class="w-6 h-6 text-gray-400 dark:text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('common.document_form_type_image') }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">accept="image/*"</p>
                                </div>
                            </div>
                        </template>

                        {{-- signature --}}
                        <template x-if="field.field_type === 'signature'">
                            <div class="mt-1.5 w-full min-h-[6rem] rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/60 flex items-center justify-center pointer-events-none select-none">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto text-gray-300 dark:text-gray-600 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('common.document_form_type_signature') }}</span>
                                </div>
                            </div>
                        </template>

                        {{-- lookup --}}
                        <template x-if="field.field_type === 'lookup'">
                            <select tabindex="-1" class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none">
                                <option value="" x-text="field.lookup_source ? ('{{ __('common.please_select') }} — ' + (lookupSources[field.lookup_source]?.label_{{ app()->getLocale() }} || lookupSources[field.lookup_source]?.label_en || '')) : '{{ __('common.document_form_preview_lookup_pick_source') }}'"></option>
                            </select>
                        </template>

                        {{-- table --}}
                        <template x-if="field.field_type === 'table'">
                            <div class="mt-1.5">
                                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <template x-for="col in (field.table_columns || [])" :key="col.key">
                                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-600" x-text="col.label || col.key"></th>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-900">
                                            <tr>
                                                <template x-for="col in (field.table_columns || [])" :key="'cell-'+col.key">
                                                    <td class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                                    </td>
                                                </template>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ __('common.document_form_table_add_row_hint') }}</p>
                            </div>
                        </template>

                        {{-- auto_number --}}
                        <template x-if="field.field_type === 'auto_number'">
                            <input type="text" readonly tabindex="-1" placeholder="Auto Generate"
                                   class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-400 dark:text-gray-500 italic font-mono pointer-events-none select-none placeholder:italic focus:outline-none">
                        </template>

                        {{-- text (default) --}}
                        <template x-if="field.field_type === 'text' || (!field.field_type)">
                            <input type="text" readonly tabindex="-1"
                                   :placeholder="field.placeholder || ''"
                                   class="mt-1.5 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100 pointer-events-none select-none focus:outline-none">
                        </template>
                    </div>
                </template>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Save confirmation — teleported like preview --}}
    <template x-teleport="body">
        <div x-show="showSaveConfirm" x-cloak
             class="fixed inset-0 z-[10000] flex items-center justify-center overflow-hidden p-4 sm:p-6"
             @keydown.escape.window="showSaveConfirm = false">
            <div x-show="showSaveConfirm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-black/50 dark:bg-black/60" @click="showSaveConfirm = false" aria-hidden="true"></div>
            <div x-show="showSaveConfirm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-[0.98]"
                 class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
                 role="dialog" aria-modal="true" aria-labelledby="doc-form-save-confirm-title" @click.stop>
                <h3 id="doc-form-save-confirm-title" class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('common.document_form_save_confirm_title') }}</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('common.document_form_save_confirm_message') }}</p>
                <div class="mt-6 flex flex-wrap justify-end gap-2">
                    <button type="button" @click="showSaveConfirm = false"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">{{ __('common.cancel') }}</button>
                    <button type="button" @click="confirmSave()"
                            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700">{{ __('common.save') }}</button>
                </div>
            </div>
        </div>
    </template>

    <form id="document-form-builder" method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        @if($inlineToolbar ?? false)
            {{-- Fixed actions + flow spacer sit above the gray card (not inside the “table” panel) --}}
            @include('settings.document-forms._form-fixed-primary-actions')
            <div class="card p-6">
        @endif

        @if ($errors->any())
            <div class="alert-error mb-4">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">{{ __('common.document_form_key') }}</label>
                <input name="form_key" value="{{ old('form_key', $documentForm?->form_key ?? '') }}" required class="form-input mt-1" />
            </div>
            <div>
                <label class="form-label">{{ __('common.name') }}</label>
                <input name="name" value="{{ old('name', $documentForm?->name ?? '') }}" required class="form-input mt-1" />
            </div>
            <div>
                <label class="form-label">{{ __('common.document_type') }}</label>
                <select name="document_type" x-model="currentDocumentType" class="form-input mt-1">
                    @foreach(\App\Models\DocumentType::allActive() as $dt)
                        <option value="{{ $dt->code }}">{{ $dt->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('common.form_layout') }}</label>
                <select name="layout_columns" class="form-input mt-1">
                    @php $layoutCols = (int) old('layout_columns', $documentForm?->layout_columns ?? 1); @endphp
                    <option value="1" @selected($layoutCols === 1)>{{ __('common.form_layout_1col') }}</option>
                    <option value="2" @selected($layoutCols === 2)>{{ __('common.form_layout_2col') }}</option>
                    <option value="3" @selected($layoutCols === 3)>{{ __('common.form_layout_3col') }}</option>
                    <option value="4" @selected($layoutCols === 4)>{{ __('common.form_layout_4col') }}</option>
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('common.table_name') }}</label>
                @if($isEdit && $documentForm?->submission_table)
                    <input name="table_name" value="{{ $documentForm->submission_table }}" readonly
                           class="form-input mt-1 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 cursor-not-allowed" />
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('common.table_name_locked') }}</p>
                @else
                    <input name="table_name" value="{{ old('table_name', $documentForm?->form_key ?? '') }}" required
                           placeholder="เช่น maintenance_requests"
                           pattern="[a-z][a-z0-9_]*" maxlength="64"
                           class="form-input mt-1" />
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('common.table_name_hint') }}</p>
                @endif
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $documentForm?->is_active ?? true))>
                    <span class="text-sm text-slate-600 dark:text-slate-300">{{ __('common.active') }}</span>
                </label>
            </div>
        </div>

        <div>
            <label class="form-label">{{ __('common.remark') }}</label>
            <textarea name="description" rows="2" class="form-input mt-1 resize-y">{{ old('description', $documentForm?->description ?? '') }}</textarea>
        </div>

        @if($inlineToolbar ?? false)
            @include('settings.document-forms._form-inline-field-actions')
        @endif

        <div class="flex w-full flex-wrap items-center gap-x-3 gap-y-2 justify-between border-b border-slate-200/80 pb-3 dark:border-slate-600">
            <div class="flex min-w-0 flex-wrap items-center gap-3">
<h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('common.document_form_fields') }}</h3>
            </div>
            @unless($inlineToolbar ?? false)
                <div class="ml-auto flex shrink-0 flex-wrap justify-end gap-2">
                    <button type="button" @click="addField()" class="px-3 py-2 rounded bg-blue-600 text-white text-sm">+ {{ __('common.document_form_add_field') }}</button>
                    <button type="button" @click="addSection()" class="px-3 py-2 rounded bg-slate-500 text-white text-sm">+ {{ __('common.document_form_add_section') }}</button>
                </div>
            @endunless
        </div>

        <template x-for="(field, idx) in fields" :key="field._rowId">
            <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/20 p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <p class="font-medium">{{ __('common.document_form_field_short') }} <span x-text="idx + 1"></span></p>
                    <div class="space-x-2">
                        <button type="button" @click="moveUp(idx)" class="px-2 py-1 rounded bg-slate-200 dark:bg-slate-700 text-xs">{{ __('common.move_up') }}</button>
                        <button type="button" @click="moveDown(idx)" class="px-2 py-1 rounded bg-slate-200 dark:bg-slate-700 text-xs">{{ __('common.move_down') }}</button>
                        <button type="button" @click="removeField(idx)" class="px-2 py-1 rounded bg-red-600 text-white text-xs">{{ __('common.delete') }}</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div x-show="field.field_type !== 'section'">
                        <label class="text-xs text-slate-500">{{ __('common.document_form_field_key') }}</label>
                        <input :name="`fields[${idx}][field_key]`" x-model="field.field_key" :required="field.field_type !== 'section'" class="form-input mt-1" />
                    </div>
                    <template x-if="field.field_type === 'section'">
                        <input type="hidden" :name="`fields[${idx}][field_key]`" :value="field.field_key">
                    </template>
                    <div :class="field.field_type === 'section' ? 'md:col-span-2' : ''">
                        <label class="text-xs text-slate-500" x-text="field.field_type === 'section' ? '{{ __('common.document_form_section_title') }}' : '{{ __('common.document_form_field_label') }}'"></label>
                        <input :name="`fields[${idx}][label]`" x-model="field.label" required class="form-input mt-1" />
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">{{ __('common.document_form_field_type') }}</label>
                        <select :name="`fields[${idx}][field_type]`" x-model="field.field_type" @change="if(field.field_type !== 'lookup') { field.lookup_source=''; field.depends_on=''; field.foreign_key=''; }" class="form-input mt-1">
                            <option value="text">{{ __('common.document_form_type_text') }}</option>
                            <option value="textarea">{{ __('common.document_form_type_textarea') }}</option>
                            <option value="number">{{ __('common.document_form_type_number') }}</option>
                            <option value="currency">{{ __('common.document_form_type_currency') }}</option>
                            <option value="date">{{ __('common.document_form_type_date') }}</option>
                            <option value="time">{{ __('common.document_form_type_time') }}</option>
                            <option value="datetime">{{ __('common.document_form_type_datetime') }}</option>
                            <option value="select">{{ __('common.document_form_type_select') }}</option>
                            <option value="radio">{{ __('common.document_form_type_radio') }}</option>
                            <option value="checkbox">{{ __('common.document_form_type_checkbox') }}</option>
                            <option value="email">{{ __('common.document_form_type_email') }}</option>
                            <option value="phone">{{ __('common.document_form_type_phone') }}</option>
                            <option value="file">{{ __('common.document_form_type_file') }}</option>
                            <option value="image">{{ __('common.document_form_type_image') }}</option>
                            <option value="signature">{{ __('common.document_form_type_signature') }}</option>
                            <option value="multi_select">{{ __('common.document_form_type_multi_select') }}</option>
                            <option value="lookup">{{ __('common.document_form_type_lookup') }}</option>
                            <option value="table">{{ __('common.document_form_type_table') }}</option>
                            <option value="section">{{ __('common.document_form_type_section') }}</option>
                            <option value="auto_number">{{ __('common.document_form_type_auto_number') }}</option>
                        </select>
                    </div>
                    <div x-show="!['lookup','table','section','image'].includes(field.field_type)">
                        <label class="text-xs text-slate-500">{{ __('common.document_form_placeholder') }}</label>
                        <input :name="`fields[${idx}][placeholder]`" x-model="field.placeholder" class="form-input mt-1" />
                    </div>
                    <div class="md:col-span-2" x-show="['select','radio','checkbox','multi_select'].includes(field.field_type)">
                        <label class="text-xs text-slate-500">{{ __('common.document_form_options_hint') }}</label>
                        <textarea :name="`fields[${idx}][options_raw]`" x-model="field.options_raw" rows="2" class="form-input mt-1 resize-y"></textarea>
                    </div>

                    {{-- Lookup config --}}
                    <template x-if="field.field_type === 'lookup'">
                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-3 border-t border-slate-100 dark:border-slate-700 pt-3">
                            <div>
                                <label class="text-xs text-slate-500">{{ __('common.document_form_lookup_source') }}</label>
                                <select :name="`fields[${idx}][lookup_source]`" x-model="field.lookup_source" @change="autoSuggestForeignKey(field)" class="form-input mt-1">
                                    <option value="">{{ __('common.please_select') }}</option>
                                    <template x-for="[key, src] in Object.entries(lookupSources)" :key="key">
                                        <option :value="key" x-text="src.label_{{ app()->getLocale() }} || src.label_en"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">{{ __('common.document_form_depends_on') }}</label>
                                <select :name="`fields[${idx}][depends_on]`" x-model="field.depends_on" @change="autoSuggestForeignKey(field)" class="form-input mt-1">
                                    <option value="">{{ __('common.none') }}</option>
                                    <template x-for="(other, oi) in fields" :key="'dep-'+oi">
                                        <template x-if="oi !== idx && other.field_type === 'lookup' && other.field_key">
                                            <option :value="other.field_key" x-text="other.label || other.field_key"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">{{ __('common.document_form_foreign_key') }}</label>
                                <input :name="`fields[${idx}][foreign_key]`" x-model="field.foreign_key" placeholder="e.g. company_id" class="form-input mt-1" />
                            </div>
                        </div>
                    </template>

                    {{-- Table columns config --}}
                    <template x-if="field.field_type === 'table'">
                        <div class="md:col-span-3 border-t border-slate-100 dark:border-slate-700 pt-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-slate-500">{{ __('common.document_form_table_columns') }}</p>
                                <button type="button" @click="addTableColumn(field)" class="px-2 py-1 rounded bg-blue-600 text-white text-xs">+ {{ __('common.document_form_table_add_column') }}</button>
                            </div>
                            <template x-for="(col, ci) in field.table_columns" :key="ci">
                                <div class="space-y-1">
                                    <div class="flex items-end gap-2">
                                        <div class="flex-1">
                                            <label class="text-xs text-slate-400" x-show="ci === 0">{{ __('common.document_form_field_key') }}</label>
                                            <input x-model="col.key" placeholder="key" class="form-input" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="text-xs text-slate-400" x-show="ci === 0">{{ __('common.document_form_field_label') }}</label>
                                            <input x-model="col.label" placeholder="{{ __('common.document_form_field_label') }}" class="form-input" />
                                        </div>
                                        <div class="w-32">
                                            <label class="text-xs text-slate-400" x-show="ci === 0">{{ __('common.document_form_field_type') }}</label>
                                            <select x-model="col.type" class="form-input">
                                                <option value="text">{{ __('common.document_form_type_text') }}</option>
                                                <option value="number">{{ __('common.document_form_type_number') }}</option>
                                                <option value="select">{{ __('common.document_form_type_select') }}</option>
                                                <option value="checkbox">{{ __('common.document_form_type_checkbox') }}</option>
                                                <option value="date">{{ __('common.document_form_type_date') }}</option>
                                                <option value="lookup">{{ __('common.document_form_type_lookup') }}</option>
                                            </select>
                                        </div>
                                        <div class="w-36" x-show="col.type === 'lookup'">
                                            <label class="text-xs text-slate-400" x-show="ci === 0">{{ __('common.document_form_lookup_source') }}</label>
                                            <select x-model="col.lookup_source" @change="autoSuggestTableColumnForeignKey(field, col)" class="form-input">
                                                <option value="">{{ __('common.please_select') }}</option>
                                                <template x-for="[key, src] in Object.entries(lookupSources)" :key="key">
                                                    <option :value="key" x-text="src.label_{{ app()->getLocale() }} || src.label_en"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <button type="button" @click="field.table_columns.splice(ci, 1)" class="px-2 py-2 rounded bg-red-600 text-white text-xs shrink-0">{{ __('common.delete') }}</button>
                                    </div>
                                    <div class="flex items-end gap-2 pl-2" x-show="col.type === 'lookup'">
                                        <div class="w-48">
                                            <label class="text-xs text-slate-400">{{ __('common.depends_on') }}</label>
                                            <select x-model="col.depends_on" @change="autoSuggestTableColumnForeignKey(field, col)" class="form-input">
                                                <option value="">—</option>
                                                <template x-for="other in field.table_columns.filter(c => c !== col && c.type === 'lookup' && c.key)" :key="other.key">
                                                    <option :value="other.key" x-text="(other.label || other.key)"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="w-56" x-show="col.depends_on">
                                            <label class="text-xs text-slate-400">{{ __('common.foreign_key') }}</label>
                                            <input x-model="col.foreign_key" placeholder="equipment_category_id" class="form-input" />
                                        </div>
                                    </div>
                                    <div class="flex items-end gap-2 pl-2" x-show="col.type === 'number' || col.type === 'text'">
                                        <div class="flex-1">
                                            <label class="text-xs text-slate-400">{{ __('common.document_form_column_formula') }}</label>
                                            <input x-model="col.formula" placeholder="qty * unit_price" class="form-input font-mono text-sm" />
                                            <p class="text-xs text-slate-400 mt-1">{{ __('common.document_form_column_formula_hint') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <input type="hidden" :name="`fields[${idx}][table_columns]`" :value="JSON.stringify(field.table_columns)">
                        </div>
                    </template>
                </div>
                <div class="flex items-center gap-4" x-show="field.field_type !== 'section'">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" :name="`fields[${idx}][is_required]`" value="1" x-model="field.is_required">
                        <span class="text-xs text-slate-600 dark:text-slate-300">{{ __('common.document_form_required') }}</span>
                    </label>
                    <label class="inline-flex items-center gap-2" x-show="isSearchableType(field.field_type)">
                        <input type="checkbox" :name="`fields[${idx}][is_searchable]`" value="1" x-model="field.is_searchable">
                        <span class="text-xs text-slate-600 dark:text-slate-300">{{ __('common.document_form_searchable') }}</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-500">{{ __('common.document_form_col_span') }}</span>
                        <select :name="`fields[${idx}][col_span]`" x-model.number="field.col_span" class="form-input py-1 px-2 text-xs">
                            <option value="0">{{ __('common.document_form_col_span_auto') }}</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>

                {{-- Advanced: Visibility Rules + Validation Rules --}}
                <div x-data="{ showAdvanced: false }" x-show="field.field_type !== 'section'" class="border-t border-slate-100 dark:border-slate-700 pt-2 mt-2">
                    <button type="button" @click="showAdvanced = !showAdvanced" class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        <svg class="w-3 h-3 transition-transform" :class="showAdvanced ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        {{ __('common.advanced_settings') ?? 'Advanced Settings' }}
                    </button>

                    <div x-show="showAdvanced" x-cloak class="mt-3 space-y-4">
                        {{-- Visibility Rules --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">{{ __('common.visibility_rules') ?? 'Visibility Rules' }}</p>
                            <template x-for="(rule, ri) in (field.visibility_rules || [])" :key="ri">
                                <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,2fr)_auto] items-center gap-2 mb-2">
                                    <select x-model="rule.field" class="form-input py-1 px-2 text-xs min-w-0">
                                        <option value="">{{ __('common.select_field') }}</option>
                                        <template x-for="(other, oi) in fields" :key="'vis-'+oi">
                                            <template x-if="oi !== idx && other.field_key && other.field_type !== 'section'">
                                                <option :value="other.field_key" x-text="other.label || other.field_key"></option>
                                            </template>
                                        </template>
                                    </select>
                                    <select x-model="rule.operator" class="form-input py-1 px-2 text-xs w-20 text-center">
                                        <option value="equals">{{ __('common.op_equals') }}</option>
                                        <option value="not_equals">{{ __('common.op_not_equals') }}</option>
                                        <option value="is_empty">{{ __('common.op_is_empty') }}</option>
                                        <option value="is_not_empty">{{ __('common.op_is_not_empty') }}</option>
                                        <option value="greater_than">{{ __('common.op_greater_than') }}</option>
                                        <option value="less_than">{{ __('common.op_less_than') }}</option>
                                    </select>
                                    <input x-show="!['is_empty','is_not_empty'].includes(rule.operator)" x-model="rule.value" placeholder="{{ __('common.value') }}" class="form-input py-1 px-2 text-xs min-w-0" />
                                    <button type="button" @click="field.visibility_rules.splice(ri, 1)" class="text-red-500 hover:text-red-700 text-xs">&times;</button>
                                </div>
                            </template>
                            <button type="button" @click="if(!field.visibility_rules) field.visibility_rules = []; field.visibility_rules.push({field:'', operator:'equals', value:''})" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">+ {{ __('common.add_condition') ?? 'Add condition' }}</button>
                            <input type="hidden" :name="`fields[${idx}][visibility_rules]`" :value="JSON.stringify(field.visibility_rules || [])">
                        </div>

                        {{-- Validation Rules --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">{{ __('common.validation_rules') ?? 'Validation Rules' }}</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <div x-show="['text','textarea','email','phone'].includes(field.field_type)">
                                    <label class="text-xs text-slate-400">{{ __('common.min_length') ?? 'Min length' }}</label>
                                    <input type="number" min="0" x-model.number="field.validation_rules.min_length" class="form-input py-1 px-2 text-xs mt-1" />
                                </div>
                                <div x-show="['text','textarea','email','phone'].includes(field.field_type)">
                                    <label class="text-xs text-slate-400">{{ __('common.max_length') ?? 'Max length' }}</label>
                                    <input type="number" min="0" x-model.number="field.validation_rules.max_length" class="form-input py-1 px-2 text-xs mt-1" />
                                </div>
                                <div x-show="['text','email','phone'].includes(field.field_type)">
                                    <label class="text-xs text-slate-400">{{ __('common.regex_pattern') ?? 'Regex pattern' }}</label>
                                    <input type="text" x-model="field.validation_rules.regex" placeholder="^[A-Z].*" class="form-input py-1 px-2 text-xs mt-1" />
                                </div>
                                <div x-show="['number','currency'].includes(field.field_type)">
                                    <label class="text-xs text-slate-400">{{ __('common.min_value') ?? 'Min value' }}</label>
                                    <input type="number" step="0.01" x-model.number="field.validation_rules.min" class="form-input py-1 px-2 text-xs mt-1" />
                                </div>
                                <div x-show="['number','currency'].includes(field.field_type)">
                                    <label class="text-xs text-slate-400">{{ __('common.max_value') ?? 'Max value' }}</label>
                                    <input type="number" step="0.01" x-model.number="field.validation_rules.max" class="form-input py-1 px-2 text-xs mt-1" />
                                </div>
                                <div x-show="field.field_type === 'date'">
                                    <label class="text-xs text-slate-400">{{ __('common.min_date') }}</label>
                                    <input type="text" x-model="field.validation_rules.min_date" placeholder="today / 2026-01-01" class="form-input py-1 px-2 text-xs mt-1" />
                                </div>
                                <div x-show="field.field_type === 'date'">
                                    <label class="text-xs text-slate-400">{{ __('common.max_date') }}</label>
                                    <input type="text" x-model="field.validation_rules.max_date" placeholder="today / 2026-12-31" class="form-input py-1 px-2 text-xs mt-1" />
                                </div>
                            </div>
                            <p x-show="field.field_type === 'date'" class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                {{ __('common.date_expression_help') }}
                            </p>
                            <input type="hidden" :name="`fields[${idx}][validation_rules]`" :value="JSON.stringify(field.validation_rules || {})">
                        </div>

                        {{-- Field-level permissions: who can edit this field + which departments see it --}}
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">{{ __('common.field_editable_by') }}</p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1">
                                <template x-for="role in availableRoles" :key="role.value">
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                        <input type="checkbox" :value="role.value"
                                               :checked="(field.editable_by || []).includes(role.value)"
                                               @change="toggleArrayValue(field, 'editable_by', role.value)"
                                               class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                                        <span x-text="role.label"></span>
                                    </label>
                                </template>
                            </div>
                            <p x-show="!(field.editable_by || []).length" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                {{ __('common.field_editable_by_none_hint') }}
                            </p>
                            <input type="hidden" :name="`fields[${idx}][editable_by]`" :value="JSON.stringify(field.editable_by || [])">
                        </div>

                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">{{ __('common.field_visible_to_departments') }}</p>
                            @if(count($departmentsJs))
                                <div class="flex flex-wrap gap-x-4 gap-y-1 max-h-40 overflow-y-auto p-1 rounded border border-slate-200 dark:border-slate-600">
                                    <template x-for="dept in departments" :key="dept.id">
                                        <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                            <input type="checkbox" :value="dept.id"
                                                   :checked="(field.visible_to_departments || []).map(Number).includes(dept.id)"
                                                   @change="toggleArrayValue(field, 'visible_to_departments', dept.id, true)"
                                                   class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700">
                                            <span x-text="dept.name"></span>
                                        </label>
                                    </template>
                                </div>
                                <p x-show="!(field.visible_to_departments || []).length" class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                    {{ __('common.field_visible_to_departments_all_hint') }}
                                </p>
                            @else
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ __('common.field_visible_to_departments_empty') }}</p>
                            @endif
                            <input type="hidden" :name="`fields[${idx}][visible_to_departments]`" :value="JSON.stringify(field.visible_to_departments || [])">
                        </div>
                    </div>
                </div>
            </div>
        </template>

        @if($inlineToolbar ?? false)
            </div>
        @endif

    </form>

</div>

<script>
    function ensureFieldRowId(field) {
        const f = { ...field };
        if (!f._rowId) {
            f._rowId = (typeof crypto !== 'undefined' && crypto.randomUUID)
                ? crypto.randomUUID()
                : 'row_' + Math.random().toString(36).slice(2, 11);
        }
        if (!Array.isArray(f.editable_by)) f.editable_by = ['requester'];
        if (!Array.isArray(f.visible_to_departments)) f.visible_to_departments = [];
        return f;
    }

    function formBuilder(initialFields, lookupSources, cascadingRelations, searchableTypes, workflowStepsByDocType, departments, initialDocumentType, roleLabels) {
        const SEARCHABLE_TYPES = searchableTypes || [];
        const defaultSearchable = (type) => SEARCHABLE_TYPES.includes(type);
        return {
            fields: (initialFields || []).map((f) => ensureFieldRowId(f)),
            lookupSources: lookupSources || {},
            cascadingRelations: cascadingRelations || {},
            searchableTypes: SEARCHABLE_TYPES,
            workflowStepsByDocType: workflowStepsByDocType || {},
            departments: departments || [],
            currentDocumentType: initialDocumentType || '',
            roleLabels: roleLabels || { requester: 'Requester', step_prefix: 'Step' },
            isSearchableType(type) { return SEARCHABLE_TYPES.includes(type); },
            get availableRoles() {
                const steps = this.workflowStepsByDocType[this.currentDocumentType] || [];
                const roles = [{ value: 'requester', label: this.roleLabels.requester }];
                for (const s of steps) {
                    const suffix = s.name ? ': ' + s.name : '';
                    roles.push({ value: 'step_' + s.step_no, label: this.roleLabels.step_prefix + ' ' + s.step_no + suffix });
                }
                return roles;
            },
            toggleArrayValue(field, prop, value, asNumber = false) {
                if (!Array.isArray(field[prop])) field[prop] = [];
                const cast = (v) => asNumber ? Number(v) : v;
                const idx = field[prop].findIndex((v) => cast(v) === cast(value));
                if (idx >= 0) {
                    field[prop].splice(idx, 1);
                } else {
                    field[prop].push(cast(value));
                }
            },
            showPreview: false,
            showSaveConfirm: false,
            previewTitle: '',
            init() {},
            openSaveConfirm() {
                this.showSaveConfirm = true;
            },
            confirmSave() {
                this.showSaveConfirm = false;
                const form = document.getElementById('document-form-builder');
                if (!form) {
                    return;
                }
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            },
            openPreview() {
                const form = this.$el.querySelector('form');
                const name = form?.querySelector('input[name="name"]')?.value?.trim() || '';
                const key = form?.querySelector('input[name="form_key"]')?.value?.trim() || '';
                this.previewTitle = name || key || '';
                this.showPreview = true;
            },
            get layoutColumns() {
                return parseInt(document.querySelector('select[name="layout_columns"]')?.value || 1);
            },
            previewGridStyle(field) {
                const cols = this.layoutColumns;
                const span = (field.col_span && cols > 1) ? Math.min(field.col_span, cols) : 1;
                return span > 1 ? `grid-column: span ${span}` : '';
            },
            addField() {
                this.fields.push(ensureFieldRowId({field_key: '', label: '', field_type: 'text', is_required: false, is_searchable: defaultSearchable('text'), placeholder: '', options_raw: '', lookup_source: '', depends_on: '', foreign_key: '', col_span: 0, table_columns: [], visibility_rules: [], validation_rules: {}}));
            },
            addSection() {
                let max = 0;
                for (const f of this.fields) {
                    const m = /^section_(\d+)$/.exec(f.field_key || '');
                    if (m) max = Math.max(max, parseInt(m[1], 10));
                }
                const n = max + 1;
                this.fields.push(ensureFieldRowId({field_key: 'section_' + n, label: '', field_type: 'section', is_required: false, is_searchable: false, placeholder: '', options_raw: '', lookup_source: '', depends_on: '', foreign_key: '', col_span: 0, table_columns: [], visibility_rules: [], validation_rules: {}}));
            },
            addTableColumn(field) {
                if (!field.table_columns) field.table_columns = [];
                field.table_columns.push({key: '', label: '', type: 'text', lookup_source: '', depends_on: '', foreign_key: '', formula: ''});
            },
            autoSuggestTableColumnForeignKey(field, col) {
                if (!col.lookup_source || !col.depends_on) {
                    col.foreign_key = '';
                    return;
                }
                const parentCol = (field.table_columns || []).find(c => c.key === col.depends_on);
                if (!parentCol || !parentCol.lookup_source) return;
                const relations = this.cascadingRelations[col.lookup_source];
                if (relations && relations[parentCol.lookup_source]) {
                    col.foreign_key = relations[parentCol.lookup_source];
                }
            },
            removeField(idx) {
                this.fields.splice(idx, 1);
            },
            moveUp(idx) {
                if (idx <= 0) return;
                [this.fields[idx - 1], this.fields[idx]] = [this.fields[idx], this.fields[idx - 1]];
            },
            moveDown(idx) {
                if (idx >= this.fields.length - 1) return;
                [this.fields[idx + 1], this.fields[idx]] = [this.fields[idx], this.fields[idx + 1]];
            },
            autoSuggestForeignKey(field) {
                if (!field.lookup_source || !field.depends_on) {
                    field.foreign_key = '';
                    return;
                }
                // Find the parent field's lookup_source
                const parentField = this.fields.find(f => f.field_key === field.depends_on);
                if (!parentField || !parentField.lookup_source) return;
                const relations = this.cascadingRelations[field.lookup_source];
                if (relations && relations[parentField.lookup_source]) {
                    field.foreign_key = relations[parentField.lookup_source];
                }
            }
        }
    }
</script>
