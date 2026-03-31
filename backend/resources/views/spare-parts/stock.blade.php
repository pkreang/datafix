@extends('layouts.app')

@section('title', __('common.spare_parts_stock'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.spare_parts_stock') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.spare_parts_stock_desc') }}</p>
    </div>

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <form method="GET" action="{{ route('spare-parts.stock') }}" class="mb-4 flex flex-wrap items-end gap-2">
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">{{ __('common.search') }}</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="{{ __('common.search_spare_parts') }}"
                       class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
            </div>
            <button class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm">{{ __('common.search') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-2">{{ __('common.code') }}</th>
                        <th class="px-3 py-2">{{ __('common.name') }}</th>
                        <th class="px-3 py-2">{{ __('common.category') }}</th>
                        <th class="px-3 py-2">{{ __('common.unit') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('common.current_stock') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('common.min_stock') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('common.unit_cost') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                        <tr class="text-gray-900 dark:text-gray-100">
                            <td class="px-3 py-2 font-medium">{{ $part->code }}</td>
                            <td class="px-3 py-2">{{ $part->name }}</td>
                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $part->category?->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $part->unit }}</td>
                            <td class="px-3 py-2 text-right {{ $part->current_stock <= $part->min_stock ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                {{ number_format($part->current_stock, 0) }}
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format($part->min_stock, 0) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($part->unit_cost, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">{{ __('common.no_data') }}</td>
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
