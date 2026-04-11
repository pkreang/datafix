@extends('layouts.app')

@section('title', 'Create Dashboard')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Create Dashboard</h2>
        <a href="{{ route('settings.dashboards.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
    </div>
    <div class="card p-6">
        @include('settings.dashboards._form')
    </div>
@endsection
