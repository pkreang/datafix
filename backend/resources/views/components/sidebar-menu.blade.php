@props(['menus'])

@foreach($menus as $menu)
    @if($menu->route === null && $menu->children->isNotEmpty())
        {{-- Group with submenu (expand / collapse) --}}
        <div class="pt-3"
             x-data="{
                 open: localStorage.getItem('nav_menu_{{ $menu->id }}') !== null
                       ? localStorage.getItem('nav_menu_{{ $menu->id }}') === '1'
                       : {{ $menu->hasActiveChild() ? 'true' : 'false' }}
             }"
             x-effect="localStorage.setItem('nav_menu_{{ $menu->id }}', open ? '1' : '0')">

            <button @click="open = !open" type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-500/50 transition-colors">
                <span class="flex items-center gap-3">
                    <x-nav-icon :name="$menu->icon" class="w-5 h-5 text-blue-200" />
                    <span class="text-sm">{{ $menu->label }}</span>
                </span>
                <svg :class="open && 'rotate-180'"
                     class="w-4 h-4 text-blue-300 transition-transform duration-200"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 x-cloak
                 class="ml-5 mt-1 space-y-0.5 border-l border-blue-400/30 pl-3">

                @foreach($menu->children as $child)
                    <a href="{{ $child->route }}" @click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-1.5 rounded-lg text-sm transition-colors {{ $child->isActive() ? 'bg-blue-500/50 text-white font-medium' : 'text-blue-200 hover:bg-blue-500/30 hover:text-blue-100' }}">
                        <x-nav-icon :name="$child->icon" class="w-4 h-4" />
                        {{ $child->label }}
                    </a>
                @endforeach
            </div>
        </div>

    @else
        {{-- Single menu item --}}
        <a href="{{ $menu->route }}" @click="sidebarOpen = false"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-blue-100 hover:bg-blue-500/50 {{ $menu->isActive() ? 'bg-blue-500/50 text-white font-medium' : '' }}">
            <x-nav-icon :name="$menu->icon" class="w-5 h-5 text-blue-200" />
            {{ $menu->label }}
        </a>
    @endif
@endforeach
