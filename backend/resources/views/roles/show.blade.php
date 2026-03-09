@extends('layouts.app')

@section('title', 'Role: ' . ($role['name'] ?? ''))

@section('content')
    <div class="max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-900">{{ $role['name'] ?? '' }}</h2>
            <a href="{{ route('roles.index') }}" class="text-sm text-blue-600 hover:text-blue-500">&larr; Back to Roles</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-sm text-gray-500">Permissions</p>
                <p class="text-2xl font-bold text-gray-900">{{ $role['permissions_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-sm text-gray-500">Users</p>
                <p class="text-2xl font-bold text-gray-900">{{ $role['users_count'] ?? 0 }}</p>
            </div>
        </div>

        @if (!empty($role['permissions_by_module']))
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Permissions by Module</h3>
                <div class="space-y-4">
                    @foreach ($role['permissions_by_module'] as $module => $perms)
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2 capitalize">{{ str_replace('_', ' ', $module) }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($perms as $perm)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $perm['action'] ?? $perm['name'] ?? '' }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
