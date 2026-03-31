@extends('layouts.app')

@section('title', __('common.my_profile'))

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    @if(session('success'))
    <div class="flex items-center gap-2 px-4 py-3 rounded-lg
                bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800
                text-green-700 dark:text-green-400 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('info'))
    <div class="flex items-center gap-2 px-4 py-3 rounded-lg
                bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800
                text-blue-800 dark:text-blue-200 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('info') }}
    </div>
    @endif

    {{-- Profile Card --}}
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">

        {{-- Avatar --}}
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100 dark:border-gray-700">
            @php
                $avatarColors = ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EF4444'];
                $colorIndex = abs(crc32($user->full_name)) % 5;
                $initials = strtoupper(mb_substr($user->first_name ?? '', 0, 1) . mb_substr($user->last_name ?? '', 0, 1)) ?: '??';
            @endphp
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="" class="w-16 h-16 rounded-full object-cover flex-shrink-0">
            @else
                <div class="w-16 h-16 rounded-full flex items-center justify-center
                            text-xl font-bold text-white flex-shrink-0"
                     style="background: {{ $avatarColors[$colorIndex] }}">
                    {{ $initials }}
                </div>
            @endif
            <div>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->full_name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                @foreach($user->roles as $role)
                <span class="inline-block px-2 py-0.5 mt-1 rounded-full text-xs font-medium
                             bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                    {{ $role->display_name ?? $role->name }}
                </span>
                @endforeach
                <div class="mt-1">
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        DataFix v{{ config('app.version') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Edit Form --}}
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.first_name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('first_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.last_name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('last_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.email') }}</label>
                    <input type="email" id="email" value="{{ $user->email }}" readonly
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-400 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('users.email_readonly_hint') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.department') }}</label>
                        <input type="text" name="department" id="department" value="{{ old('department', $user->department) }}"
                               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                      bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="position_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.position') }}</label>
                        <select name="position_id" id="position_id"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('common.choose_position') }}</option>
                            @foreach ($positions as $pos)
                                <option value="{{ $pos->id }}" @selected(old('position_id', $user->position_id) == $pos->id)>{{ $pos->name }} ({{ $pos->code }})</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('users.position_from_master_hint') }}</p>
                    </div>
                </div>

                {{-- LINE Notify Token --}}
                <div>
                    <label for="line_notify_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.271.173-.508.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                            </svg>
                            {{ __('notifications.line_notify_token') }}
                        </span>
                    </label>
                    <input type="text" name="line_notify_token" id="line_notify_token"
                           value="{{ old('line_notify_token', $user->line_notify_token) }}"
                           placeholder="{{ __('notifications.line_notify_token_placeholder') }}"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('notifications.line_notify_token_hint') }}
                        <a href="https://notify-bot.line.me/" target="_blank" rel="noopener noreferrer"
                           class="text-blue-600 dark:text-blue-400 hover:underline">notify-bot.line.me</a>
                    </p>
                    @error('line_notify_token')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

            </div>
            <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ url()->previous() }}"
                   class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600
                          text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800
                          hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium rounded-lg
                               bg-blue-600 hover:bg-blue-700 text-white transition-colors">
                    {{ __('common.save') }}
                </button>
            </div>
        </form>
    </div>

    @if (empty($canChangePasswordInApp))
    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 p-6">
        <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-100 mb-2">{{ __('auth.password_managed_by_org') }}</h3>
        <p class="text-sm text-amber-800 dark:text-amber-200 mb-3">{{ __('auth.password_use_org_portal') }}</p>
        @if (! empty($authPasswordHelpUrl))
            <a href="{{ $authPasswordHelpUrl }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 text-sm font-medium text-amber-900 dark:text-amber-100 underline hover:no-underline">
                {{ __('auth.open_password_help_link') }}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        @endif
    </div>
    @endif
</div>
@endsection
