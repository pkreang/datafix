@extends('layouts.app')

@section('title', 'Edit Dashboard')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.settings')],
        ['label' => __('common.reports'), 'url' => route('settings.dashboards.index')],
        ['label' => __('common.edit')],
    ]" />
@endsection

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Edit Dashboard: {{ $dashboard->name }}</h2>
        <a href="{{ route('settings.dashboards.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
    </div>
    <div class="card p-6">
        @include('settings.dashboards._form')
    </div>
@endsection
