@extends('layouts.app')

@section('title', __('common.my_repair_jobs'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.repair_request'), 'url' => route('repair-requests.index')],
        ['label' => __('common.my_repair_jobs')],
    ]" />
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.my_repair_jobs') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.my_repair_jobs_desc') }}</p>
    </div>

    <div class="card p-8 text-center">
        <p class="text-slate-500 dark:text-slate-400">{{ __('common.coming_soon') }}</p>
    </div>
@endsection
