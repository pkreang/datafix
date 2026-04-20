@php
    $inputClass = 'form-input mt-1';

    // Visibility check (Feature 1B) — department-based (server-side)
    $userDeptId = $userDeptId ?? null;
    $editorRole = $editorRole ?? 'requester';
    $visibleDepts = $field->visible_to_departments;
    $isVisible = empty($visibleDepts)
        || ($userDeptId !== null && in_array((int) $userDeptId, array_map('intval', $visibleDepts)));

    // Editability check (Feature 2)
    $effectiveEditableBy = $field->effective_editable_by; // accessor: null → ['requester']
    $isReadOnly = $field->field_type !== 'section'
        && !in_array($editorRole, $effectiveEditableBy);
    $readonlyClass = $isReadOnly ? ' opacity-70 cursor-not-allowed bg-gray-50 dark:bg-gray-800' : '';

    // Visibility rules (client-side conditional show/hide based on other field values)
    $visibilityRules = $field->visibility_rules ?? [];
    $hasVisibilityRules = !empty($visibilityRules);

    // Validation rules (HTML5 attributes)
    $validationRules = $field->validation_rules ?? [];
    $validationAttrs = '';
    if (!empty($validationRules['min_length'])) $validationAttrs .= ' minlength="' . (int) $validationRules['min_length'] . '"';
    if (!empty($validationRules['max_length'])) $validationAttrs .= ' maxlength="' . (int) $validationRules['max_length'] . '"';
    if (!empty($validationRules['regex']))      $validationAttrs .= ' pattern="' . e($validationRules['regex']) . '"';
    if (isset($validationRules['min']) && in_array($field->field_type, ['number', 'currency']))
        $validationAttrs .= ' min="' . $validationRules['min'] . '"';
    elseif ($field->field_type === 'currency')
        $validationAttrs .= ' min="0"';
    if (isset($validationRules['max']) && in_array($field->field_type, ['number', 'currency']))
        $validationAttrs .= ' max="' . $validationRules['max'] . '"';
    if ($field->field_type === 'date') {
        if (!empty($validationRules['min_date'])) {
            $resolvedMin = \App\Support\DateExpressionResolver::resolve($validationRules['min_date']);
            if ($resolvedMin) $validationAttrs .= ' min="' . $resolvedMin . '"';
        }
        if (!empty($validationRules['max_date'])) {
            $resolvedMax = \App\Support\DateExpressionResolver::resolve($validationRules['max_date']);
            if ($resolvedMax) $validationAttrs .= ' max="' . $resolvedMax . '"';
        }
    }
@endphp

@if($isVisible)

@if($field->field_type === 'auto_number')
    {{-- Document number — always read-only, auto-generated on submit --}}
    @php
        $autoValue = $value ?? $referenceNo ?? null;
    @endphp
    <input type="text" value="{{ $autoValue }}" readonly
           placeholder="Auto Generate"
           class="form-input mt-1 bg-slate-50 dark:bg-slate-800 cursor-not-allowed font-mono {{ $autoValue ? 'font-semibold text-slate-900 dark:text-slate-100' : 'italic text-slate-400 dark:text-slate-500' }}"
    >

@elseif($field->field_type === 'section')
    {{-- Section divider — display only, no input --}}
    <div class="border-b border-gray-300 dark:border-gray-600 pb-1 mt-2">
        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $field->label }}</h4>
    </div>

@elseif($field->field_type === 'textarea')
    <textarea
        name="{{ $name }}"
        @required($field->is_required && !$isReadOnly)
        @readonly($isReadOnly)
        placeholder="{{ $field->placeholder }}"
        {!! $validationAttrs !!}
        class="{{ $inputClass }}{{ $readonlyClass }}"
    >{{ $value }}</textarea>

@elseif($field->field_type === 'select')
    <select name="{{ $name }}" @required($field->is_required && !$isReadOnly) @disabled($isReadOnly) class="{{ $inputClass }}{{ $readonlyClass }}">
        <option value="">{{ __('common.please_select') }}</option>
        @foreach(($field->options ?? []) as $option)
            <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
        @endforeach
    </select>

@elseif($field->field_type === 'radio')
    <div class="mt-2 space-y-1">
        @foreach(($field->options ?? []) as $option)
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 {{ $isReadOnly ? 'opacity-70' : '' }}">
                <input type="radio" name="{{ $name }}" value="{{ $option }}" @checked($value === $option) @disabled($isReadOnly) class="border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                {{ $option }}
            </label>
        @endforeach
    </div>

@elseif($field->field_type === 'checkbox')
    @if(!empty($field->options))
        <div class="mt-2 space-y-1">
            @foreach(($field->options ?? []) as $option)
                @php $checked = is_array($value) ? in_array($option, $value) : false; @endphp
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 {{ $isReadOnly ? 'opacity-70' : '' }}">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $option }}" @checked($checked) @disabled($isReadOnly) class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    {{ $option }}
                </label>
            @endforeach
        </div>
    @else
        <div class="mt-2">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 {{ $isReadOnly ? 'opacity-70' : '' }}">
                <input type="checkbox" name="{{ $name }}" value="1" @checked($value) @disabled($isReadOnly) class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                {{ $field->label }}
            </label>
        </div>
    @endif

@elseif($field->field_type === 'file')
    @if($isReadOnly)
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 italic">
            {{ $value ? basename((string) $value) : '—' }}
        </p>
    @else
        <input type="file" name="{{ $name }}" @required($field->is_required)
               class="mt-1 w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 dark:file:bg-gray-700 file:text-blue-700 dark:file:text-gray-300">
    @endif

@elseif($field->field_type === 'image')
    @if($value && is_string($value))
        <img src="{{ \Illuminate\Support\Facades\Storage::url($value) }}" alt="{{ $field->label }}"
             class="mb-2 max-h-40 rounded border border-gray-200 dark:border-gray-600">
    @endif
    @if(!$isReadOnly)
        <input type="file" name="{{ $name }}" accept="image/*" @required($field->is_required && !$value)
               class="mt-1 w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 dark:file:bg-gray-700 file:text-blue-700 dark:file:text-gray-300">
    @endif

@elseif($field->field_type === 'multi_select')
    @php $selected = is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?: []) : []); @endphp
    <select multiple name="{{ $name }}[]" @required($field->is_required && !$isReadOnly) @disabled($isReadOnly)
            class="{{ $inputClass }}{{ $readonlyClass }} h-32">
        @foreach(($field->options ?? []) as $option)
            <option value="{{ $option }}" @selected(in_array($option, $selected, true))>{{ $option }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('common.multi_select_hint') }}</p>

@elseif($field->field_type === 'signature')
    @if($isReadOnly)
        @if($value)
            <img src="{{ $value }}" alt="{{ $field->label }}" class="mt-1 max-h-24 border border-gray-200 dark:border-gray-600 rounded-lg">
        @else
            <p class="mt-1 text-sm text-gray-400 italic">—</p>
        @endif
    @else
    <div class="mt-1" x-data="{ signatureData: '{{ $value }}', _inited: false }" x-init="
        $nextTick(() => {
            const c = $refs.signatureCanvas;
            c.width = c.offsetWidth;
            c.height = c.offsetHeight;
            _inited = true;
        })
    ">
        <canvas
            class="w-full h-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 cursor-crosshair"
            x-ref="signatureCanvas"
            @mousedown="
                const canvas = $refs.signatureCanvas;
                if (!_inited) { canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; _inited = true; }
                const ctx = canvas.getContext('2d');
                let drawing = true;
                ctx.strokeStyle = document.documentElement.classList.contains('dark') ? '#fff' : '#000';
                ctx.lineWidth = 2;
                ctx.beginPath();
                const rect = canvas.getBoundingClientRect();
                ctx.moveTo($event.clientX - rect.left, $event.clientY - rect.top);
                const move = (e) => { if(drawing) { ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top); ctx.stroke(); }};
                const up = () => { drawing = false; signatureData = canvas.toDataURL(); canvas.removeEventListener('mousemove', move); canvas.removeEventListener('mouseup', up); };
                canvas.addEventListener('mousemove', move);
                canvas.addEventListener('mouseup', up);
            "
        ></canvas>
        <input type="hidden" name="{{ $name }}" :value="signatureData">
        <button type="button" @click="
            const canvas = $refs.signatureCanvas;
            const ctx = canvas.getContext('2d');
            canvas.width = canvas.offsetWidth;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            signatureData = '';
        " class="mt-1 text-xs text-red-500 hover:underline">{{ __('common.delete') }}</button>
    </div>
    @endif

