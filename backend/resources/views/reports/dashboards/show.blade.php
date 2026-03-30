@extends('layouts.app')

@section('title', $dashboard->name)

@push('scripts')
<meta name="api-token" content="{{ $apiToken ?? '' }}">
@endpush

@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $dashboard->name }}</h2>
        @if($dashboard->description)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $dashboard->description }}</p>
        @endif
    </div>
    <a href="{{ route('reports.index') }}"
       class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back
    </a>
</div>

{{-- Global filter bar --}}
<div x-data="{}"
     class="mb-6 flex flex-wrap items-end gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
    <div class="flex flex-col gap-1">
        <label for="filter-date-from" class="text-xs font-medium text-gray-500 dark:text-gray-400">Date From</label>
        <input type="date" id="filter-date-from"
               class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="flex flex-col gap-1">
        <label for="filter-date-to" class="text-xs font-medium text-gray-500 dark:text-gray-400">Date To</label>
        <input type="date" id="filter-date-to"
               class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="flex flex-col gap-1">
        <label for="filter-department" class="text-xs font-medium text-gray-500 dark:text-gray-400">Department</label>
        <select id="filter-department"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="button"
            onclick="document.querySelectorAll('[data-dashboard-widget]').forEach(el => { Alpine.$data(el)?.loadData?.(); })"
            class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors">
        Refresh
    </button>
</div>

{{-- Widget grid --}}
<div class="grid gap-4" style="grid-template-columns: repeat({{ $dashboard->layout_columns ?? 2 }}, minmax(0, 1fr))">
    @foreach($dashboard->widgets as $widget)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4"
             style="grid-column: span {{ $widget->col_span ?: 1 }}"
             data-dashboard-widget
             x-data="dashboardWidget({{ $widget->id }}, {{ $dashboard->id }}, '{{ $widget->widget_type }}')"
             x-init="loadData()">

            {{-- Widget header --}}
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ $widget->title }}</h3>

            {{-- Loading state --}}
            <div x-show="loading" class="flex items-center justify-center h-24 text-gray-400">
                <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            {{-- Error state --}}
            <div x-show="error && !loading" class="text-sm text-red-500 p-2" x-text="error"></div>

            {{-- Metric widget --}}
            <div x-show="!loading && !error && widgetType === 'metric'">
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100" x-text="data.value ?? '-'"></p>
                <template x-if="data.label">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="data.label"></p>
                </template>
            </div>

            {{-- Chart widget --}}
            <div x-show="!loading && !error && widgetType === 'chart'" style="position: relative; height: 200px;">
                <canvas :id="`chart-${widgetId}`"
                        data-chart-type="{{ $widget->config['chart_type'] ?? 'bar' }}"
                        height="200"></canvas>
            </div>

            {{-- Table widget --}}
            <div x-show="!loading && !error && widgetType === 'table'" class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <template x-for="label in (data.column_labels || [])">
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider" x-text="label"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="(row, ridx) in (data.rows || [])" :key="ridx">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <template x-for="col in (data.columns || [])" :key="col">
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300 whitespace-nowrap" x-text="row[col] ?? '-'"></td>
                                </template>
                            </tr>
                        </template>
                        <template x-if="!data.rows || data.rows.length === 0">
                            <tr>
                                <td :colspan="(data.columns || []).length || 1" class="px-3 py-4 text-center text-gray-400">No data</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                {{-- Pagination --}}
                <div x-show="data.pagination && data.pagination.last_page > 1"
                     class="flex items-center justify-between mt-3 text-sm text-gray-500 dark:text-gray-400">
                    <span x-text="`Page ${data.pagination?.current_page} of ${data.pagination?.last_page}`"></span>
                    <div class="flex gap-2">
                        <button @click="prevPage()"
                                :disabled="data.pagination?.current_page <= 1"
                                class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-40 transition-colors">
                            Prev
                        </button>
                        <button @click="nextPage()"
                                :disabled="data.pagination?.current_page >= data.pagination?.last_page"
                                class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-40 transition-colors">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($dashboard->widgets->isEmpty())
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">No widgets have been added to this dashboard yet.</p>
    </div>
@endif

@endsection
