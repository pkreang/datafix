@props(['menus'])

@foreach($menus as $menu)
    @if($menu->route === null && $menu->children->isNotEmpty())
        {{-- Group with submenu (expand / collapse) --}}
        <div class="pt-3"
             x-data="{
                 open: {{ $menu->hasActiveChild() ? 'true' : 'false' }}
                       || (localStorage.getItem('nav_menu_{{ $menu->id }}') === '1')
             }"
             x-effect="localStorage.setItem('nav_menu_{{ $menu->id }}', open ? '1' : '0')">

            <button @click="open = !open" type="button"
                    class="w-full flex items-center rounded-lg px-3 py-2 text-blue-100 hover:bg-blue-500/50 transition-colors"
                    :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <span class="flex items-center gap-3 min-w-0" :class="sidebarCollapsed ? 'w-full justify-center' : ''">
                    <x-nav-icon :name="$menu->icon" class="w-5 h-5 shrink-0 text-blue-200" />
                    <span class="text-sm font-medium truncate" x-show="!sidebarCollapsed" x-cloak>{{ $menu->translated_label }}</span>
                </span>
                <svg x-show="!sidebarCollapsed"
                     :class="open && 'rotate-180'"
                     class="w-4 h-4 text-blue-300 transition-transform duration-200"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open && !sidebarCollapsed"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 x-cloak
                 class="ml-5 mt-1 space-y-0.5 border-l border-blue-400/30 pl-3">

                @php
                    $activeChild = $menu->children->filter(fn ($c) => $c->isActive())->sortByDesc(fn ($c) => strlen($c->route ?? ''))->first();
                @endphp
                @foreach($menu->children as $child)
                    @php $childActive = $activeChild && $child->id === $activeChild->id; @endphp
                    <a href="{{ $child->route }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $childActive ? 'bg-blue-500/50 text-white font-semibold' : 'text-blue-200 hover:bg-blue-500/30 hover:text-blue-100' }}">
                        <x-nav-icon :name="$child->icon" class="w-4 h-4" />
                        <span x-show="!sidebarCollapsed" x-cloak>{{ $child->translated_label }}</span>
                    </a>
                @endforeach
            </div>
        </div>

    @else
        {{-- Single menu item --}}
        @php $menuActive = $menu->isActive(); @endphp
        <a href="{{ $menu->route }}" @click="sidebarOpen = false"
           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium {{ $menuActive ? 'bg-blue-500/50 text-white font-semibold' : 'text-blue-100 hover:bg-blue-500/50' }}"
           :class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
            <x-nav-icon :name="$menu->icon" class="w-5 h-5 shrink-0 text-blue-200" />
            <span x-show="!sidebarCollapsed" x-cloak>{{ $menu->translated_label }}</span>
        </a>
    @endif
@endforeach