@elseif(in_array($field->field_type, ['lookup', 'user_lookup', 'equipment_lookup']))
    @php
        $source = match($field->field_type) {
            'user_lookup' => 'user',
            'equipment_lookup' => 'equipment',
            default => $field->options['source'] ?? null,
        };
        $dependsOn = $field->options['depends_on'] ?? null;
        $foreignKey = $field->options['foreign_key'] ?? null;
    @endphp

    @if($dependsOn && $foreignKey)
        {{-- Cascading lookup: load items dynamically via fetch --}}
        <div x-data="cascadingLookup('{{ $source }}', '{{ $dependsOn }}', '{{ $foreignKey }}', '{{ $value }}')" x-init="init()">
            <select name="{{ $name }}" @required($field->is_required && !$isReadOnly) @disabled($isReadOnly) x-model="selected" class="{{ $inputClass }}{{ $readonlyClass }}">
                <option value="">{{ __('common.please_select') }}</option>
                <template x-for="item in items" :key="item.value">
                    <option :value="item.value" x-text="item.display"></option>
                </template>
            </select>
            <p x-show="loading" class="text-xs text-gray-400 mt-1">{{ __('common.loading') }}...</p>
        </div>
    @else
        {{-- Static lookup: pre-load all items --}}
        @php
            $lookupItems = $source ? \App\Support\LookupRegistry::getItems($source) : collect();
        @endphp
        <select name="{{ $name }}" @required($field->is_required && !$isReadOnly) @disabled($isReadOnly) class="{{ $inputClass }}{{ $readonlyClass }}">
            <option value="">{{ __('common.please_select') }}</option>
            @foreach($lookupItems as $item)
                <option value="{{ $item['value'] }}" @selected($value == $item['value'])>{{ $item['display'] }}</option>
            @endforeach
        </select>
    @endif

