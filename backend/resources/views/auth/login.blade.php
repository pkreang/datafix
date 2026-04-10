@extends('layouts.auth-guest')

@section('page-title', __('common.login'))

@section('content')
<div x-data="{ showPassword: false }">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6 text-center">{{ __('common.login') }}</h2>

    @if (session('status'))
        <div class="alert-success mb-5" role="status">{{ session('status') }}</div>
    @endif

    @if (isset($authConfigured) && ! $authConfigured)
        <div class="alert-warning mb-6">{{ __('auth.misconfigured') }}</div>
    @endif

    @if (! empty($authLocalEnabled))
    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf
        <div>
            <label for="email" class="form-label">{{ __('auth.placeholder_email') }}</label>
            <div class="relative">
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       placeholder="{{ __('auth.placeholder_email') }}" required autofocus
                       class="form-input pr-10 @error('email') form-input-error @enderror"
                       aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                       @if ($errors->has('email')) aria-describedby="email-error" @endif>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
            @error('email')
                <p id="email-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="form-label">{{ __('auth.placeholder_password') }}</label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                       placeholder="{{ __('auth.placeholder_password') }}"
                       class="form-input pr-10 @error('password') form-input-error @enderror"
                       aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                       @if ($errors->has('password')) aria-describedby="password-error" @endif>
                <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none cursor-pointer">
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.783-2.961m5.208 5.208A3 3 0 1112 15m-5.625-5.625A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.783 2.961"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p id="password-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        @if (! empty($authLocalEnabled))
            <div class="text-right">
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('common.forgot_password') }}</a>
            </div>
        @endif

        <button type="submit" class="btn-primary w-full py-2.5">{{ __('common.login') }}</button>
    </form>
    @endif

    @if (! empty($authEntraEnabled) || ! empty($authLdapEnabled))
        @if (! empty($authLocalEnabled))
            <p class="text-center text-sm text-slate-500 dark:text-slate-400 my-5">{{ __('auth.or_use') }}</p>
        @endif
        @if (! empty($authEntraEnabled))
            <a href="{{ route('auth.entra.redirect') }}"
               class="btn-secondary w-full py-2.5 mb-4 no-underline bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 21 21" aria-hidden="true"><path fill="currentColor" d="M0 0h10v10H0V0zm11 0h10v10H11V0zM0 11h10v10H0V11zm11 0h10v10H11V11z"/></svg>
                {{ __('auth.sign_in_with_microsoft') }}
            </a>
        @endif
        @if (! empty($authLdapEnabled))
            <div class="mt-2 pt-4 border-t border-slate-200 dark:border-slate-600">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">{{ __('auth.sign_in_ldap') }}</p>
                <form method="POST" action="{{ route('auth.ldap.login') }}" class="space-y-4" novalidate>
                    @csrf
                    <div>
                        <label for="ldap_email" class="form-label">{{ __('auth.ldap_email') }}</label>
                        <input type="email" name="ldap_email" id="ldap_email" value="{{ old('ldap_email') }}"
                               class="form-input @error('ldap_email') form-input-error @enderror"
                               required autocomplete="username"
                               aria-invalid="{{ $errors->has('ldap_email') ? 'true' : 'false' }}"
                               @if ($errors->has('ldap_email')) aria-describedby="ldap-email-error" @endif>
                        @error('ldap_email')
                            <p id="ldap-email-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ldap_password" class="form-label">{{ __('auth.ldap_password') }}</label>
                        <input type="password" name="ldap_password" id="ldap_password"
                               class="form-input @error('ldap_password') form-input-error @enderror"
                               required autocomplete="current-password"
                               aria-invalid="{{ $errors->has('ldap_password') ? 'true' : 'false' }}"
                               @if ($errors->has('ldap_password')) aria-describedby="ldap-password-error" @endif>
                        @error('ldap_password')
                            <p id="ldap-password-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn-secondary w-full py-2.5 bg-slate-700 hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-700 text-white">
                        {{ __('auth.sign_in_ldap') }}
                    </button>
                </form>
            </div>
        @endif
    @endif
</div>
@endsection
