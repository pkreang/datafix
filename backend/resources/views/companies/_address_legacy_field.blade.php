@php
    $model = $model ?? null;
    $legacyInputId = $legacyInputId ?? 'address-legacy';
@endphp
<div class="md:col-span-2">
    <label for="{{ $legacyInputId }}" class="form-label">{{ __('company.legacy_address') }}</label>
    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('company.legacy_address_help') }}</p>
    <textarea name="address" id="{{ $legacyInputId }}" rows="2"
              class="form-input resize-y @error('address') form-input-error @enderror">{{ old('address', $model?->address) }}</textarea>
    @error('address')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
