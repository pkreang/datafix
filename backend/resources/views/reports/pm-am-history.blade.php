@extends('layouts.app')

@section('title', __('common.pm_am_history_report'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.pm_am_history_report') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.pm_am_history_report_desc') }}</p>
    </div>

    <div class="card p-8 text-center">
        <p class="text-slate-500 dark:text-slate-400">{{ __('common.coming_soon') }}</p>
    </div>
@endsection
