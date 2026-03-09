@extends('layouts.app')

@section('title', 'Navigation Menu')

@section('content')
<div class="w-full" x-data="navigationIndex()">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Navigation Menu</h2>
            <p class="text-sm text-gray-500 mt-1">Manage sidebar menu items</p>
        </div>
        <a href="{{ route('settings.navigation.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Menu Item
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 w-10"></th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Icon</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Label</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Route</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Permission</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase text-center">Order</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase text-center">Status</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="menu-table-body">
                    @foreach ($rootMenus as $menu)
                        <tr class="border-b border-gray-100 hover:bg-gray-50/50"
                            data-menu-id="{{ $menu->id }}">
                            <td class="px-4 py-3">
                                <span class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600">
                                    <x-nav-icon :name="$menu->icon ?? ''" class="w-4 h-4" />
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $menu->label }}</td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $menu->route ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $menu->permission ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $menu->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                <button @click="toggleActive({{ $menu->id }}, $event)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                                               {{ $menu->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $menu->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('settings.navigation.edit', $menu) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @if ($menu->allChildren->isEmpty())
                                    <form method="POST" action="{{ route('settings.navigation.destroy', $menu) }}"
                                          onsubmit="return confirm('Delete &quot;{{ $menu->label }}&quot;?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Children --}}
                        @foreach ($menu->allChildren as $child)
                        <tr class="border-b border-gray-100 hover:bg-gray-50/50 bg-gray-50/30"
                            data-menu-id="{{ $child->id }}">
                            <td class="px-4 py-3">
                                <span class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 ml-4">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-500 ml-4">
                                    <x-nav-icon :name="$child->icon ?? ''" class="w-3.5 h-3.5" />
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-400 mr-1">└</span>
                                <span class="text-gray-700">{{ $child->label }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $child->route ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $child->permission ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $child->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                <button @click="toggleActive({{ $child->id }}, $event)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                                               {{ $child->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $child->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $child->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('settings.navigation.edit', $child) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('settings.navigation.destroy', $child) }}"
                                          onsubmit="return confirm('Delete &quot;{{ $child->label }}&quot;?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('navigationIndex', () => ({
        init() {
            const tbody = document.getElementById('menu-table-body');
            if (tbody) {
                Sortable.create(tbody, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'bg-blue-50',
                    onEnd: () => {
                        const ids = [...tbody.querySelectorAll('[data-menu-id]')]
                            .map(el => parseInt(el.dataset.menuId));
                        fetch('{{ route("settings.navigation.reorder") }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ ids }),
                        })
                        .then(r => r.json())
                        .then(() => {
                            document.querySelectorAll('[data-menu-id]').forEach((row, i) => {
                                const orderCell = row.querySelector('td:nth-child(6)');
                                if (orderCell) orderCell.textContent = i + 1;
                            });
                        });
                    },
                });
            }
        },

        toggleActive(id, event) {
            const btn = event.currentTarget;
            fetch(`/settings/navigation/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                const dot  = btn.querySelector('span');
                if (data.is_active) {
                    btn.className = btn.className
                        .replace('bg-gray-100', 'bg-green-50')
                        .replace('text-gray-500', 'text-green-700');
                    dot.className = dot.className
                        .replace('bg-gray-400', 'bg-green-500');
                    btn.lastChild.textContent = 'Active';
                } else {
                    btn.className = btn.className
                        .replace('bg-green-50', 'bg-gray-100')
                        .replace('text-green-700', 'text-gray-500');
                    dot.className = dot.className
                        .replace('bg-green-500', 'bg-gray-400');
                    btn.lastChild.textContent = 'Inactive';
                }
            });
        },
    }));
});
</script>
@endsection
