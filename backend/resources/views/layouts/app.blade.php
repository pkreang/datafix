@php
    $layoutUser = session('user') ?? [];
    $layoutUserName = $layoutUser['name'] ?? 'User';
    $layoutUserAvatar = $layoutUser['avatar'] ?? ('https://ui-avatars.com/api/?name=' . urlencode($layoutUserName ?: 'U') . '&background=0ea5e9&color=fff');
    $perms = session('user_permissions') ?? [];
    $isSuperAdmin = $layoutUser['is_super_admin'] ?? false;
    $can = fn(string $p) => $isSuperAdmin || in_array($p, $perms);
    $hasSettings = $can('user_access.read') || $can('role_access.read') || $can('permission_access.read');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DataPLC') }} - @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
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
        <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 flex flex-col transform transition-transform duration-200 ease-in-out -translate-x-full lg:translate-x-0"
               :class="{ 'translate-x-0': sidebarOpen }">
            {{-- Logo / App name --}}
            <div class="h-16 flex items-center justify-between px-6 border-b border-gray-200">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-900">
                    {{ config('app.name', 'DataPLC') }}
                </a>
                <button @click="sidebarOpen = false" type="button" class="lg:hidden p-2 -mr-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-100 font-medium' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                @if ($hasSettings)
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Settings</p>
                </div>
                @endif

                @if ($can('user_access.read'))
                <a href="{{ route('users.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('users.*') ? 'bg-gray-100 font-medium' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Users
                </a>
                @endif

                @if ($can('role_access.read'))
                <a href="{{ route('roles.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('roles.*') ? 'bg-gray-100 font-medium' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Roles
                </a>
                @endif

                @if ($can('permission_access.read'))
                <a href="{{ route('permissions.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 {{ request()->routeIs('permissions.*') ? 'bg-gray-100 font-medium' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Permissions
                </a>
                @endif
            </nav>

            {{-- Current user --}}
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <img src="{{ $layoutUserAvatar }}" alt="" class="w-9 h-9 rounded-full object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $layoutUserName }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $layoutUser['email'] ?? '' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 min-w-0 pl-0 lg:pl-64">
            {{-- Top header --}}
            <header class="sticky top-0 z-20 h-16 bg-white border-b border-gray-200 flex items-center justify-between gap-4 px-4 sm:px-8">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarOpen = true" type="button" class="lg:hidden shrink-0 p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg focus:outline-none" aria-label="Open menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900 truncate">@yield('title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-4" x-data="{ open: false }">
                    <div class="relative">
                        <button @click="open = !open" type="button"
                                class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-100 focus:outline-none">
                            <img src="{{ $layoutUserAvatar }}"
                                 alt="" class="w-8 h-8 rounded-full object-cover">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="p-4 sm:p-8 overflow-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
