@extends('layouts.app')

@section('title', __('notifications.notification_settings'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('notifications.notification_settings') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('notifications.notification_settings_desc') }}</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('settings.notifications.update') }}">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            {{-- Email Section --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('notifications.email_notifications') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('notifications.email_notifications_desc') }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="toggle[notifications.email_enabled]" value="0">
                        <input type="checkbox" name="toggle[notifications.email_enabled]" value="1"
                               {{ ($settings['notifications.email_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('notifications.email_notifications') }}</span>
                    </label>

                    @php
                        $emailEvents = [
                            'notifications.approval_pending_email' => __('notifications.event_approval_pending'),
                            'notifications.workflow_approved_email' => __('notifications.event_workflow_approved'),
                            'notifications.workflow_rejected_email' => __('notifications.event_workflow_rejected'),
                        ];
                    @endphp

                    @foreach($emailEvents as $key => $label)
                        <label class="flex items-center gap-3 cursor-pointer ml-6">
                            <input type="hidden" name="toggle[{{ $key }}]" value="0">
                            <input type="checkbox" name="toggle[{{ $key }}]" value="1"
                                   {{ ($settings[$key] ?? '1') === '1' ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- LINE Notify Section --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.271.173-.508.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('notifications.line_notifications') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('notifications.line_notifications_desc') }}</p>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 ml-11">
                    {{ __('notifications.line_token_hint') }}
                    <a href="https://notify-bot.line.me/" target="_blank" rel="noopener noreferrer"
                       class="text-blue-600 dark:text-blue-400 hover:underline">notify-bot.line.me</a>
                </p>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="toggle[notifications.line_enabled]" value="0">
                        <input type="checkbox" name="toggle[notifications.line_enabled]" value="1"
                               {{ ($settings['notifications.line_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-green-600 focus:ring-green-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('notifications.line_notifications') }}</span>
                    </label>

                    @php
                        $lineEvents = [
                            'notifications.approval_pending_line' => __('notifications.event_approval_pending'),
                            'notifications.workflow_approved_line' => __('notifications.event_workflow_approved'),
                            'notifications.workflow_rejected_line' => __('notifications.event_workflow_rejected'),
                        ];
                    @endphp

                    @foreach($lineEvents as $key => $label)
                        <label class="flex items-center gap-3 cursor-pointer ml-6">
                            <input type="hidden" name="toggle[{{ $key }}]" value="0">
                            <input type="checkbox" name="toggle[{{ $key }}]" value="1"
                                   {{ ($settings[$key] ?? '1') === '1' ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500 w-4 h-4">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium
                           rounded-lg hover:bg-blue-700 transition-colors">
                {{ __('notifications.save_settings') }}
            </button>
        </div>
    </form>
@endsection
