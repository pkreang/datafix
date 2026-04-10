@extends('layouts.app')
@section('title', __('common.user_details'))
@section('content')
    <div class="max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.user_details') }}</h2>
            <a href="{{ route('users.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
        </div>
        <p class="text-slate-500 dark:text-slate-400">{{ __('common.coming_soon') }}</p>
    </div>
@endsection