@elseif($field->field_type === 'table')
    @php
        $columns = $field->options['columns'] ?? [];
        $tableData = is_array($value) ? $value : (json_decode($value ?? '[]', true) ?: []);
    @endphp
    @if(count($columns))
        <div x-data="{
            columns: @js($columns),
            rows: @js(count($tableData) ? $tableData : []),
            addRow() {
                let row = {};
                this.columns.forEach(c => row[c.key] = '');
                this.rows.push(row);
                this.notifyChange();
            },
            removeRow(i) { this.rows.splice(i, 1); this.notifyChange(); },
            notifyChange() {
                window.dispatchEvent(new CustomEvent('documentform:table-changed', {
                    detail: { fieldKey: @js($field->field_key), rows: this.rows }
                }));
            },
            /** Evaluate a simple arithmetic formula like 'qty * unit_price' against row columns.
             *  Whitelisted tokens: column identifiers, numbers, + - * / ( ) . Fallback to 0 on error. */
            computeFormula(formula, row) {
                if (!formula) return 0;
                const tokens = String(formula).match(/[A-Za-z_][A-Za-z0-9_]*|\d+(?:\.\d+)?|[+\-*/()%]/g) || [];
                if (!tokens.length) return 0;
                const expr = tokens.map(t => /^[A-Za-z_]/.test(t) ? '(' + Number(row[t] || 0) + ')' : t).join('');
                try {
                    const val = Function('\"use strict\"; return (' + expr + ');')();
                    return Number.isFinite(val) ? val : 0;
                } catch (e) { return 0; }
            },
            formatNumber(n) {
                const num = Number(n) || 0;
                return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }" x-init="
            if(!rows.length && !{{ $isReadOnly ? 'true' : 'false' }}) addRow();
            $watch('rows', () => notifyChange(), { deep: true });
        ">
            <input type="hidden" name="{{ $name }}" :value="JSON.stringify(rows)">
            <div class="mt-1 overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 w-10">#</th>
                            <template x-for="col in columns" :key="col.key">
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400" x-text="col.label"></th>
                            </template>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, ri) in rows" :key="ri">
                            <tr class="border-t border-gray-200 dark:border-gray-600">
                                <td class="px-3 py-2 text-gray-400 text-xs" x-text="ri + 1"></td>
                                <template x-for="col in columns" :key="col.key">
                                    <td class="px-3 py-1">
                                        <template x-if="!col.formula && col.type === 'select'">
                                            <select x-model="row[col.key]" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value=""></option>
                                                <template x-for="opt in (col.options || '').split(',').map(o => o.trim()).filter(Boolean)" :key="opt">
                                                    <option :value="opt" x-text="opt"></option>
                                                </template>
                                            </select>
                                        </template>
                                        <template x-if="!col.formula && col.type === 'checkbox'">
                                            <input type="checkbox" x-model="row[col.key]" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        </template>
                                        <template x-if="!col.formula && col.type === 'date'">
                                            <input type="date" x-model="row[col.key]" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        </template>
                                        <template x-if="!col.formula && col.type === 'number'">
                                            <input type="number" step="0.01" x-model="row[col.key]" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        </template>
                                        <template x-if="!col.formula && col.type === 'lookup'">
                                            <div x-data="{ items: [], loaded: false, lastFilterKey: '' }"
                                                 x-effect="
                                                    if (!col.lookup_source) return;
                                                    let filterKey = '';
                                                    const params = new URLSearchParams({ source: col.lookup_source });
                                                    if (col.depends_on && col.foreign_key) {
                                                        const parentVal = row[col.depends_on];
                                                        if (!parentVal) {
                                                            items = []; loaded = false; lastFilterKey = '';
                                                            if (row[col.key]) row[col.key] = '';
                                                            return;
                                                        }
                                                        params.append('filters[' + col.foreign_key + ']', parentVal);
                                                        filterKey = col.foreign_key + '=' + parentVal;
                                                    }
                                                    if (lastFilterKey === filterKey && loaded) return;
                                                    lastFilterKey = filterKey;
                                                    fetch('/lookup?' + params.toString(), { headers: {'X-Requested-With': 'XMLHttpRequest'} })
                                                        .then(r => r.json())
                                                        .then(j => {
                                                            items = j.data || [];
                                                            loaded = true;
                                                            if (row[col.key] && !items.some(i => String(i.value) === String(row[col.key]))) {
                                                                row[col.key] = '';
                                                            }
                                                        });
                                                 ">
                                                @if($isReadOnly)
                                                    <span class="text-sm text-gray-900 dark:text-gray-100" x-text="(items.find(i => String(i.value) === String(row[col.key]))?.display) || row[col.key] || '—'"></span>
                                                @else
                                                    <select x-model="row[col.key]" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                        <option value="">{{ __('common.please_select') }}</option>
                                                        <template x-for="item in items" :key="item.value">
                                                            <option :value="item.value" x-text="item.display"></option>
                                                        </template>
                                                    </select>
                                                @endif
                                            </div>
                                        </template>
                                        <template x-if="col.formula" x-effect="row[col.key] = computeFormula(col.formula, row)">
                                            <span class="inline-block w-full px-2 py-1.5 text-sm text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/50 rounded text-right font-mono" x-text="formatNumber(row[col.key])"></span>
                                        </template>
                                        <template x-if="!col.formula && (!col.type || col.type === 'text')">
                                            <input type="text" x-model="row[col.key]" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        </template>
                                    </td>
                                </template>
                                <td class="px-3 py-2">
                                    <button type="button" @click="removeRow(ri)" class="text-red-500 hover:text-red-700 text-xs">&times;</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            @if(!$isReadOnly)
            <button type="button" @click="addRow()" class="mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">+ {{ __('common.document_form_table_add_row') }}</button>
            @endif
        </div>
    @endif

@elseif($field->field_type === 'currency')
    <div class="relative mt-1">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm">฿</span>
        <input type="number" step="0.01"
               name="{{ $name }}"
               value="{{ $value }}"
               placeholder="{{ $field->placeholder ?? '0.00' }}"
               @required($field->is_required && !$isReadOnly)
               @readonly($isReadOnly)
               {!! $validationAttrs !!}
               class="w-full pl-8 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700{{ $readonlyClass }}">
    </div>

@else
    {{-- text, number, date, time, datetime, email, phone --}}
    @php
        $typeMap = [
            'number' => 'number',
            'date' => 'date',
            'time' => 'time',
            'datetime' => 'datetime-local',
            'email' => 'email',
            'phone' => 'tel',
        ];
        $htmlType = $typeMap[$field->field_type] ?? 'text';
    @endphp
    <input
        type="{{ $htmlType }}"
        step="{{ in_array($field->field_type, ['number', 'currency']) ? '0.01' : '' }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $field->placeholder }}"
        @required($field->is_required && !$isReadOnly)
        @readonly($isReadOnly)
        {!! $validationAttrs !!}
        class="{{ $inputClass }}{{ $readonlyClass }}"
    >
@endif

@endif {{-- end @if($isVisible) --}}
