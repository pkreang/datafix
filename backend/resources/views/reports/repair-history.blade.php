@extends('layouts.app')

@section('title', __('common.repair_history_report'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.reports'), 'url' => route('reports.index')],
        ['label' => __('common.repair_history_report')],
    ]" />
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.repair_history_report') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.repair_history_report_desc') }}</p>
    </div>

    <div class="card p-8 text-center">
        <p class="text-slate-500 dark:text-slate-400">{{ __('common.coming_soon') }}</p>
    </div>
@endsection
