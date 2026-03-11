@extends('layouts.app')
@section('title', __('common.user_details'))
@section('content')
    <div class="max-w-2xl">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('common.user_details') }}</h2>
        <p class="text-gray-500 dark:text-gray-400">{{ __('common.coming_soon') }}</p>
        <a href="{{ route('users.index') }}" class="mt-4 inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back_to_users') }}</a>
    </div>
@endsection
