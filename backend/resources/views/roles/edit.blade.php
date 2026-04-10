@extends('layouts.app')

@section('title', __('common.edit_role') . ': ' . ($role['name'] ?? ''))

@section('content')
    <div class="max-w-4xl">
        <div class="flex items-center justify-between gap-4 mb-6">
            <nav class="text-sm text-slate-500 dark:text-slate-400">
                <span>{{ __('common.settings') }}</span>
                <span class="mx-1">/</span>
                <a href="{{ route('roles.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('common.roles') }}</a>
                <span class="mx-1">/</span>
                <span class="text-slate-700 dark:text-slate-300">{{ __('common.edit_role') }}</span>
            </nav>
            <a href="{{ route('roles.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 shrink-0">&larr; {{ __('common.back') }}</a>
        </div>

        @if ($errors->any())
            <div class="alert-error mb-4">
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first() }}</p>
            </div>
        @endif

        @php
            $assignedIds = collect($role['permissions'] ?? [])->pluck('id')->toArray();
        @endphp

        <form method="POST" action="{{ route('roles.update', $role['id']) }}" class="card p-6 space-y-6" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="form-label">{{ __('common.role_name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $role['name'] ?? '') }}" required
                       class="form-input max-w-md">
            </div>

            <div>
                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">{{ __('common.permissions_title') }}</h3>
                <div class="space-y-4">
                    @foreach ($grouped as $module => $perms)
                        <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4" x-data="{ expanded: true }">
                            <button type="button" @click="expanded = !expanded" class="flex items-center justify-between w-full text-left">
                                <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ \App\Support\PermissionDisplay::module($module) }}</span>
                                <svg :class="{ 'rotate-180': expanded }" class="w-4 h-4 text-slate-400 dark:text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="expanded" class="mt-3 flex flex-wrap gap-3">
                                @foreach ($perms as $perm)
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm['id'] }}"
                                               {{ in_array($perm['id'], old('permissions', $assignedIds)) ? 'checked' : '' }}
                                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        {{ \App\Support\PermissionDisplay::label($perm['name'] ?? '') }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('roles.index') }}" class="btn-secondary">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit" class="btn-primary">
                    {{ __('common.save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
