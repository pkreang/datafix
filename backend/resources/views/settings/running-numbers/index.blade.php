@extends('layouts.app')

@section('title', __('common.running_numbers'))

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.running_numbers') }}</h2>
        <a href="{{ route('settings.running-numbers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
            {{ __('common.add') }} {{ __('common.running_numbers') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-visible">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/80">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.document_type') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.running_number_prefix') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.running_number_preview') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.running_number_current') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.running_number_reset_mode') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.status') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($configs as $config)
                    @php
                        $now = now();
                        $preview = $config->prefix;
                        if ($config->include_year) $preview .= $now->format('Y');
                        if ($config->include_month) $preview .= $now->format('m');
                        $preview .= '-' . str_pad($config->last_number + 1, $config->digit_count, '0', STR_PAD_LEFT);
                    @endphp
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $config->document_type }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $config->prefix }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-blue-600 dark:text-blue-400">{{ $preview }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $config->last_number }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ __('common.running_number_reset_' . $config->reset_mode) }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($config->is_active)
                                <span class="text-green-600 dark:text-green-400">{{ __('common.active') }}</span>
                            @else
                                <span class="text-gray-500">{{ __('common.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                        class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                                    <a href="{{ route('settings.running-numbers.edit', $config) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('settings.running-numbers.reset', $config) }}"
                                          onsubmit="return confirm('{{ __('common.running_number_reset_confirm') }}')">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-gray-200 dark:hover:bg-gray-700">
                                            {{ __('common.running_number_reset_counter') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('settings.running-numbers.destroy', $config) }}"
                                          onsubmit="return confirm('{{ __('common.delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-700">
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('common.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
