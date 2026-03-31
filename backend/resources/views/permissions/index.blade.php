@extends('layouts.app')

@section('title', __('common.permissions'))

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.permissions_title') }}</h2>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $total }} {{ __('common.total') }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($grouped as $module => $perms)
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3 capitalize">{{ str_replace('_', ' ', $module) }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($perms as $perm)
                        @php
                            $action = $perm['action'] ?? '';
                            $colors = match($action) {
                                'create' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                'read' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                                'update' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                'delete' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                'export' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
                                default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $colors }}">
                            {{ $action }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @if (empty($grouped))
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-12 text-center text-gray-500 dark:text-gray-400">
            {{ __('common.no_permissions_found') }}
        </div>
    @endif
@endsection
