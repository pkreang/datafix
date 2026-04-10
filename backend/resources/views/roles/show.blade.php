@extends('layouts.app')

@section('title', 'Role: ' . ($role['name'] ?? ''))

@section('content')
    <div class="max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="page-title">{{ $role['name'] ?? '' }}</h2>
            <a href="{{ route('roles.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            <div class="card p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('common.permissions') }}</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $role['permissions_count'] ?? 0 }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('common.users') }}</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $role['users_count'] ?? 0 }}</p>
            </div>
        </div>

        @if (!empty($role['permissions_by_module']))
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ __('common.permissions_by_module') }}</h3>
                <div class="space-y-4">
                    @foreach ($role['permissions_by_module'] as $module => $perms)
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">{{ \App\Support\PermissionDisplay::module($module) }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($perms as $perm)
                                    <span class="badge-blue" title="{{ $perm['name'] ?? '' }}">{{ \App\Support\PermissionDisplay::label($perm['name'] ?? '') }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
