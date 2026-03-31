@extends('layouts.app')

@section('title', __('common.change_password'))

@section('content')
<div class="max-w-xl mx-auto">

    @if(session('success'))
    <div class="flex items-center gap-2 px-4 py-3 rounded-lg mb-6
                bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800
                text-green-700 dark:text-green-400 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Password policy --}}
    @if(!empty($passwordPolicy))
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4 mb-6">
        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">{{ __('password_policy.password_must') }}</h3>
        <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-disc list-inside">
            @foreach($passwordPolicy as $rule)
            <li>{{ $rule }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            {{ __('common.change_password') }}
        </h2>

        <form method="POST" action="{{ route('profile.password.update') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.current_password') }}</label>
                    <input type="password" name="current_password" id="current_password"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('current_password')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.new_password') }}</label>
                    <input type="password" name="password" id="new_password"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('password')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.confirm_new_password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('profile.edit') }}"
                   class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600
                          text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800
                          hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium rounded-lg
                               bg-blue-600 hover:bg-blue-700 text-white transition-colors">
                    {{ __('common.change_password') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
