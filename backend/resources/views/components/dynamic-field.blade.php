@php
    $inputClass = 'mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700';
@endphp

@if($field->field_type === 'section')
    {{-- Section divider — display only, no input --}}
    <div class="border-b border-gray-300 dark:border-gray-600 pb-1 mt-2">
        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $field->label }}</h4>
    </div>

@elseif($field->field_type === 'textarea')
    <textarea
        name="{{ $name }}"
        @required($field->is_required)
        placeholder="{{ $field->placeholder }}"
        class="{{ $inputClass }}"
    >{{ $value }}</textarea>

@elseif($field->field_type === 'select')
    <select name="{{ $name }}" @required($field->is_required) class="{{ $inputClass }}">
        <option value="">{{ __('common.please_select') }}</option>
        @foreach(($field->options ?? []) as $option)
            <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
        @endforeach
    </select>

@elseif($field->field_type === 'radio')
    <div class="mt-2 space-y-1">
        @foreach(($field->options ?? []) as $option)
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="radio" name="{{ $name }}" value="{{ $option }}" @checked($value === $option) class="border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                {{ $option }}
            </label>
        @endforeach
    </div>

@elseif($field->field_type === 'checkbox')
    @if(!empty($field->options))
        <div class="mt-2 space-y-1">
            @foreach(($field->options ?? []) as $option)
                @php $checked = is_array($value) ? in_array($option, $value) : false; @endphp
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $option }}" @checked($checked) class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    {{ $option }}
                </label>
            @endforeach
        </div>
    @else
        <div class="mt-2">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="{{ $name }}" value="1" @checked($value) class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                {{ $field->label }}
            </label>
        </div>
    @endif

@elseif($field->field_type === 'file')
    <input type="file" name="{{ $name }}" @required($field->is_required)
           class="mt-1 w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-50 dark:file:bg-gray-700 file:text-blue-700 dark:file:text-gray-300">

@elseif($field->field_type === 'signature')
    <div class="mt-1" x-data="{ signatureData: '{{ $value }}' }">
        <canvas
            class="w-full h-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 cursor-crosshair"
            x-ref="signatureCanvas"
            @mousedown="
                const canvas = $refs.signatureCanvas;
                const ctx = canvas.getContext('2d');
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
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
            <select name="{{ $name }}" @required($field->is_required) x-model="selected" class="{{ $inputClass }}">
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
        <select name="{{ $name }}" @required($field->is_required) class="{{ $inputClass }}">
            <option value="">{{ __('common.please_select') }}</option>
            @foreach($lookupItems as $item)
                <option value="{{ $item['value'] }}" @selected($value == $item['value'])>{{ $item['display'] }}</option>
            @endforeach
        </select>
    @endif

@elseif($field->field_type === 'table')
    @php
        $columns = $field->options['columns'] ?? [];
        $tableData = is_array($value) ? $value : json_decode($value ?? '[]', true) ?: [];
    @endphp
    @if(count($columns))
        <div x-data="{
            columns: @js($columns),
            rows: @js(count($tableData) ? $tableData : []),
            addRow() {
                let row = {};
                this.columns.forEach(c => row[c.key] = '');
                this.rows.push(row);
            },
            removeRow(i) { this.rows.splice(i, 1); }
        }" x-init="if(!rows.length) addRow()">
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
                                        <template x-if="col.type === 'select'">
                                            <select x-model="row[col.key]" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                <option value=""></option>
                                                <template x-for="opt in (col.options || '').split(',').map(o => o.trim()).filter(Boolean)" :key="opt">
                                                    <option :value="opt" x-text="opt"></option>
                                                </template>
                                            </select>
                                        </template>
                                        <template x-if="col.type === 'checkbox'">
                                            <input type="checkbox" x-model="row[col.key]" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        </template>
                                        <template x-if="col.type === 'date'">
                                            <input type="date" x-model="row[col.key]" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        </template>
                                        <template x-if="col.type === 'number'">
                                            <input type="number" step="0.01" x-model="row[col.key]" class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        </template>
                                        <template x-if="!col.type || col.type === 'text'">
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
            <button type="button" @click="addRow()" class="mt-2 text-sm text-blue-600 dark:text-blue-400 hover:underline">+ {{ __('common.document_form_table_add_row') }}</button>
        </div>
    @endif

@elseif($field->field_type === 'currency')
    <div class="relative mt-1">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm">฿</span>
        <input type="number" step="0.01" min="0"
               name="{{ $name }}"
               value="{{ $value }}"
               placeholder="{{ $field->placeholder ?? '0.00' }}"
               @required($field->is_required)
               class="w-full pl-8 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
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
        @required($field->is_required)
        class="{{ $inputClass }}"
    >
@endif
