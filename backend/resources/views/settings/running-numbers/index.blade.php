@extends('layouts.app')

@section('title', __('common.running_numbers'))

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('common.running_numbers') }}</h2>
        <a href="{{ route('settings.running-numbers.create') }}" class="btn-primary">
            {{ __('common.add') }} {{ __('common.running_numbers') }}
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    @endif

    <div class="table-wrapper">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    <th class="table-header">{{ __('common.document_type') }}</th>
                    <th class="table-header">{{ __('common.running_number_prefix') }}</th>
                    <th class="table-header">{{ __('common.running_number_preview') }}</th>
                    <th class="table-header">{{ __('common.running_number_current') }}</th>
                    <th class="table-header">{{ __('common.running_number_reset_mode') }}</th>
                    <th class="table-header">{{ __('common.status') }}</th>
                    <th class="table-header text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($configs as $config)
                    @php
                        $now = now();
                        $preview = $config->prefix;
                        if ($config->include_year) $preview .= $now->format('Y');
                        if ($config->include_month) $preview .= $now->format('m');
                        $preview .= '-' . str_pad($config->last_number + 1, $config->digit_count, '0', STR_PAD_LEFT);
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $config->document_type }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $config->prefix }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-blue-600 dark:text-blue-400">{{ $preview }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $config->last_number }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ __('common.running_number_reset_' . $config->reset_mode) }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($config->is_active)
                                <span class="badge-green">{{ __('common.active') }}</span>
                            @else
                                <span class="badge-gray">{{ __('common.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute right-0 bottom-full mb-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50">
                                    <a href="{{ route('settings.running-numbers.edit', $config) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('settings.running-numbers.reset', $config) }}"
                                          onsubmit="return confirm('{{ __('common.running_number_reset_confirm') }}')" novalidate>
                                        @csrf
                                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                                            {{ __('common.running_number_reset_counter') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('settings.running-numbers.destroy', $config) }}"
                                          onsubmit="return confirm('{{ __('common.delete_confirm') }}')" novalidate>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('common.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
