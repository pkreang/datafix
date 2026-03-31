@php
    $isEdit = isset($documentForm);
    $action = $isEdit ? route('settings.document-forms.update', $documentForm) : route('settings.document-forms.store');
    $cascadingRelations = \App\Support\LookupRegistry::cascadingRelations();
    $initialFields = old('fields', $isEdit ? $documentForm->fields->map(function ($f) {
        $isLookup = $f->field_type === 'lookup';
        $isOldLookup = in_array($f->field_type, ['user_lookup', 'equipment_lookup']);
        $isTable = $f->field_type === 'table';
        return [
            'field_key' => $f->field_key,
            'label' => $f->label,
            'field_type' => $isOldLookup ? 'lookup' : $f->field_type,
            'is_required' => $f->is_required,
            'placeholder' => $f->placeholder,
            'options_raw' => is_array($f->options) && !isset($f->options['source']) && !isset($f->options['columns']) ? implode("\n", $f->options) : '',
            'lookup_source' => $isLookup ? ($f->options['source'] ?? '') : ($isOldLookup ? str_replace('_lookup', '', $f->field_type) : ''),
            'depends_on' => $isLookup ? ($f->options['depends_on'] ?? '') : '',
            'foreign_key' => $isLookup ? ($f->options['foreign_key'] ?? '') : '',
            'col_span' => $f->col_span ?? 0,
            'table_columns' => $isTable ? ($f->options['columns'] ?? []) : [],
        ];
    })->values() : [
        ['field_key' => 'title', 'label' => __('common.document_form_default_title'), 'field_type' => 'text', 'is_required' => true, 'placeholder' => '', 'options_raw' => '', 'lookup_source' => '', 'depends_on' => '', 'foreign_key' => '', 'col_span' => 0, 'table_columns' => []],
        ['field_key' => 'amount', 'label' => __('common.document_form_default_amount'), 'field_type' => 'number', 'is_required' => true, 'placeholder' => '', 'options_raw' => '', 'lookup_source' => '', 'depends_on' => '', 'foreign_key' => '', 'col_span' => 0, 'table_columns' => []],
    ]);
@endphp

