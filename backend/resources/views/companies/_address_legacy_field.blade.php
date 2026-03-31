@php
    $model = $model ?? null;
    $legacyInputId = $legacyInputId ?? 'address-legacy';
@endphp
<div class="md:col-span-2">
    <label for="{{ $legacyInputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('company.legacy_address') }}</label>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('company.legacy_address_help') }}</p>
    <textarea name="address" id="{{ $legacyInputId }}" rows="2"
              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-y bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('address') border-red-500 @enderror">{{ old('address', $model?->address) }}</textarea>
    @error('address')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
