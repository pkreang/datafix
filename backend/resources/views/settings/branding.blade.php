@extends('layouts.app')

@section('title', __('branding.title'))

@section('content')
    <div class="w-full">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('branding.title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('branding.subtitle') }}</p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.branding.save') }}" enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            {{-- System Logo --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('branding.system_logo') }}</h3>
                @if ($systemLogo)
                    <div class="mb-4 flex items-center gap-4">
                        <img src="{{ asset('storage/' . $systemLogo) }}" alt="Logo" class="h-16 object-contain bg-white dark:bg-gray-700 rounded-lg p-2 border border-gray-200 dark:border-gray-600">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                            <input type="hidden" name="remove_system_logo" value="0">
                            <input type="checkbox" name="remove_system_logo" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500 w-4 h-4">
                            {{ __('branding.remove_logo') }}
                        </label>
                    </div>
                @endif
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('branding.system_logo_help') }}</p>
                <input type="file" name="system_logo" id="system_logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml"
                       class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 dark:file:bg-gray-600 dark:file:text-gray-200">
            </div>

            {{-- Login Background --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('branding.login_background') }}</h3>
                @if ($loginBackground)
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $loginBackground) }}" alt="Background" class="max-h-32 rounded-lg border border-gray-200 dark:border-gray-600 object-cover">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer mt-2">
                            <input type="hidden" name="remove_login_background" value="0">
                            <input type="checkbox" name="remove_login_background" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500 w-4 h-4">
                            {{ __('branding.remove_background') }}
                        </label>
                    </div>
                @endif
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('branding.login_background_help') }}</p>
                <input type="file" name="login_background" id="login_background" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                       class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 dark:file:bg-gray-600 dark:file:text-gray-200">
            </div>

            {{-- Login Illustration (ภาพแผงซ้ายหน้าล็อกอิน e.g. โรงงาน) --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('branding.login_illustration') }}</h3>
                @if ($loginIllustration ?? null)
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $loginIllustration) }}" alt="Illustration" class="max-h-40 rounded-lg border border-gray-200 dark:border-gray-600 object-contain">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer mt-2">
                            <input type="hidden" name="remove_login_illustration" value="0">
                            <input type="checkbox" name="remove_login_illustration" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500 w-4 h-4">
                            {{ __('branding.remove_login_illustration') }}
                        </label>
                    </div>
                @endif
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('branding.login_illustration_help') }}</p>
                <input type="file" name="login_illustration" id="login_illustration" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                       class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 dark:file:bg-gray-600 dark:file:text-gray-200">
            </div>

            {{-- Login Background Color --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('branding.login_background_color') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('branding.login_background_color_help') }}</p>
                <div class="flex flex-wrap items-center gap-3" x-data="{ color: '{{ old('login_background_color', $loginBackgroundColor) }}' }">
                    <input type="color" :value="color" @input="color = $event.target.value"
                           class="h-10 w-14 rounded border border-gray-300 dark:border-gray-600 cursor-pointer bg-white dark:bg-gray-700">
                    <input type="text" name="login_background_color" x-model="color"
                           class="w-28 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                           placeholder="#2563eb">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    {{ __('common.save') }}
                </button>
                <a href="{{ route('dashboard') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    {{ __('common.cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection
