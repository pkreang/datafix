@extends('layouts.app')

@section('title', __('common.equipment_locations'))

@section('content')
<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.equipment_locations') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.equipment_locations_desc') }}</p>
    </div>

    @if ($locations->isEmpty())
        <div class="card p-8 text-center">
            <p class="text-slate-500 dark:text-slate-400">{{ __('common.no_equipment_locations') }}</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($locations as $location)
                <div class="table-wrapper">
                    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $location->name }}
                            <span class="text-xs font-normal text-slate-400 dark:text-slate-500 ml-1">{{ $location->code }}</span>
                        </h3>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-slate-500 dark:text-slate-400">
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
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-800/60">
                                <tr>
                                    <th class="table-header">{{ __('common.name') }}</th>
                                    <th class="table-header">{{ __('common.code') }}</th>
                                    <th class="table-header">{{ __('common.category') }}</th>
                                    <th class="table-header">{{ __('common.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach ($location->equipment as $item)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                                        <td class="px-4 py-2 text-sm text-slate-900 dark:text-slate-100">{{ $item->name }}</td>
                                        <td class="px-4 py-2 text-sm text-slate-500 dark:text-slate-400">{{ $item->code }}</td>
                                        <td class="px-4 py-2 text-sm text-slate-500 dark:text-slate-400">{{ $item->category->name ?? '—' }}</td>
                                        <td class="px-4 py-2">
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ __('common.no_equipment_found') }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
