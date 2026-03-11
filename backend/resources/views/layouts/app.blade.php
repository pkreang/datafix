@php
    $layoutUser = session('user') ?? [];
    $layoutUserName = trim(($layoutUser['first_name'] ?? '') . ' ' . ($layoutUser['last_name'] ?? '')) ?: ($layoutUser['name'] ?? 'User');
    $layoutUserAvatar = $layoutUser['avatar'] ?? ('https://ui-avatars.com/api/?name=' . urlencode($layoutUserName ?: 'U') . '&background=0ea5e9&color=fff');
    $layoutUserInitials = strtoupper(mb_substr($layoutUser['first_name'] ?? '', 0, 1) . mb_substr($layoutUser['last_name'] ?? '', 0, 1)) ?: strtoupper(mb_substr($layoutUserName, 0, 2)) ?: 'U';
    $layoutAvatarColors = ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EF4444'];
    $layoutAvatarBg = $layoutAvatarColors[abs(crc32($layoutUserName ?? 'U')) % 5];
@endphp
<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <script>
        (function() {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>DATA FIX - @yield('title', __('common.dashboard'))</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">

    @stack('scripts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-20 bg-gray-900/50 lg:hidden"
             x-cloak
             aria-hidden="true"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-blue-600 flex flex-col transform transition-transform duration-200 ease-in-out -translate-x-full lg:translate-x-0"
               :class="{ 'translate-x-0': sidebarOpen }">
            <div class="h-16 flex items-center justify-between px-6 border-b border-blue-500/40">
                <a href="{{ route('dashboard') }}" class="text-base font-semibold text-white tracking-wide">
                    DATA FIX
                </a>
                <button @click="sidebarOpen = false" type="button" class="lg:hidden p-2 -mr-2 text-blue-200 hover:text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <x-sidebar-menu :menus="$navigationMenus ?? collect()" />
            </nav>

            <div class="p-4 border-t border-blue-500/40">
                <div class="flex items-center gap-3">
                    <img src="{{ $layoutUserAvatar }}" alt="" class="w-9 h-9 rounded-full object-cover ring-2 ring-blue-400/50">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-white truncate">{{ $layoutUserName }}</p>
                        <p class="text-xs text-blue-200 truncate">{{ $layoutUser['email'] ?? '' }}</p>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">v{{ config('app.version') }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 min-w-0 pl-0 lg:pl-64 flex flex-col gap-4 bg-white dark:bg-gray-900">
            <header class="sticky top-0 z-20 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between gap-4 px-4 sm:px-8">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarOpen = true" type="button" class="lg:hidden shrink-0 p-2 -ml-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg focus:outline-none" aria-label="Open menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 truncate">@yield('title', __('common.dashboard'))</h1>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="$store.theme.toggle()"
                            class="p-1.5 rounded-lg transition-colors
                                   text-gray-500 dark:text-gray-400
                                   hover:bg-gray-100 dark:hover:bg-gray-700"
                            aria-label="Toggle dark mode">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path class="block dark:hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            <path class="hidden dark:block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>

                    <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden text-xs">
                        <a href="{{ route('lang.switch', 'th') }}"
                           class="px-2.5 py-1 font-medium transition-colors
                                  {{ app()->getLocale() === 'th' ? 'bg-blue-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            TH
                        </a>
                        <a href="{{ route('lang.switch', 'en') }}"
                           class="px-2.5 py-1 font-medium transition-colors border-l border-gray-200 dark:border-gray-700
                                  {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            EN
                        </a>
                    </div>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                                class="flex items-center gap-1.5 p-1 rounded-lg
                                       hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            @if($layoutUser['avatar'] ?? null)
                                <img src="{{ $layoutUserAvatar }}" alt="" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full flex items-center justify-center
                                            text-xs font-bold text-white"
                                     style="background: {{ $layoutAvatarBg }}">
                                    {{ $layoutUserInitials }}
                                </div>
                            @endif
                            <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-10 w-52 z-50
                                    bg-white dark:bg-gray-800
                                    border border-gray-200 dark:border-gray-700
                                    rounded-xl shadow-lg py-1">

                            <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $layoutUserName }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-400 truncate">{{ $layoutUser['email'] ?? '' }}</p>
                            </div>

                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm
                                      text-gray-700 dark:text-gray-300
                                      hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('common.my_profile') }}
                            </a>

                            <a href="{{ route('profile.password') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm
                                      text-gray-700 dark:text-gray-300
                                      hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ __('common.change_password') }}
                            </a>

                            <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-2.5 px-3 py-2 text-sm
                                               text-red-600 dark:text-red-400
                                               hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ __('common.sign_out') }}
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            </header>

            <main class="p-6 overflow-auto flex-1">
                @yield('content')
            </main>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
