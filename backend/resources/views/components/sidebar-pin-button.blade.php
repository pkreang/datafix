@props(['menuId', 'size' => 'md'])

@php
    $idStr = (string) $menuId;
    $iconClass = $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4';
@endphp

<button type="button"
        x-show="!sidebarCollapsed"
        @click.stop.prevent="$store.pinnedMenus.toggle('{{ $idStr }}')"
        class="absolute right-1.5 top-1/2 -translate-y-1/2 p-1 rounded transition-colors focus:outline-none focus:ring-2 focus:ring-white/30"
        :class="$store.pinnedMenus.has('{{ $idStr }}')
            ? 'text-yellow-300 opacity-100'
            : 'text-blue-200/50 opacity-0 group-hover:opacity-100 hover:text-yellow-200'"
        :aria-pressed="$store.pinnedMenus.has('{{ $idStr }}')"
        :title="$store.pinnedMenus.has('{{ $idStr }}') ? @js(__('common.unpin_menu')) : @js(__('common.pin_menu'))"
        :aria-label="$store.pinnedMenus.has('{{ $idStr }}') ? @js(__('common.unpin_menu')) : @js(__('common.pin_menu'))"
        x-cloak>
    <svg x-show="$store.pinnedMenus.has('{{ $idStr }}')" class="{{ $iconClass }}" fill="currentColor" viewBox="0 0 24 24">
        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
    </svg>
    <svg x-show="!$store.pinnedMenus.has('{{ $idStr }}')" class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
    </svg>
</button>