<div x-data="formBuilder({{ Js::from($initialFields) }}, {{ Js::from($lookupSources) }}, {{ Js::from($cascadingRelations) }})">
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.document_form_key') }}</label>
                <input name="form_key" value="{{ old('form_key', $documentForm->form_key ?? '') }}" required class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.name') }}</label>
                <input name="name" value="{{ old('name', $documentForm->name ?? '') }}" required class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.document_type') }}</label>
                <select name="document_type" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    @php $docType = old('document_type', $documentForm->document_type ?? ''); @endphp
                    @foreach(\App\Models\DocumentType::allActive() as $dt)
                        <option value="{{ $dt->code }}" @selected($docType === $dt->code)>{{ $dt->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.form_layout') }}</label>
                <select name="layout_columns" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    @php $layoutCols = (int) old('layout_columns', $documentForm->layout_columns ?? 1); @endphp
                    <option value="1" @selected($layoutCols === 1)>{{ __('common.form_layout_1col') }}</option>
                    <option value="2" @selected($layoutCols === 2)>{{ __('common.form_layout_2col') }}</option>
                    <option value="3" @selected($layoutCols === 3)>{{ __('common.form_layout_3col') }}</option>
                    <option value="4" @selected($layoutCols === 4)>{{ __('common.form_layout_4col') }}</option>
                </select>
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $documentForm->is_active ?? true))>
                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.active') }}</span>
                </label>
            </div>
        </div>

        <div>
            <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.remark') }}</label>
            <textarea name="description" rows="2" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description', $documentForm->description ?? '') }}</textarea>
        </div>

        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('common.document_form_fields') }}</h3>
            <div class="flex gap-2">
                <button type="button" @click="addField()" class="px-3 py-2 rounded bg-blue-600 text-white text-sm">+ {{ __('common.document_form_add_field') }}</button>
                <button type="button" @click="addSection()" class="px-3 py-2 rounded bg-gray-500 text-white text-sm">+ {{ __('common.document_form_add_section') }}</button>
            </div>
        </div>

        <template x-for="(field, idx) in fields" :key="idx">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/20 p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <p class="font-medium">{{ __('common.document_form_field_short') }} <span x-text="idx + 1"></span></p>
                    <div class="space-x-2">
                        <button type="button" @click="moveUp(idx)" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs">{{ __('common.move_up') }}</button>
                        <button type="button" @click="moveDown(idx)" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs">{{ __('common.move_down') }}</button>
                        <button type="button" @click="removeField(idx)" class="px-2 py-1 rounded bg-red-600 text-white text-xs">{{ __('common.delete') }}</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div x-show="field.field_type !== 'section'">
                        <label class="text-xs text-gray-500">{{ __('common.document_form_field_key') }}</label>
                        <input :name="`fields[${idx}][field_key]`" x-model="field.field_key" :required="field.field_type !== 'section'" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                    <template x-if="field.field_type === 'section'">
                        <input type="hidden" :name="`fields[${idx}][field_key]`" :value="field.field_key">
                    </template>
                    <div :class="field.field_type === 'section' ? 'md:col-span-2' : ''">
                        <label class="text-xs text-gray-500" x-text="field.field_type === 'section' ? '{{ __('common.document_form_section_title') }}' : '{{ __('common.document_form_field_label') }}'"></label>
                        <input :name="`fields[${idx}][label]`" x-model="field.label" required class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">{{ __('common.document_form_field_type') }}</label>
                        <select :name="`fields[${idx}][field_type]`" x-model="field.field_type" @change="if(field.field_type !== 'lookup') { field.lookup_source=''; field.depends_on=''; field.foreign_key=''; }" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
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
                            <option value="signature">{{ __('common.document_form_type_signature') }}</option>
                            <option value="lookup">{{ __('common.document_form_type_lookup') }}</option>
                            <option value="table">{{ __('common.document_form_type_table') }}</option>
                            <option value="section">{{ __('common.document_form_type_section') }}</option>
                        </select>
                    </div>
                    <div x-show="!['lookup','table','section'].includes(field.field_type)">
                        <label class="text-xs text-gray-500">{{ __('common.document_form_placeholder') }}</label>
                        <input :name="`fields[${idx}][placeholder]`" x-model="field.placeholder" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="md:col-span-2" x-show="['select','radio','checkbox'].includes(field.field_type)">
                        <label class="text-xs text-gray-500">{{ __('common.document_form_options_hint') }}</label>
                        <textarea :name="`fields[${idx}][options_raw]`" x-model="field.options_raw" rows="2" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"></textarea>
                    </div>

                    {{-- Lookup config --}}
                    <template x-if="field.field_type === 'lookup'">
                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-3 border-t border-gray-100 dark:border-gray-700 pt-3">
                            <div>
                                <label class="text-xs text-gray-500">{{ __('common.document_form_lookup_source') }}</label>
                                <select :name="`fields[${idx}][lookup_source]`" x-model="field.lookup_source" @change="autoSuggestForeignKey(field)" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">{{ __('common.please_select') }}</option>
                                    <template x-for="[key, src] in Object.entries(lookupSources)" :key="key">
                                        <option :value="key" x-text="src.label_{{ app()->getLocale() }} || src.label_en"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ __('common.document_form_depends_on') }}</label>
                                <select :name="`fields[${idx}][depends_on]`" x-model="field.depends_on" @change="autoSuggestForeignKey(field)" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">{{ __('common.none') }}</option>
                                    <template x-for="(other, oi) in fields" :key="'dep-'+oi">
                                        <template x-if="oi !== idx && other.field_type === 'lookup' && other.field_key">
                                            <option :value="other.field_key" x-text="other.label || other.field_key"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ __('common.document_form_foreign_key') }}</label>
                                <input :name="`fields[${idx}][foreign_key]`" x-model="field.foreign_key" placeholder="e.g. company_id" class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            </div>
                        </div>
                    </template>

                    {{-- Table columns config --}}
                    <template x-if="field.field_type === 'table'">
                        <div class="md:col-span-3 border-t border-gray-100 dark:border-gray-700 pt-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-gray-500">{{ __('common.document_form_table_columns') }}</p>
                                <button type="button" @click="addTableColumn(field)" class="px-2 py-1 rounded bg-blue-600 text-white text-xs">+ {{ __('common.document_form_table_add_column') }}</button>
                            </div>
                            <template x-for="(col, ci) in field.table_columns" :key="ci">
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <label class="text-xs text-gray-400" x-show="ci === 0">{{ __('common.document_form_field_key') }}</label>
                                        <input x-model="col.key" placeholder="key" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                                    </div>
                                    <div class="flex-1">
                                        <label class="text-xs text-gray-400" x-show="ci === 0">{{ __('common.document_form_field_label') }}</label>
                                        <input x-model="col.label" placeholder="{{ __('common.document_form_field_label') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                                    </div>
                                    <div class="w-32">
                                        <label class="text-xs text-gray-400" x-show="ci === 0">{{ __('common.document_form_field_type') }}</label>
                                        <select x-model="col.type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                            <option value="text">{{ __('common.document_form_type_text') }}</option>
                                            <option value="number">{{ __('common.document_form_type_number') }}</option>
                                            <option value="select">{{ __('common.document_form_type_select') }}</option>
                                            <option value="checkbox">{{ __('common.document_form_type_checkbox') }}</option>
                                            <option value="date">{{ __('common.document_form_type_date') }}</option>
                                        </select>
                                    </div>
                                    <button type="button" @click="field.table_columns.splice(ci, 1)" class="px-2 py-2 rounded bg-red-600 text-white text-xs shrink-0">{{ __('common.delete') }}</button>
                                </div>
                            </template>
                            <input type="hidden" :name="`fields[${idx}][table_columns]`" :value="JSON.stringify(field.table_columns)">
                        </div>
                    </template>
                </div>
                <div class="flex items-center gap-4" x-show="field.field_type !== 'section'">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" :name="`fields[${idx}][is_required]`" value="1" x-model="field.is_required">
                        <span class="text-xs text-gray-600 dark:text-gray-300">{{ __('common.document_form_required') }}</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">{{ __('common.document_form_col_span') }}</span>
                        <select :name="`fields[${idx}][col_span]`" x-model.number="field.col_span" class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="0">{{ __('common.document_form_col_span_auto') }}</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <div class="flex items-center justify-end gap-2">
            <button type="button" @click="showPreview = true"
                    class="px-4 py-2 rounded text-sm border bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ __('common.document_form_preview') }}
                </span>
            </button>
            <a href="{{ route('settings.document-forms.index') }}" class="px-4 py-2 rounded bg-gray-300 dark:bg-gray-700 text-sm">{{ __('common.cancel') }}</a>
            <button class="px-4 py-2 rounded bg-blue-600 text-white text-sm">{{ __('common.save') }}</button>
        </div>
    </form>

    {{-- Preview Modal --}}
    <div x-show="showPreview" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showPreview = false">

        {{-- Backdrop --}}
        <div x-show="showPreview" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-black/50" @click="showPreview = false"></div>

        {{-- Modal Content --}}
        <div x-show="showPreview" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-lg max-h-[85vh] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('common.document_form_preview') }}</h3>
                </div>
                <button type="button" @click="showPreview = false"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Hint --}}
            <div class="px-5 py-2 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-900/30 shrink-0">
                <p class="text-xs text-blue-600 dark:text-blue-400">{{ __('common.document_form_preview_hint') }}</p>
            </div>

            {{-- Body --}}
            <div class="px-5 py-5 overflow-y-auto">
                <template x-if="fields.length === 0">
                    <p class="text-sm text-gray-400 dark:text-gray-500 italic">{{ __('common.document_form_preview_empty') }}</p>
                </template>

                <div class="grid gap-4" :style="`grid-template-columns: repeat(${layoutColumns}, minmax(0, 1fr))`">
                <template x-for="(field, idx) in fields" :key="'preview-'+idx">
                    <div :style="field.field_type === 'section' ? `grid-column: span ${layoutColumns}` : previewGridStyle(field)">
                        {{-- section divider --}}
                        <template x-if="field.field_type === 'section'">
                            <div class="border-b border-gray-300 dark:border-gray-600 pb-1 mt-2">
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200" x-text="field.label || '{{ __('common.document_form_type_section') }}'"></h4>
                            </div>
                        </template>

                        <template x-if="field.field_type !== 'section'">
                            <label class="text-sm text-gray-600 dark:text-gray-300">
                                <span x-text="field.label || '{{ __('common.document_form_field_untitled') }}'"></span>
                                <span x-show="field.is_required" class="text-red-500">*</span>
                            </label>
                        </template>

                        {{-- textarea --}}
                        <template x-if="field.field_type === 'textarea'">
                            <textarea disabled rows="3"
                                      :placeholder="field.placeholder || ''"
                                      class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed"></textarea>
                        </template>

                        {{-- select --}}
                        <template x-if="field.field_type === 'select'">
                            <select disabled class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                                <option>{{ __('common.please_select') }}</option>
                                <template x-for="opt in (field.options_raw || '').split('\n').filter(o => o.trim())" :key="opt">
                                    <option x-text="opt.trim()"></option>
                                </template>
                            </select>
                        </template>

                        {{-- number --}}
                        <template x-if="field.field_type === 'number'">
                            <input type="number" step="0.01" disabled
                                   :placeholder="field.placeholder || '0.00'"
                                   class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                        </template>

                        {{-- date --}}
                        <template x-if="field.field_type === 'date'">
                            <input type="date" disabled class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                        </template>

                        {{-- time --}}
                        <template x-if="field.field_type === 'time'">
                            <input type="time" disabled class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                        </template>

                        {{-- datetime --}}
                        <template x-if="field.field_type === 'datetime'">
                            <input type="datetime-local" disabled class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                        </template>

                        {{-- email --}}
                        <template x-if="field.field_type === 'email'">
                            <input type="email" disabled :placeholder="field.placeholder || 'name@example.com'" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                        </template>

                        {{-- phone --}}
                        <template x-if="field.field_type === 'phone'">
                            <input type="tel" disabled :placeholder="field.placeholder || '0xx-xxx-xxxx'" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                        </template>

                        {{-- currency --}}
                        <template x-if="field.field_type === 'currency'">
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">฿</span>
                                <input type="number" step="0.01" disabled :placeholder="field.placeholder || '0.00'" class="w-full pl-8 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                            </div>
                        </template>

                        {{-- checkbox --}}
                        <template x-if="field.field_type === 'checkbox'">
                            <div class="mt-2 space-y-1">
                                <template x-for="opt in (field.options_raw || '').split('\n').filter(o => o.trim())" :key="opt">
                                    <label class="flex items-center gap-2 text-sm text-gray-400">
                                        <input type="checkbox" disabled class="rounded border-gray-300 dark:border-gray-600">
                                        <span x-text="opt.trim()"></span>
                                    </label>
                                </template>
                                <template x-if="!(field.options_raw || '').trim()">
                                    <label class="flex items-center gap-2 text-sm text-gray-400">
                                        <input type="checkbox" disabled class="rounded border-gray-300 dark:border-gray-600">
                                        <span x-text="field.label || '{{ __('common.document_form_field_untitled') }}'"></span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        {{-- radio --}}
                        <template x-if="field.field_type === 'radio'">
                            <div class="mt-2 space-y-1">
                                <template x-for="opt in (field.options_raw || '').split('\n').filter(o => o.trim())" :key="opt">
                                    <label class="flex items-center gap-2 text-sm text-gray-400">
                                        <input type="radio" disabled class="border-gray-300 dark:border-gray-600">
                                        <span x-text="opt.trim()"></span>
                                    </label>
                                </template>
                                <template x-if="!(field.options_raw || '').trim()">
                                    <p class="text-xs text-gray-400 italic mt-1">{{ __('common.document_form_options_hint') }}</p>
                                </template>
                            </div>
                        </template>

                        {{-- file --}}
                        <template x-if="field.field_type === 'file'">
                            <input type="file" disabled class="mt-1 w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-400 cursor-not-allowed">
                        </template>

                        {{-- signature --}}
                        <template x-if="field.field_type === 'signature'">
                            <div class="mt-1 w-full h-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center">
                                <span class="text-xs text-gray-400">{{ __('common.document_form_type_signature') }}</span>
                            </div>
                        </template>

                        {{-- lookup --}}
                        <template x-if="field.field_type === 'lookup'">
                            <select disabled class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                                <option x-text="'{{ __('common.please_select') }} — ' + (lookupSources[field.lookup_source]?.label_{{ app()->getLocale() }} || lookupSources[field.lookup_source]?.label_en || '...')"></option>
                            </select>
                        </template>

                        {{-- table --}}
                        <template x-if="field.field_type === 'table'">
                            <div class="mt-1 overflow-x-auto">
                                <table class="min-w-full border border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <template x-for="col in (field.table_columns || [])" :key="col.key">
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600" x-text="col.label || col.key"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <template x-for="col in (field.table_columns || [])" :key="'cell-'+col.key">
                                                <td class="px-3 py-2 border-b border-gray-200 dark:border-gray-600">
                                                    <span class="text-gray-300 dark:text-gray-500">—</span>
                                                </td>
                                            </template>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="text-xs text-gray-400 mt-1 italic">{{ __('common.document_form_table_add_row_hint') }}</p>
                            </div>
                        </template>

                        {{-- text (default) --}}
                        <template x-if="field.field_type === 'text' || (!field.field_type)">
                            <input type="text" disabled
                                   :placeholder="field.placeholder || ''"
                                   class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-400 cursor-not-allowed">
                        </template>
                    </div>
                </template>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 shrink-0 flex justify-end">
                <button type="button" @click="showPreview = false"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    {{ __('common.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function formBuilder(initialFields, lookupSources, cascadingRelations) {
        return {
            fields: initialFields || [],
            lookupSources: lookupSources || {},
            cascadingRelations: cascadingRelations || {},
            showPreview: false,
            get layoutColumns() {
                return parseInt(document.querySelector('select[name="layout_columns"]')?.value || 1);
            },
            previewGridStyle(field) {
                const cols = this.layoutColumns;
                const span = (field.col_span && cols > 1) ? Math.min(field.col_span, cols) : 1;
                return span > 1 ? `grid-column: span ${span}` : '';
            },
            addField() {
                this.fields.push({field_key: '', label: '', field_type: 'text', is_required: false, placeholder: '', options_raw: '', lookup_source: '', depends_on: '', foreign_key: '', col_span: 0, table_columns: []});
            },
            addSection() {
                const idx = this.fields.filter(f => f.field_type === 'section').length + 1;
                this.fields.push({field_key: 'section_' + idx, label: '', field_type: 'section', is_required: false, placeholder: '', options_raw: '', lookup_source: '', depends_on: '', foreign_key: '', col_span: 0, table_columns: []});
            },
            addTableColumn(field) {
                if (!field.table_columns) field.table_columns = [];
                field.table_columns.push({key: '', label: '', type: 'text'});
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
