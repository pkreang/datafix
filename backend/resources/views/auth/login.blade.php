<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - DATA FIX</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex relative">
    {{-- Version badge --}}
    <div class="fixed bottom-4 right-4 z-50 text-sm text-gray-500 font-medium">v0.1</div>

    {{-- Left: Illustration --}}
    <div class="hidden lg:flex flex-1 flex-col bg-blue-600 items-center justify-center p-12 relative overflow-hidden">
        <h1 class="relative z-10 text-[5rem] lg:text-[6.5rem] font-bold tracking-[0.15em] text-white mb-10 drop-shadow-lg">DATA FIX</h1>
        <div class="relative z-10 max-w-md">
            <svg class="w-full h-auto" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="chair" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#f97316"/>
                        <stop offset="100%" style="stop-color:#ea580c"/>
                    </linearGradient>
                </defs>
                <rect x="80" y="180" width="240" height="80" rx="8" fill="url(#chair)"/>
                <rect x="100" y="120" width="200" height="90" rx="4" fill="#1e3a8a" stroke="#334155" stroke-width="1"/>
                <circle cx="200" cy="90" r="35" fill="#fbbf24"/>
                <rect x="60" y="200" width="40" height="60" rx="4" fill="#78716c"/>
                <rect x="300" y="200" width="40" height="60" rx="4" fill="#78716c"/>
                <rect x="130" y="135" width="140" height="60" rx="2" fill="#475569"/>
                <rect x="165" y="60" width="30" height="40" rx="4" fill="#f97316"/>
                <path d="M195 50 Q220 35 245 50" stroke="#64748b" stroke-width="2" fill="none"/>
            </svg>
        </div>
        <div class="absolute inset-0 opacity-30">
            <div class="absolute top-20 left-20 w-32 h-32 rounded-full bg-indigo-200"></div>
            <div class="absolute bottom-32 right-20 w-48 h-48 rounded-full bg-indigo-100"></div>
            <div class="absolute top-1/2 left-1/3 w-24 h-24 rounded-lg bg-indigo-150 transform -rotate-12"></div>
        </div>
    </div>

    {{-- Right: Form --}}
    <div class="flex-1 flex items-center justify-center bg-white p-8 sm:p-12 relative">
        <div class="w-full max-w-md" x-data="{ showPassword: false }">
            <h1 class="lg:hidden text-5xl font-bold tracking-widest text-blue-600 mb-2">DATA FIX</h1>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-10">Login</h2>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-base text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-7">
                @csrf

                <div>
                    <label for="email" class="sr-only">Email</label>
                    <div class="relative">
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               placeholder="email address" required autofocus
                               class="w-full pl-5 pr-14 py-4 text-base bg-gray-100 border-0 rounded-xl text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password" class="sr-only">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                               placeholder="password"
                               class="w-full pl-5 pr-14 py-4 text-base bg-gray-100 border-0 rounded-xl text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-5 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.783-2.961m5.208 5.208A3 3 0 1112 15m-5.625-5.625A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.783 2.961m0 0a10.047 10.047 0 01-2.695 2.195"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-4 px-6 text-base sm:text-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Login
                </button>

                <div class="text-right">
                    <a href="#" class="text-sm sm:text-base text-blue-600 hover:text-blue-500 underline">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>

    <style>[x-cloak]{display:none!important}</style>
</body>
</html>
