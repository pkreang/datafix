@extends('layouts.auth-guest')

@section('content')
    <div x-data="{ showPassword: false, showPassword2: false }">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6 text-center">{{ __('auth.reset_password_page_title') }}</h2>

        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 text-center leading-relaxed">{{ __('auth.reset_password_intro') }}</p>

        @if (! empty($passwordPolicyLines))
            <ul class="text-xs text-slate-500 dark:text-slate-400 mb-5 list-disc list-inside space-y-0.5">
                @foreach ($passwordPolicyLines as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label for="password" class="form-label">{{ __('auth.reset_password_new') }}</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required autocomplete="new-password"
                           class="form-input pr-10 @error('password') form-input-error @enderror"
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           @if ($errors->has('password')) aria-describedby="password-error" @endif>
                    <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.783-2.961m5.208 5.208A3 3 0 1112 15m-5.625-5.625A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.783 2.961m0 0a10.047 10.047 0 01-2.695 2.195"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="form-label">{{ __('auth.reset_password_confirm') }}</label>
                <div class="relative">
                    <input :type="showPassword2 ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                           class="form-input pr-10 @error('password_confirmation') form-input-error @enderror"
                           aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                           @if ($errors->has('password_confirmation')) aria-describedby="password-confirmation-error" @endif>
                    <button type="button" @click="showPassword2 = !showPassword2"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none">
                        <svg x-show="!showPassword2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword2" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.783-2.961m5.208 5.208A3 3 0 1112 15m-5.625-5.625A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.783 2.961m0 0a10.047 10.047 0 01-2.695 2.195"/>
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <p id="password-confirmation-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full py-2.5">{{ __('auth.reset_password_submit') }}</button>

            <p class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('auth.back_to_login') }}</a>
            </p>
        </form>
    </div>
@endsection
