@props([
    'card',         // string — KPI card key e.g. 'school_pending_approvals'
    'title',        // string — display label
    'icon',         // string — Heroicons SVG path data (d attribute)
    'color',        // string — Tailwind color name e.g. 'blue', 'orange', 'green'
    'canToggle' => false,
])

@php
    $apiToken = session('api_token', '');
    $colorMap = [
        'blue'   => ['bg' => 'bg-blue-100 dark:bg-blue-900/30',    'icon' => 'text-blue-600 dark:text-blue-400'],
        'orange' => ['bg' => 'bg-orange-100 dark:bg-orange-900/30', 'icon' => 'text-orange-600 dark:text-orange-400'],
        'green'  => ['bg' => 'bg-green-100 dark:bg-green-900/30',   'icon' => 'text-green-600 dark:text-green-400'],
        'red'    => ['bg' => 'bg-red-100 dark:bg-red-900/30',       'icon' => 'text-red-600 dark:text-red-400'],
        'purple' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'icon' => 'text-purple-600 dark:text-purple-400'],
        'yellow' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/30', 'icon' => 'text-yellow-600 dark:text-yellow-400'],
        'gray'   => ['bg' => 'bg-slate-100 dark:bg-slate-800/50',      'icon' => 'text-slate-600 dark:text-slate-400'],
    ];
    $c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div
    x-data="{
        value: null,
        delta: null,
        deltaDirection: null,
        loading: true,
        async fetchKpi() {
            try {
                const res = await fetch('{{ url('/api/v1/dashboard/kpi/' . $card) }}', {
                    headers: {
                        'Authorization': 'Bearer {{ $apiToken }}',
                        'Accept': 'application/json',
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.value = data.value;
                    this.delta = data.delta ?? null;
                    this.deltaDirection = data.delta_direction ?? null;
                }
            } finally {
                this.loading = false;
            }
        }
    }"
    x-init="fetchKpi()"
    class="card p-6 relative"
    data-card-key="{{ $card }}"
>
    @if($canToggle)
        <button
            @click="$dispatch('hide-kpi-card', { card: '{{ $card }}' })"
            class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
            title="{{ __('common.hide_card') }}"
            aria-label="{{ __('common.hide_card') }}"
            type="button"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif

    <div class="flex items-center gap-4">
        <div class="w-12 h-12 flex items-center justify-center rounded-lg {{ $c['bg'] }}">
            <svg class="w-6 h-6 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $title }}</p>
            <template x-if="loading">
                <div class="h-7 w-16 bg-slate-200 dark:bg-slate-700 rounded animate-pulse mt-1"></div>
            </template>
            <template x-if="!loading">
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-slate-900 dark:text-slate-100" x-text="value ?? '—'"></p>
                    <template x-if="delta !== null">
                        <span
                            class="text-xs font-medium"
                            :class="deltaDirection === 'up' ? 'text-orange-500' : 'text-green-500'"
                            x-text="(deltaDirection === 'up' ? '↑' : '↓') + delta"
                        ></span>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>
