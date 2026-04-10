@extends('layouts.auth-guest')

@section('content')
    <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6 text-center">{{ __('auth.forgot_password_page_title') }}</h2>

    @if (session('status'))
        <div class="alert-success mb-5" role="status">{{ session('status') }}</div>
    @endif

    <p class="text-sm text-gray-600 dark:text-gray-400 mb-5 text-center leading-relaxed">{{ __('auth.forgot_password_intro') }}</p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf
        <div>
            <label for="email" class="form-label">{{ __('auth.placeholder_email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   placeholder="{{ __('auth.placeholder_email') }}" required autofocus
                   class="form-input @error('email') form-input-error @enderror"
                   aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                   @if ($errors->has('email')) aria-describedby="email-error" @endif>
            @error('email')
                <p id="email-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">{{ __('auth.forgot_password_submit') }}</button>

        <p class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('auth.back_to_login') }}</a>
        </p>
    </form>
@endsection
