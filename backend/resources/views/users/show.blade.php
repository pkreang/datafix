@extends('layouts.app')
@section('title', __('common.user_details'))
@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.user_and_access'), 'url' => route('users.index')],
        ['label' => isset($user) ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: __('common.user_details') : __('common.user_details')],
    ]" />
@endsection
@section('content')
    <div class="max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.user_details') }}</h2>
            <a href="{{ route('users.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
        </div>
        <div class="card p-8 text-center">
            <p class="text-slate-500 dark:text-slate-400">{{ __('common.coming_soon') }}</p>
        </div>
    </div>
@endsection
