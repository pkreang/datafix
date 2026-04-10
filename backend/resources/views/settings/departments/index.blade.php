@extends('layouts.app')

@section('title', __('common.departments'))

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('common.department_list') }}</h2>
        <a href="{{ route('settings.departments.create') }}" class="btn-primary">
            {{ __('common.add') }} {{ __('common.departments') }}
        </a>
    </div>

    @if (session('error'))
        <div class="alert-error mb-4">
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    @endif

    <div class="table-wrapper">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    <th class="table-header">{{ __('common.code') }}</th>
                    <th class="table-header">{{ __('common.name') }}</th>
                    <th class="table-header">{{ __('common.remark') }}</th>
                    <th class="table-header text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($departments as $department)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $department->code }}</td>
                        <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $department->name }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $department->description ?: '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open" type="button"
                                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none">
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
                                     class="absolute right-0 bottom-full mb-2 w-44 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50">
                                    <a href="{{ route('settings.departments.edit', $department) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('settings.departments.destroy', $department) }}"
                                          onsubmit="return confirm('{{ __('common.delete_confirm_msg', ['name' => $department->name]) }}')" novalidate>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-slate-200 dark:hover:bg-slate-700">
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
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('common.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
