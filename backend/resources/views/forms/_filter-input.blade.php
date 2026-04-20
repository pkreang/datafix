@php
    $key = $field->field_key;
    $label = $field->label;
    $type = $field->field_type;
    $value = $filters[$key] ?? null;
    $options = is_array($field->options) ? $field->options : [];
@endphp

<div>
    <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">
        {{ $label }}
    </label>

    @if(in_array($type, ['select', 'radio'], true))
        <select name="{{ $key }}" class="form-input">
            <option value="">{{ __('common.status_all') }}</option>
            @foreach($options as $opt)
                <option value="{{ $opt }}" @selected((string) $value === (string) $opt)>{{ $opt }}</option>
            @endforeach
        </select>
    @elseif(in_array($type, ['date', 'datetime'], true))
        @php
            $from = $filters[$key.'_from'] ?? '';
            $to = $filters[$key.'_to'] ?? '';
            $inputType = $type === 'datetime' ? 'datetime-local' : 'date';
        @endphp
        <div class="flex items-center gap-2">
            <input type="{{ $inputType }}" name="{{ $key }}_from" value="{{ $from }}" class="form-input">
            <span class="text-xs text-slate-400">–</span>
            <input type="{{ $inputType }}" name="{{ $key }}_to" value="{{ $to }}" class="form-input">
        </div>
    @elseif($type === 'number')
        <input type="number" step="any" name="{{ $key }}" value="{{ $value }}" class="form-input">
    @else
        {{-- text, textarea, email, phone, multi_select, lookup → single text input (LIKE) --}}
        <input type="text" name="{{ $key }}" value="{{ is_array($value) ? implode(',', $value) : $value }}" class="form-input">
    @endif
</div>
