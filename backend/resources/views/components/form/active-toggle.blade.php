@props([
    'name' => 'is_active',
    'checked' => true,
    'label' => null,
    'labelClass' => 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1',
])
@php
    $labelText = $label ?? __('common.status');
    $initial = old($name, $checked);
    $initialOn = filter_var($initial, FILTER_VALIDATE_BOOLEAN) || $initial === 1 || $initial === '1';
@endphp
<div {{ $attributes }} x-data="{
    on: {{ $initialOn ? 'true' : 'false' }},
    activeLabel: @js(__('common.active')),
    inactiveLabel: @js(__('common.inactive')),
}">
    <label @class([$labelClass])>{{ $labelText }}</label>
    <div class="flex items-center gap-3">
        <button type="button" role="switch" :aria-checked="on"
                @click="on = !on"
                :class="on ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600'"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                  class="pointer-events-none inline-block h-5 w-5 mt-0.5 rounded-full bg-white shadow transition duration-200 ease-in-out"></span>
        </button>
        <span class="text-sm font-medium"
              :class="on ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'"
              x-text="on ? activeLabel : inactiveLabel"></span>
        <input type="hidden" name="{{ $name }}" x-bind:value="on ? '1' : '0'">
    </div>
</div>
