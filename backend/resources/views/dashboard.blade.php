@extends('layouts.app')

@section('title', __('common.dashboard'))

@section('content')
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.total_users') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.total_roles') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalRoles }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.total_permissions') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalPermissions }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Welcome --}}
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-6">
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ __('common.welcome_back') }}, {{ trim(session('user.first_name', '') . ' ' . session('user.last_name', '')) ?: session('user.name', 'User') }}!</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.welcome_subtitle') }}</p>
    </div>
@endsection
