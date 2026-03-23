<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - DATA FIX</title>

    <link rel="icon" href="data:,">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 text-base text-gray-800 dark:text-gray-200 relative">
    @php
        $bgImage = ($loginBackground ?? null)
            ? asset('storage/' . $loginBackground)
            : asset('images/login-bg-factory.jpg');
    @endphp
    {{-- Full-page background (factory / custom) --}}
    <div class="fixed inset-0 z-0" style="background: url({{ $bgImage }}) center center / cover no-repeat; background-color: {{ $loginBackgroundColor ?? '#1e3a8a' }};"></div>
    <div class="fixed inset-0 z-0 bg-black/40"></div>

    {{-- Version badge --}}
    <div class="fixed bottom-4 right-4 z-50 text-xs text-white/80 font-medium drop-shadow">v{{ config('app.version') }}</div>

    {{-- Centered card (compact width like reference) --}}
    <div class="relative z-10 w-full max-w-[634px] mx-auto flex flex-col lg:flex-row rounded-2xl shadow-2xl overflow-hidden border border-white/10 bg-white dark:bg-gray-900 login-card">
        {{-- Left: Welcome (สีเดียวกับ sidebar) --}}
        <div class="hidden lg:flex lg:w-[42%] flex-col justify-center items-center text-center p-8 lg:p-10 bg-blue-600 text-white login-welcome">
            @if ($systemLogo ?? null)
                <img src="{{ asset('storage/' . $systemLogo) }}" alt="{{ config('app.name') }}" class="max-h-20 w-auto object-contain mb-8 opacity-95">
            @else
                <h1 class="login-brand">DATA FIX</h1>
            @endif
            <h2 class="login-welcome-title">{{ __('common.login_welcome') }}</h2>
            <p class="login-welcome-desc">{{ __('common.login_welcome_subtitle') }}</p>
        </div>

        {{-- Right: Form --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-8 lg:p-10 bg-white dark:bg-gray-900 min-w-0">
            <div class="w-full max-w-[280px] sm:max-w-xs" x-data="{ showPassword: false }">
                @if ($systemLogo ?? null)
                    <img src="{{ asset('storage/' . $systemLogo) }}" alt="{{ config('app.name') }}" class="lg:hidden h-12 w-auto object-contain mb-6 mx-auto">
                @else
                    <h1 class="lg:hidden text-center font-bold tracking-widest text-blue-600 mb-6 text-2xl">DATA FIX</h1>
                @endif
                <h2 class="login-form-title text-center">{{ __('common.login') }}</h2>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                        <p class="login-form-error">{{ $errors->first() }}</p>
                    </div>
                @endif

                @if (isset($authConfigured) && ! $authConfigured)
                    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                        <p class="text-sm text-amber-800 dark:text-amber-200">{{ __('auth.misconfigured') }}</p>
                    </div>
                @endif

                @if (! empty($authLocalEnabled))
                <form method="POST" action="{{ route('login') }}" class="space-y-5 login-form">
                    @csrf

                    <div>
                        <label for="email" class="login-form-label">{{ __('auth.placeholder_email') }}</label>
                        <div class="relative">
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   placeholder="{{ __('auth.placeholder_email') }}" required autofocus
                                   class="login-form-input w-full pl-3 pr-10 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="login-form-label">{{ __('auth.placeholder_password') }}</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                                   placeholder="{{ __('auth.placeholder_password') }}"
                                   class="login-form-input w-full pl-3 pr-10 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
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

                    <div class="text-right">
                        <a href="#" class="login-form-link">{{ __('common.forgot_password') }}</a>
                    </div>

                    <button type="submit" class="login-form-btn">
                        {{ __('common.login') }}
                    </button>
                </form>
                @endif

                @if (! empty($authEntraEnabled) || ! empty($authLdapEnabled))
                    @if (! empty($authLocalEnabled))
                        <p class="text-center text-sm text-gray-500 dark:text-gray-400 my-5">{{ __('auth.or_use') }}</p>
                    @endif

                    @if (! empty($authEntraEnabled))
                        <a href="{{ route('auth.entra.redirect') }}"
                           class="login-form-btn flex items-center justify-center gap-2 no-underline text-center mb-4 bg-gray-800 hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600">
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 21 21" aria-hidden="true"><path fill="currentColor" d="M0 0h10v10H0V0zm11 0h10v10H11V0zM0 11h10v10H0V11zm11 0h10v10H11V11z"/></svg>
                            {{ __('auth.sign_in_with_microsoft') }}
                        </a>
                    @endif

                    @if (! empty($authLdapEnabled))
                        <div class="mt-2 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('auth.sign_in_ldap') }}</p>
                            <form method="POST" action="{{ route('auth.ldap.login') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="ldap_email" class="login-form-label">{{ __('auth.ldap_email') }}</label>
                                    <input type="email" name="ldap_email" id="ldap_email" value="{{ old('ldap_email') }}"
                                           class="login-form-input w-full px-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl"
                                           required autocomplete="username">
                                </div>
                                <div>
                                    <label for="ldap_password" class="login-form-label">{{ __('auth.ldap_password') }}</label>
                                    <input type="password" name="ldap_password" id="ldap_password"
                                           class="login-form-input w-full px-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl"
                                           required autocomplete="current-password">
                                </div>
                                <button type="submit" class="login-form-btn bg-slate-700 hover:bg-slate-800">{{ __('auth.sign_in_ldap') }}</button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <style>
    [x-cloak]{display:none!important}
    .login-card { min-width: 0; max-width: 634px; }
    @media (min-width: 1024px) {
        .login-card { box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05); }
    }
    /* ฝั่งต้อนรับ: พื้นหลังสีเดียวกับ sidebar (blue-600) ข้อความขาว */
    .login-welcome { background-color: #2563eb; color: #fff; }
    .login-welcome .login-brand,
    .login-welcome .login-welcome-title,
    .login-welcome .login-welcome-desc { color: #fff; }
    .login-welcome .login-welcome-desc { opacity: 0.9; }
    /* ขนาดตัวอักษรและปุ่มแบบกำหนดค่า (ไม่พึ่ง Tailwind cache) */
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
</body>
</html>
