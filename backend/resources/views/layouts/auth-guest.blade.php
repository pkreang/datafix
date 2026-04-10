<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page-title', $pageTitle ?? config('app.name')) - {{ config('app.name') }}</title>

    <link rel="icon" href="data:,">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 text-base text-slate-800 dark:text-slate-200 relative">
    @php
        $bgImage = ($loginBackground ?? null)
            ? asset('storage/' . $loginBackground)
            : asset('images/approval-workflow.jpg');
    @endphp
    <div class="fixed inset-0 z-0" style="background: url({{ $bgImage }}) center center / cover no-repeat; background-color: {{ $loginBackgroundColor ?? '#1e3a8a' }};"></div>
    <div class="fixed inset-0 z-0 bg-black/40"></div>

    <div class="fixed bottom-4 right-4 z-50 text-xs text-white/80 font-medium drop-shadow">v{{ config('app.version') }}</div>

    <div class="relative z-10 w-full max-w-[634px] mx-auto flex flex-col lg:flex-row rounded-[16px] shadow-2xl overflow-hidden border border-white/10 bg-white dark:bg-gray-900 login-card">
        <div class="hidden lg:flex lg:w-[42%] flex-col justify-center items-center text-center p-8 lg:p-10 bg-gradient-to-b from-blue-800 to-blue-600 text-white login-welcome">
            @if ($systemLogo ?? null)
                <img src="{{ asset('storage/' . $systemLogo) }}" alt="{{ config('app.name') }}" class="max-h-20 w-auto object-contain mb-8 opacity-95">
            @else
                <h1 class="login-brand">{{ config('app.name') }}</h1>
            @endif
            <h2 class="login-welcome-title">{{ $welcomeTitle ?? __('common.login_welcome', ['app' => config('app.name')]) }}</h2>
            <p class="login-welcome-desc">{{ $welcomeSubtitle ?? __('common.login_welcome_subtitle') }}</p>
        </div>

        <div class="flex-1 flex items-center justify-center p-6 sm:p-8 lg:p-10 bg-white dark:bg-gray-900 min-w-0">
            <div class="w-full max-w-[280px] sm:max-w-xs">
                @if ($systemLogo ?? null)
                    <img src="{{ asset('storage/' . $systemLogo) }}" alt="{{ config('app.name') }}" class="lg:hidden h-12 w-auto object-contain mb-6 mx-auto">
                @else
                    <h1 class="lg:hidden text-center font-bold tracking-widest text-blue-600 mb-6 text-2xl">{{ config('app.name') }}</h1>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <style>
    [x-cloak]{display:none!important}
    .login-card { min-width: 0; max-width: 634px; }
    @media (min-width: 1024px) {
        .login-card { box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05); }
    }
    .login-welcome { background: linear-gradient(to bottom, #1e40af, #2563eb); color: #fff; }
    .login-welcome .login-brand,
    .login-welcome .login-welcome-title,
    .login-welcome .login-welcome-desc { color: #fff; }
    .login-welcome .login-welcome-desc { opacity: 0.9; }
    .login-brand { font-size: 2.75rem; font-weight: 700; letter-spacing: 0.025em; margin-bottom: 1.5rem; }
    .login-welcome-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; }
    .login-welcome-desc { font-size: 0.9375rem; line-height: 1.6; }
    .login-form-title { font-size: 1.625rem; font-weight: 700; color: inherit; margin-bottom: 1.5rem; text-align: center; }
    .login-form-label { display: block; font-size: 1.0625rem; font-weight: 500; margin-bottom: 0.25rem; color: #374151; }
    .dark .login-form-label { color: #d1d5db; }
    .login-form-input { font-size: 0.9375rem; padding-top: 0.5rem; padding-bottom: 0.5rem; }
    .login-form-link { font-size: 0.9375rem; color: #2563eb; text-decoration: underline; }
    .dark .login-form-link { color: #60a5fa; }
    .login-form-btn { width: 100%; padding: 0.5rem 1rem; font-size: 0.9375rem; font-weight: 600; background: #2563eb; color: #fff; border-radius: 0.5rem; border: none; cursor: pointer; transition: background 0.2s; }
    .login-form-btn:hover { background: #1d4ed8; }
    .login-form-error { font-size: 0.9375rem; }
    </style>
    @stack('scripts')
</body>
</html>
