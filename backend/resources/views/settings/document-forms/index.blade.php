@extends('layouts.app')

@section('title', __('common.document_forms'))

@section('content')
@php
    $totalForms = $forms->count();
@endphp
<div x-data="{ search: '' }">
    <div class="flex items-center justify-between mb-2">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.document_forms') }}</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ __('common.document_forms_desc') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('common.document_forms_list_subtitle', ['count' => $totalForms]) }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.document-forms.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('common.add') }}
            </a>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-5">
        <div class="relative max-w-sm">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" x-model="search" placeholder="{{ __('common.search_forms_placeholder') }}"
                   class="form-input w-full pl-10">
        </div>
    </div>

    @if (session('error'))
        <div class="alert-error mb-4">{{ session('error') }}</div>
    @endif

    @if (session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="table-wrapper pb-24">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    <th class="table-header">{{ __('common.name') }}</th>
                    <th class="table-header">{{ __('common.document_type') }}</th>
                    <th class="table-header">{{ __('common.fields') }}</th>
                    <th class="table-header">{{ __('common.workflow_policy') }}</th>
                    <th class="table-header">{{ __('common.status') }}</th>
                    <th class="table-header">{{ __('common.updated_at') }}</th>
                    <th class="table-header text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($forms as $form)
                    @php
                        $searchBlob = Str::lower($form->name . ' ' . $form->form_key . ' ' . $form->document_type);
                    @endphp
                    <tr
                        class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150"
                        data-search="{{ e($searchBlob) }}"
                        x-show="!search.trim() || ($el.dataset.search || '').includes(search.toLowerCase())"
                    >
                        <td class="px-6 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-violet-500 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $form->name }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate font-mono">{{ $form->form_key }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <span class="badge-gray">{{ $form->document_type }}</span>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                            {{ $form->fields_count }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            @php $mainPolicy = $form->workflowPolicies->first(); @endphp
                            @if (!$mainPolicy)
                                <span class="badge-gray">{{ __('common.policy_summary_not_configured') }}</span>
                            @elseif ($mainPolicy->use_amount_condition)
                                <span class="badge-blue">{{ __('common.policy_summary_amount_ranges', ['count' => $mainPolicy->ranges->count()]) }}</span>
                            @elseif ($mainPolicy->workflow)
                                <span class="badge-blue">{{ $mainPolicy->workflow->name }}</span>
                            @else
                                <span class="badge-gray">{{ __('common.policy_summary_not_configured') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            @if ($form->is_active)
                                <span class="badge-green">{{ __('common.active') }}</span>
                            @else
                                <span class="badge-red">{{ __('common.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                            {{ $form->updated_at ? $form->updated_at->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-right">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                        class="p-1 rounded-lg text-slate-400 dark:text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-cloak
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-44 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50">
                                    <a href="{{ route('settings.document-forms.edit', $form) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        {{ __('common.edit') }}
                                    </a>
                                    <a href="{{ route('settings.document-forms.policy.edit', $form) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        {{ __('common.workflow_policy') }}
                                    </a>
                                    <form method="POST" action="{{ route('settings.document-forms.destroy', $form) }}"
                                          onsubmit="return confirm('{{ __('common.delete_confirm_msg', ['name' => $form->name]) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-slate-50 dark:hover:bg-slate-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('common.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
