@php
    $company = $company ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.company_code') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="code" id="code" value="{{ old('code', $company?->code) }}" required maxlength="50"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('code') border-red-500 @enderror">
        @error('code')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.company_name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $company?->name) }}" required maxlength="255"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('name') border-red-500 @enderror">
        @error('name')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.tax_id') }}
        </label>
        <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $company?->tax_id) }}" maxlength="20"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('tax_id') border-red-500 @enderror">
        @error('tax_id')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="business_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.business_type') }}
        </label>
        <input type="text" name="business_type" id="business_type" value="{{ old('business_type', $company?->business_type) }}" maxlength="100"
               placeholder="{{ __('company.business_type_placeholder') }}"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('business_type') border-red-500 @enderror">
        @error('business_type')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    @include('companies._address_fields', [
        'prefix' => '',
        'model' => $company,
        'showLegacy' => true,
        'includeLegacyInPartial' => false,
    ])
    @include('companies._address_legacy_field', ['model' => $company])

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.phone') }}
        </label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $company?->phone) }}" maxlength="20"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('phone') border-red-500 @enderror">
        @error('phone')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.email') }}
        </label>
        <input type="email" name="email" id="email" value="{{ old('email', $company?->email) }}"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('email') border-red-500 @enderror">
        @error('email')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="fax" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.fax') }}
        </label>
        <input type="text" name="fax" id="fax" value="{{ old('fax', $company?->fax) }}" maxlength="20"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('fax') border-red-500 @enderror">
        @error('fax')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.website') }}
        </label>
        <input type="text" name="website" id="website" value="{{ old('website', $company?->website) }}" maxlength="255"
               placeholder="https://..."
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('website') border-red-500 @enderror">
        @error('website')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.description') }}
        </label>
        <textarea name="description" id="description" rows="3" maxlength="1000"
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('description') border-red-500 @enderror">{{ old('description', $company?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('company.logo') }}
        </label>
        @if ($company?->logo)
            <div class="mb-2">
                <img src="{{ asset('storage/' . $company->logo) }}" alt="" class="w-20 h-20 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('company.current_logo') }}</p>
            </div>
        @endif
        <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 dark:file:bg-gray-600 dark:file:text-gray-200 @error('logo') border-red-500 @enderror">
        @error('logo')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2" x-data="{ isActive: {{ old('is_active', $company?->is_active ?? '1') ? 'true' : 'false' }} }">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('company.status') }}
        </label>
        <div class="flex items-center gap-3">
            <button type="button" @click="isActive = true"
                    :class="isActive ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition">
                {{ __('common.active') }}
            </button>
            <button type="button" @click="isActive = false"
                    :class="!isActive ? 'bg-gray-600 text-white dark:bg-gray-500' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition">
                {{ __('common.inactive') }}
            </button>
            <input type="hidden" name="is_active" :value="isActive ? '1' : '0'">
        </div>
    </div>
</div>
