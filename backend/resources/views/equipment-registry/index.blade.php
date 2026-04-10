@extends('layouts.app')

@section('title', __('common.equipment_list'))

@section('content')
<div>
    <div class="flex items-center justify-between mb-2">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.equipment_list') }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('common.total') }}: {{ $equipment->total() }}</p>
        </div>
        <a href="{{ route('equipment-registry.create') }}" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('common.add_equipment') }}
        </a>
    </div>

    {{-- Search & Filters --}}
    <form method="GET" action="{{ route('equipment-registry.index') }}" class="mb-5">
        <div class="flex flex-wrap items-end gap-3">
            <div class="relative w-full max-w-sm">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="{{ __('common.search_equipment') }}"
                       class="form-input pl-10">
            </div>
            <select name="category_id" onchange="this.form.submit()" class="form-input w-auto">
                <option value="">{{ __('common.all_categories') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="location_id" onchange="this.form.submit()" class="form-input w-auto">
                <option value="">{{ __('common.all_locations') }}</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" class="form-input w-auto">
                <option value="">{{ __('common.all_statuses') }}</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('common.status_active') }}</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('common.status_inactive') }}</option>
                <option value="under_maintenance" {{ request('status') == 'under_maintenance' ? 'selected' : '' }}>{{ __('common.status_under_maintenance') }}</option>
                <option value="decommissioned" {{ request('status') == 'decommissioned' ? 'selected' : '' }}>{{ __('common.status_decommissioned') }}</option>
            </select>
        </div>
    </form>

    @if (session('error'))
        <div class="alert-error mb-4">
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    @endif

    @if (session('success'))
        <div class="alert-success mb-4">
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    @endif

    <div class="table-wrapper">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    <th class="table-header">{{ __('common.name') }} / {{ __('common.code') }}</th>
                    <th class="table-header">{{ __('common.serial_number') }}</th>
                    <th class="table-header">{{ __('common.category') }}</th>
                    <th class="table-header">{{ __('common.location') }}</th>
                    <th class="table-header">{{ __('common.status') }}</th>
                    <th class="table-header text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse ($equipment as $item)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                        <td class="px-4 py-2 whitespace-nowrap">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $item->name }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ $item->code }}</p>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $item->serial_number ?? '—' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $item->category->name ?? '—' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $item->location->name ?? '—' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            @switch($item->status)
                                @case('active')
                                    <span class="badge-green">{{ __('common.status_active') }}</span>
                                    @break
                                @case('inactive')
                                    <span class="badge-red">{{ __('common.status_inactive') }}</span>
                                    @break
                                @case('under_maintenance')
                                    <span class="badge-yellow">{{ __('common.status_under_maintenance') }}</span>
                                    @break
                                @case('decommissioned')
                                    <span class="badge-gray">{{ __('common.status_decommissioned') }}</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right">
                            <div class="relative z-10 inline-block text-left" x-data="{ open: false }">
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
                                     class="absolute right-0 bottom-full mb-2 w-40 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-[200]">
                                    <a href="{{ route('equipment-registry.edit', $item) }}"
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        {{ __('common.edit') }}
                                    </a>
                                    <form method="POST" action="{{ route('equipment-registry.destroy', $item) }}" class="block"
                                          onsubmit="return confirm('{{ __('common.are_you_sure') }}')" novalidate>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 text-left">
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
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('common.no_equipment_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($equipment->hasPages())
        <div class="mt-4">
            {{ $equipment->links() }}
        </div>
    @endif
</div>
@endsection
