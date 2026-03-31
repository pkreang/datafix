@extends('layouts.app')

@section('title', __('common.navigation_menu'))

@section('content')
<div class="w-full" x-data="navigationIndex()"
     data-nav-active="{{ __('common.active') }}"
     data-nav-inactive="{{ __('common.inactive') }}">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.navigation_menu') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('common.manage_menu_items') }}</p>
        </div>
        <a href="{{ route('settings.navigation.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('common.add_menu_item') }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 w-10"></th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.menu_field_icon') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.menu_field_label') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.menu_field_route') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.menu_field_permission') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider text-center">{{ __('common.menu_field_order') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider text-center">{{ __('common.status') }}</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider text-right">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="menu-table-body" x-ref="menuTableBody">
                    @foreach ($rootMenus as $menu)
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-150"
                            data-menu-id="{{ $menu->id }}">
                            <td class="px-6 py-3">
                                <span class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600">
                                    <x-nav-icon :name="$menu->icon ?? ''" class="w-4 h-4" />
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $menu->translated_label }}</td>
                            <td class="px-6 py-3 text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $menu->route ?? '—' }}</td>
                            <td class="px-6 py-3 text-xs text-gray-400 dark:text-gray-500">{{ $menu->permission ?? '—' }}</td>
                            <td class="px-6 py-3 text-center text-sm text-gray-500 dark:text-gray-400">{{ $menu->sort_order }}</td>
                            <td class="px-6 py-3 text-center">
                                <button @click="toggleActive({{ $menu->id }}, $event)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                                               {{ $menu->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $menu->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $menu->is_active ? __('common.active') : __('common.inactive') }}
                                </button>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button @click="open = !open" type="button"
                                            class="p-1.5 rounded-lg text-gray-400
                                                   hover:text-gray-600 dark:hover:text-gray-300
                                                   hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         x-cloak
                                         class="absolute right-0 top-8 w-36 z-30
                                                bg-white dark:bg-gray-800
                                                border border-gray-200 dark:border-gray-700
                                                rounded-xl shadow-lg py-1">
                                        <a href="{{ route('settings.navigation.edit', $menu) }}"
                                           class="flex items-center gap-2 px-3 py-2 text-sm
                                                  text-gray-700 dark:text-gray-300
                                                  hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            {{ __('common.edit') }}
                                        </a>
                                        @if ($menu->allChildren->isEmpty())
                                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                        <button @click="open = false;
                                                        $dispatch('open-nav-delete-modal', { id: {{ $menu->id }}, name: {{ json_encode($menu->translated_label ?? '') }} })"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left
                                                       text-red-600 dark:text-red-400
                                                       hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            {{ __('common.delete') }}
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>

                        {{-- Children --}}
                        @foreach ($menu->allChildren as $child)
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 bg-gray-50/30 dark:bg-gray-800/30 transition-colors duration-150"
                            data-menu-id="{{ $child->id }}">
                            <td class="px-6 py-3">
                                <span class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-300 ml-4">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 ml-4">
                                    <x-nav-icon :name="$child->icon ?? ''" class="w-3.5 h-3.5" />
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-gray-400 mr-1">└</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $child->translated_label }}</span>
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $child->route ?? '—' }}</td>
                            <td class="px-6 py-3 text-xs text-gray-400 dark:text-gray-500">{{ $child->permission ?? '—' }}</td>
                            <td class="px-6 py-3 text-center text-sm text-gray-500 dark:text-gray-400">{{ $child->sort_order }}</td>
                            <td class="px-6 py-3 text-center">
                                <button @click="toggleActive({{ $child->id }}, $event)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                                               {{ $child->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $child->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $child->is_active ? __('common.active') : __('common.inactive') }}
                                </button>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button @click="open = !open" type="button"
                                            class="p-1.5 rounded-lg text-gray-400
                                                   hover:text-gray-600 dark:hover:text-gray-300
                                                   hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         x-cloak
                                         class="absolute right-0 top-8 w-36 z-30
                                                bg-white dark:bg-gray-800
                                                border border-gray-200 dark:border-gray-700
                                                rounded-xl shadow-lg py-1">
                                        <a href="{{ route('settings.navigation.edit', $child) }}"
                                           class="flex items-center gap-2 px-3 py-2 text-sm
                                                  text-gray-700 dark:text-gray-300
                                                  hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            {{ __('common.edit') }}
                                        </a>
                                        <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                        <button @click="open = false;
                                                        $dispatch('open-nav-delete-modal', { id: {{ $child->id }}, name: {{ json_encode($child->translated_label ?? '') }} })"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left
                                                       text-red-600 dark:text-red-400
                                                       hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            {{ __('common.delete') }}
                                        </button>
                                    </div>
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
            const tbody = this.$refs.menuTableBody;
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
            const wrapper = document.querySelector('[data-nav-active]');
            const activeTxt = wrapper?.dataset.navActive || 'Active';
            const inactiveTxt = wrapper?.dataset.navInactive || 'Inactive';
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
                    btn.lastChild.textContent = activeTxt;
                } else {
                    btn.className = btn.className
                        .replace('bg-green-50', 'bg-gray-100')
                        .replace('text-green-700', 'text-gray-500');
                    dot.className = dot.className
                        .replace('bg-green-500', 'bg-gray-400');
                    btn.lastChild.textContent = inactiveTxt;
                }
            });
        },
    }));
});
</script>

    {{-- Delete Confirm Modal --}}
    <div x-data="{ show: false, id: null, name: '' }"
         @open-nav-delete-modal.window="show = true; id = $event.detail.id; name = $event.detail.name"
         x-show="show" x-cloak x-transition
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div @click.outside="show = false"
             class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-80 shadow-xl text-center">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                {{ __('common.confirm_delete') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ __('common.delete') }} <strong x-text="name"></strong>?
                <br><span class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.cannot_undo') }}</span>
            </p>
            <div class="flex gap-2 mt-4">
                <button @click="show = false"
                        class="flex-1 px-4 py-2 text-sm font-medium rounded-lg
                               border border-gray-300 dark:border-gray-600
                               text-gray-700 dark:text-gray-300
                               bg-white dark:bg-gray-800
                               hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    {{ __('common.cancel') }}
                </button>
                <form x-bind:action="`{{ url('settings/navigation') }}/${id}`" method="POST" class="flex-1" x-show="id">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium rounded-lg
                                   bg-red-600 hover:bg-red-700 text-white transition-colors">
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
