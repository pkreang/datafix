@extends('layouts.app')

@section('title', __('common.spare_parts_stock'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.spare_parts_stock') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.spare_parts_stock_desc') }}</p>
    </div>

    <div class="table-wrapper p-5">
        <form method="GET" action="{{ route('spare-parts.stock') }}" class="mb-4 flex flex-wrap items-end gap-2">
            <div>
                <label class="form-label">{{ __('common.search') }}</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="{{ __('common.search_spare_parts') }}"
                       class="form-input">
            </div>
            <button class="btn-primary">{{ __('common.search') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="table-header">{{ __('common.code') }}</th>
                        <th class="table-header">{{ __('common.name') }}</th>
                        <th class="table-header">{{ __('common.category') }}</th>
                        <th class="table-header">{{ __('common.unit') }}</th>
                        <th class="table-header text-right">{{ __('common.current_stock') }}</th>
                        <th class="table-header text-right">{{ __('common.min_stock') }}</th>
                        <th class="table-header text-right">{{ __('common.unit_cost') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($parts as $part)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-900 dark:text-slate-100">
                            <td class="px-3 py-2 font-medium">{{ $part->code }}</td>
                            <td class="px-3 py-2">{{ $part->name }}</td>
                            <td class="px-3 py-2 text-slate-500 dark:text-slate-400">{{ $part->category?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $part->unit }}</td>
                            <td class="px-3 py-2 text-right {{ $part->current_stock <= $part->min_stock ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                {{ number_format($part->current_stock, 0) }}
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format($part->min_stock, 0) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($part->unit_cost, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-4 text-center text-slate-500 dark:text-slate-400">{{ __('common.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $parts->links() }}
        </div>
    </div>
@endsection
