@php
    /** @var \App\Models\Company $company */
@endphp

<div class="mt-10 pt-8 border-t border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ __('company.branches_section') }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('company.branches_section_hint') }}</p>

    @if ($company->branches->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('company.branches_empty') }}</p>
    @else
        <div class="space-y-6 mb-8">
            @foreach ($company->branches as $branch)
                <div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900/30 p-4">
                    <form method="POST" action="{{ route('companies.branches.update', [$company, $branch]) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('company.branch_code') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="code" value="{{ $branch->code }}" required maxlength="50"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('company.branch_name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ $branch->name }}" required maxlength="255"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            </div>
                            @include('companies._address_fields', [
                                'prefix' => '',
                                'model' => $branch,
                                'showLegacy' => true,
                                'legacyInputId' => 'branch-legacy-'.$branch->id,
                            ])
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('company.branch_phone') }}</label>
                                <input type="text" name="phone" value="{{ $branch->phone }}" maxlength="20"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            </div>
                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($branch->is_active)
                                           class="rounded border-gray-300 text-blue-600">
                                    {{ __('company.branch_active') }}
                                </label>
                            </div>
                        </div>
                        @can('manage companies')
                            <div class="flex flex-wrap gap-2 pt-2">
                                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg">{{ __('common.save') }}</button>
                            </div>
                        @endcan
                    </form>
                    @can('manage companies')
                        <form method="POST" action="{{ route('companies.branches.destroy', [$company, $branch]) }}" class="mt-2"
                              onsubmit="return confirm(@json(__('company.branch_delete_confirm')));">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">{{ __('company.branch_delete') }}</button>
                        </form>
                    @endcan
                </div>
            @endforeach
        </div>
    @endif

    @can('manage companies')
        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('company.add_branch') }}</h4>
        <form method="POST" action="{{ route('companies.branches.store', $company) }}" class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-4 space-y-3 bg-gray-50/80 dark:bg-gray-900/20">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('company.branch_code') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="branch_code" value="{{ old('branch_code') }}" required maxlength="50"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('company.branch_name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="branch_name" value="{{ old('branch_name') }}" required maxlength="255"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                </div>
                @include('companies._address_fields', ['prefix' => 'branch_', 'model' => null, 'showLegacy' => false])
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('company.branch_phone') }}</label>
                    <input type="text" name="branch_phone" value="{{ old('branch_phone') }}" maxlength="20"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="hidden" name="branch_is_active" value="0">
                        <input type="checkbox" name="branch_is_active" value="1" @checked(old('branch_is_active', '1') === '1')
                               class="rounded border-gray-300 text-blue-600">
                        {{ __('company.branch_active') }}
                    </label>
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 dark:bg-gray-600 hover:bg-gray-900 dark:hover:bg-gray-500 text-white text-sm font-medium rounded-lg">
                {{ __('company.add_branch') }}
            </button>
        </form>
    @endcan
</div>
