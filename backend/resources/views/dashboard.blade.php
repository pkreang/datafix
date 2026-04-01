@extends('layouts.app')

@section('title', __('common.dashboard'))

@section('content')
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            {{ __('common.dashboard') }}
        </h1>
        @if($canCustomize)
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('common.dashboard_customize_hint') }}</p>
        @endif
    </div>

    {{-- KPI Cards grid — listens for hide-kpi-card events to remove cards and persist the change --}}
    @php
        $cardMeta = [
            'repair_pending'     => ['title' => __('common.kpi_repair_pending'),     'color' => 'orange', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            'repair_this_month'  => ['title' => __('common.kpi_repair_this_month'),  'color' => 'blue',   'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            'pm_pending'         => ['title' => __('common.kpi_pm_pending'),         'color' => 'yellow', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            'pm_this_week'       => ['title' => __('common.kpi_pm_this_week'),       'color' => 'green',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            'spare_low_stock'    => ['title' => __('common.kpi_spare_low_stock'),    'color' => 'red',    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            'equipment_active'   => ['title' => __('common.kpi_equipment_active'),   'color' => 'green',  'icon' => 'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18'],
            'my_pending_repairs' => ['title' => __('common.kpi_my_pending_repairs'), 'color' => 'orange', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
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
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-6">
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">
            {{ __('common.welcome_back') }}, {{ trim(session('user.first_name', '') . ' ' . session('user.last_name', '')) ?: session('user.name', 'User') }}!
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.welcome_subtitle') }}</p>
    </div>
@endsection
