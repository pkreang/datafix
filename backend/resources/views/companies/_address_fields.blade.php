@php
    /** @var string $prefix Input name prefix: '' or 'branch_' */
    $prefix = $prefix ?? '';
    $model = $model ?? null;
    $showLegacy = $showLegacy ?? true;
    $includeLegacyInPartial = $includeLegacyInPartial ?? true;
    $legacyInputId = $legacyInputId ?? null;
    $idBase = $prefix === '' ? 'addr-' : 'branch-addr-';
    $field = static fn (string $key): string => $prefix.$key;
    $value = static fn (string $key) => old($field($key), $model?->{$key});
    $thaiPickerConfig = [
        'searchUrl' => route('addresses.thailand.subdistricts'),
    ];
@endphp

<div x-data="thaiSubdistrictPicker({{ \Illuminate\Support\Js::from($thaiPickerConfig) }})" class="contents">

<div class="md:col-span-2">
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('company.address_structured_hint') }}</p>
</div>

<div>
    <label for="{{ $idBase }}no" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.address_no') }}</label>
    <input type="text" name="{{ $field('address_no') }}" id="{{ $idBase }}no" value="{{ $value('address_no') }}" maxlength="50"
           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error($field('address_no')) border-red-500 @enderror">
    @error($field('address_no'))
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="{{ $idBase }}building" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.address_building') }}</label>
    <input type="text" name="{{ $field('address_building') }}" id="{{ $idBase }}building" value="{{ $value('address_building') }}" maxlength="255"
           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error($field('address_building')) border-red-500 @enderror">
    @error($field('address_building'))
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

{{-- ซอย แล้วตามด้วย ถนน (แถวเดียวกัน: ซ้ายซอย ขวาถนน) --}}
<div>
    <label for="{{ $idBase }}soi" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.address_soi') }}</label>
    <input type="text" name="{{ $field('address_soi') }}" id="{{ $idBase }}soi" value="{{ $value('address_soi') }}" maxlength="255"
           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error($field('address_soi')) border-red-500 @enderror">
    @error($field('address_soi'))
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="{{ $idBase }}street" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.address_street') }}</label>
    <input type="text" name="{{ $field('address_street') }}" id="{{ $idBase }}street" value="{{ $value('address_street') }}" maxlength="255"
           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error($field('address_street')) border-red-500 @enderror">
    @error($field('address_street'))
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

{{-- ตำบล: ค้นหาแล้วเลือกจากรายการ (แสดง ตำบล » อำเภอ » จังหวัด » รหัสไปรษณีย์) --}}
<div class="md:col-span-2 relative">
    <label for="{{ $idBase }}subdistrict" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.address_subdistrict') }}</label>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('company.address_subdistrict_search_hint') }}</p>
    <input type="text" name="{{ $field('address_subdistrict') }}" id="{{ $idBase }}subdistrict" x-ref="subdistrict"
           value="{{ $value('address_subdistrict') }}" maxlength="120" autocomplete="off"
           @input="onSubdistrictInput($event)"
           @focus="onFocus()"
           @blur="onBlurSoon()"
           @keydown="onKeydown($event)"
           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error($field('address_subdistrict')) border-red-500 @enderror">
    @error($field('address_subdistrict'))
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <div x-show="open && (results.length > 0 || loading)" x-cloak
         class="absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg">
        <template x-if="loading">
            <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ __('company.address_search_loading') }}</div>
        </template>
        <template x-if="!loading">
            <template x-for="(item, index) in results" :key="item.i + '-' + index">
                <button type="button"
                        class="w-full text-left px-3 py-2 text-sm border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-gray-100 dark:hover:bg-gray-700/80 text-gray-900 dark:text-gray-100"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': highlighted === index }"
                        @mousedown.prevent="select(item)"
                        x-text="labelLine(item)"></button>
            </template>
        </template>
    </div>
</div>

<div>
    <label for="{{ $idBase }}district" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.address_district') }}</label>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1" x-show="pickerLocked" x-cloak>{{ __('company.address_picker_locked_hint') }}</p>
    <input type="text" name="{{ $field('address_district') }}" id="{{ $idBase }}district" x-ref="district" value="{{ $value('address_district') }}" maxlength="120"
           :readonly="pickerLocked"
           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 read-only:bg-gray-50 read-only:cursor-default dark:read-only:bg-gray-800/70 @error($field('address_district')) border-red-500 @enderror">
    @error($field('address_district'))
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="{{ $idBase }}province" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.address_province') }}</label>
    <input type="text" name="{{ $field('address_province') }}" id="{{ $idBase }}province" x-ref="province" value="{{ $value('address_province') }}" maxlength="120"
           :readonly="pickerLocked"
           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 read-only:bg-gray-50 read-only:cursor-default dark:read-only:bg-gray-800/70 @error($field('address_province')) border-red-500 @enderror">
    @error($field('address_province'))
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

{{-- รหัสไปรษณีย์อยู่ท้ายสุด (หลังจังหวัด) --}}
<div class="md:col-span-2">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[12rem] max-w-xs">
            <label for="{{ $idBase }}postal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.address_postal_code') }}</label>
            <input type="text" name="{{ $field('address_postal_code') }}" id="{{ $idBase }}postal" x-ref="postal" value="{{ $value('address_postal_code') }}" maxlength="10"
                   :readonly="pickerLocked"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 read-only:bg-gray-50 read-only:cursor-default dark:read-only:bg-gray-800/70 @error($field('address_postal_code')) border-red-500 @enderror">
            @error($field('address_postal_code'))
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <button type="button" x-show="pickerLocked" x-cloak @click="unlockPickerFields()"
                class="mb-0.5 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
            {{ __('company.address_picker_unlock') }}
        </button>
    </div>
</div>

</div>

@if ($showLegacy && $prefix === '' && $includeLegacyInPartial)
    @include('companies._address_legacy_field', [
        'model' => $model,
        'legacyInputId' => $legacyInputId ?? $idBase.'legacy-address',
    ])
@endif

