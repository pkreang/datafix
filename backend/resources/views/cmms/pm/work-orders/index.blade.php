@extends('layouts.app')

@section('title', __('common.pm_work_orders'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.cmms'), 'url' => null],
        ['label' => __('common.pm_work_orders')],
    ]" />
@endsection

@php
    $statusClasses = [
        'due' => 'badge-blue',
        'in_progress' => 'badge-yellow',
        'overdue' => 'badge-red',
        'done' => 'badge-green',
        'skipped' => 'badge-gray',
        'cancelled' => 'badge-gray',
    ];
@endphp

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.pm_work_orders') }}</h2>
    </div>

    @if(session('success'))
        <div class="alert-success mb-4"><p class="text-sm">{{ session('success') }}</p></div>
    @endif

    <x-filter-bar :action="route('cmms.pm.work-orders.index')">
        <input name="search" value="{{ request('search') }}" placeholder="{{ __('common.search') }}" class="form-input w-auto" />
        <select name="status" onchange="this.form.submit()" class="form-input w-auto">
            <option value="">{{ __('common.all_statuses') }}</option>
            @foreach($statuses as $st)
                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ __('common.pm_wo_status_' . $st) }}</option>
            @endforeach
        </select>
        <select name="equipment_id" onchange="this.form.submit()" class="form-input w-auto">
            <option value="">{{ __('common.all_equipment') }}</option>
            @foreach($equipmentList as $eq)
                <option value="{{ $eq->id }}" {{ request('equipment_id') == $eq->id ? 'selected' : '' }}>{{ $eq->code }} — {{ $eq->name }}</option>
            @endforeach
        </select>
    </x-filter-bar>

    <x-data-table
        :columns="[
            ['key' => 'code', 'label' => __('common.pm_wo_code')],
            ['key' => 'equipment', 'label' => __('common.equipment')],
            ['key' => 'plan', 'label' => __('common.pm_plan_name')],
            ['key' => 'due_date', 'label' => __('common.pm_due_date')],
            ['key' => 'status', 'label' => __('common.status')],
            ['key' => 'assignee', 'label' => __('common.pm_wo_assignee')],
            ['key' => 'actions', 'label' => __('common.actions'), 'class' => 'text-right'],
        ]"
        :rows="$workOrders"
        :disable-pagination="true"
        :empty-message="__('common.no_pm_work_orders_found')"
    >
        @foreach($workOrders as $wo)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                <td class="px-4 py-2 whitespace-nowrap">
                    <a href="{{ route('cmms.pm.work-orders.show', $wo) }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ $wo->code }}</a>
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $wo->equipment?->code ?? '—' }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $wo->equipment?->name }}</p>
                </td>
                <td class="table-sub">{{ $wo->plan?->name ?? '—' }}</td>
                <td class="table-sub">
                    {{ $wo->due_date?->format('Y-m-d') ?? '—' }}
                    @if($wo->status === 'overdue' && $wo->due_date)
                        <p class="text-xs text-red-600 dark:text-red-400">{{ $wo->due_date->diffForHumans() }}</p>
                    @endif
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <span class="{{ $statusClasses[$wo->status] ?? 'badge-gray' }}">{{ __('common.pm_wo_status_' . $wo->status) }}</span>
                </td>
                <td class="table-sub">{{ $wo->assignee?->first_name ?? '—' }} {{ $wo->assignee?->last_name }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-right">
                    <a href="{{ route('cmms.pm.work-orders.show', $wo) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('common.open') }}</a>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    <x-per-page-footer :paginator="$workOrders" :perPage="$perPage" id="pm-wo-pagination" />
</div>
@endsection
