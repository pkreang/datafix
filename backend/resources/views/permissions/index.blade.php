@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">All Permissions</h2>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ $total }} total</span>
            <a href="#" onclick="alert('Coming soon')" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Permission
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($grouped as $module => $perms)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3 capitalize">{{ str_replace('_', ' ', $module) }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($perms as $perm)
                        @php
                            $action = $perm['action'] ?? '';
                            $colors = match($action) {
                                'create' => 'bg-green-100 text-green-800',
                                'read' => 'bg-blue-100 text-blue-800',
                                'update' => 'bg-yellow-100 text-yellow-800',
                                'delete' => 'bg-red-100 text-red-800',
                                'export' => 'bg-purple-100 text-purple-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors }}">
                            {{ $action }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @if (empty($grouped))
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-500">
            No permissions found.
        </div>
    @endif
@endsection
