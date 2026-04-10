@extends('layouts.app')

@section('title', __('common.change_password'))

@section('content')
<div class="max-w-xl mx-auto">

    @if(session('success'))
    <div class="alert-success mb-6">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('warning'))
    <div class="alert-warning mb-6">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        {{ session('warning') }}
    </div>
    @endif

    @if(!empty($passwordChangeMandatory))
    <div class="alert-warning mb-6">
        {{ __('auth.password_change_required') }}
    </div>
    @endif

    {{-- Password policy --}}
    @if(!empty($passwordPolicy))
    <div class="alert-info rounded-xl p-4 mb-6">
        <h3 class="text-sm font-semibold mb-2">{{ __('password_policy.password_must') }}</h3>
        <ul class="text-sm space-y-1 list-disc list-inside">
            @foreach($passwordPolicy as $rule)
            <li>{{ $rule }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card p-6">
        <h2 class="font-semibold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            {{ __('common.change_password') }}
        </h2>

        <form method="POST" action="{{ route('profile.password.update') }}" novalidate>
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="form-label">{{ __('common.current_password') }}</label>
                    <input type="password" name="current_password" id="current_password"
                           class="form-input">
                    @error('current_password')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="new_password" class="form-label">{{ __('common.new_password') }}</label>
                    <input type="password" name="password" id="new_password"
                           class="form-input">
                    @error('password')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">{{ __('common.confirm_new_password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-input">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                @if(empty($passwordChangeMandatory))
                <a href="{{ route('profile.edit') }}" class="btn-secondary">
                    {{ __('common.cancel') }}
                </a>
                @else
                <form method="POST" action="{{ route('logout') }}" class="inline" novalidate>
                    @csrf
                    <button type="submit" class="btn-secondary">
                        {{ __('common.sign_out') }}
                    </button>
                </form>
                @endif
                <button type="submit" class="btn-primary">
                    {{ __('common.change_password') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
