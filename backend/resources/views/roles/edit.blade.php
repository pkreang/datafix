@extends('layouts.app')

@section('title', __('common.edit_role') . ': ' . ($role['name'] ?? ''))

@section('content')
    <div class="max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.edit_role') }}</h2>
            <a href="{{ route('roles.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first() }}</p>
            </div>
        @endif

        @php
            $assignedIds = collect($role['permissions'] ?? [])->pluck('id')->toArray();
        @endphp

        <form method="POST" action="{{ route('roles.update', $role['id']) }}" class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.role_name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $role['name'] ?? '') }}" required
                       class="w-full max-w-md px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('common.permissions_title') }}</h3>
                <div class="space-y-4">
                    @foreach ($grouped as $module => $perms)
                        <div class="border border-gray-200 rounded-lg p-4" x-data="{ expanded: true }">
                            <button type="button" @click="expanded = !expanded" class="flex items-center justify-between w-full text-left">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 capitalize">{{ str_replace('_', ' ', $module) }}</span>
                                <svg :class="{ 'rotate-180': expanded }" class="w-4 h-4 text-gray-400 dark:text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="expanded" class="mt-3 flex flex-wrap gap-3">
                                @foreach ($perms as $perm)
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm['id'] }}"
                                               {{ in_array($perm['id'], old('permissions', $assignedIds)) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        {{ $perm['action'] ?? $perm['name'] ?? '' }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    {{ __('common.update_role') }}
                </button>
            </div>
        </form>
    </div>
@endsection
