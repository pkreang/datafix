@extends('layouts.app')

@section('title', __('common.equipment_locations'))

@section('content')
<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.equipment_locations') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.equipment_locations_desc') }}</p>
    </div>

    @if ($locations->isEmpty())
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
            <p class="text-gray-500 dark:text-gray-400">{{ __('common.no_equipment_locations') }}</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($locations as $location)
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 overflow-visible">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $location->name }}
                            <span class="text-xs font-normal text-gray-400 dark:text-gray-500 ml-1">{{ $location->code }}</span>
                        </h3>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @if ($location->building)
                                <span>{{ __('common.building') }}: {{ $location->building }}</span>
                            @endif
                            @if ($location->floor)
                                <span>{{ __('common.floor') }}: {{ $location->floor }}</span>
                            @endif
                            @if ($location->zone)
                                <span>{{ __('common.zone') }}: {{ $location->zone }}</span>
                            @endif
                        </div>
                    </div>

                    @if ($location->equipment->isNotEmpty())
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/60">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.name') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.code') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.category') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('common.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($location->equipment as $item)
                                    <tr class="hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $item->code }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $item->category->name ?? '—' }}</td>
                                        <td class="px-4 py-2">
                                            @switch($item->status)
                                                @case('active')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">{{ __('common.status_active') }}</span>
                                                    @break
                                                @case('inactive')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">{{ __('common.status_inactive') }}</span>
                                                    @break
                                                @case('under_maintenance')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">{{ __('common.status_under_maintenance') }}</span>
                                                    @break
                                                @case('decommissioned')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 dark:bg-gray-600/30 text-gray-600 dark:text-gray-400">{{ __('common.status_decommissioned') }}</span>
                                                    @break
                                            @endswitch
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ __('common.no_equipment_found') }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
