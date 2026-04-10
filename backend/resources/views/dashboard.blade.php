@extends('layouts.app')

@section('title', __('common.dashboard'))

@section('content')
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-200">
            {{ __('common.dashboard') }}
        </h2>
        @if($canCustomize)
            <p class="text-xs text-slate-400 dark:text-slate-500">{{ __('common.dashboard_customize_hint') }}</p>
        @endif
    </div>

    {{-- KPI Cards grid — listens for hide-kpi-card events to remove cards and persist the change --}}
    @php
        $cardMeta = [
            'school_pending_approvals' => ['title' => __('common.kpi_school_pending_approvals'), 'color' => 'orange', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'school_submissions_this_month' => ['title' => __('common.kpi_school_submissions_this_month'), 'color' => 'blue', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            'school_my_submissions_this_month' => ['title' => __('common.kpi_school_my_submissions_this_month'), 'color' => 'green', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            'school_my_pending_requests' => ['title' => __('common.kpi_school_my_pending_requests'), 'color' => 'yellow', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            'school_draft_forms' => ['title' => __('common.kpi_school_draft_forms'), 'color' => 'gray', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
            'active_users' => ['title' => __('common.kpi_active_users'), 'color' => 'green', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ];
        $apiToken = session('api_token', '');
    @endphp

    <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8"
        x-data="{
            enabledCards: {{ json_encode($enabledCards) }},
            hideCard(card) {
                this.enabledCards = this.enabledCards.filter(c => c !== card);
                fetch('{{ url('/api/v1/dashboard/kpi-config') }}', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer {{ $apiToken }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ cards: this.enabledCards })
                });
            }
        }"
        @hide-kpi-card.window="hideCard($event.detail.card)"
    >
        @foreach($enabledCards as $cardKey)
            @if(isset($cardMeta[$cardKey]))
                <div x-show="enabledCards.includes('{{ $cardKey }}')">
                    <x-kpi-card
                        :card="$cardKey"
                        :title="$cardMeta[$cardKey]['title']"
                        :icon="$cardMeta[$cardKey]['icon']"
                        :color="$cardMeta[$cardKey]['color']"
                        :canToggle="$canCustomize"
                    />
                </div>
            @endif
        @endforeach
    </div>

    {{-- Welcome --}}
    <div class="card p-6">
        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-2">
            {{ __('common.welcome_back') }}, {{ trim(session('user.first_name', '') . ' ' . session('user.last_name', '')) ?: session('user.name', 'User') }}!
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.welcome_subtitle') }}</p>
    </div>
@endsection
