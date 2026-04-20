@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'autoSubmit' => true,
])
@php
    $value = $value ?? request($name, '');
    $placeholder = $placeholder ?? __('common.all');
@endphp

<div>
    @if($label)
        <label for="filter-{{ $name }}" class="form-label text-xs mb-1">{{ $label }}</label>
    @endif
    <select name="{{ $name }}"
            id="filter-{{ $name }}"
            @if($autoSubmit) onchange="this.form.submit()" @endif
            class="form-input w-auto">
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>
</div>
