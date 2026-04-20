@extends('layouts.app')

@section('title', __('common.activity_history'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.settings')],
        ['label' => __('common.activity_history')],
    ]" />
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.activity_history') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.activity_history_desc') }}</p>
    </div>

    <div class="card p-8 text-center">
        <p class="text-slate-500 dark:text-slate-400">{{ __('common.activity_history_placeholder') }}</p>
    </div>
@endsection
