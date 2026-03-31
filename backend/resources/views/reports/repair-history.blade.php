@extends('layouts.app')

@section('title', __('common.repair_history_report'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.repair_history_report') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.repair_history_report_desc') }}</p>
    </div>

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ __('common.coming_soon') }}</p>
    </div>
@endsection
