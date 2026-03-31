@extends('layouts.app')

@section('title', __('notifications.notifications'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('notifications.notifications') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('notifications.all_notifications_desc') }}</p>
        </div>
        @if($notifications->total() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium
                               text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20
                               hover:bg-blue-100 dark:hover:bg-blue-900/30
                               rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('notifications.mark_all_read') }}
                </button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">{{ __('notifications.no_notifications') }}</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($notifications as $notification)
                <a href="{{ route('notifications.read', $notification->id) }}"
                   class="flex gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors
                          {{ $notification->read_at ? '' : 'bg-blue-50/50 dark:bg-blue-900/10' }}">
                    <div class="shrink-0 mt-0.5">
                        @if(($notification->data['icon'] ?? '') === 'check-circle')
                            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif(($notification->data['icon'] ?? '') === 'x-circle')
                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $notification->data['title'] ?? '' }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $notification->data['body'] ?? '' }}
                        </p>
                        @if($notification->data['comment'] ?? null)
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 italic">
                                "{{ $notification->data['comment'] }}"
                            </p>
                        @endif
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                    @if(! $notification->read_at)
                        <div class="shrink-0 mt-2">
                            <span class="block w-2 h-2 rounded-full bg-blue-500"></span>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
@endsection
