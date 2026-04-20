@props(['variant' => 'block'])
@php
    $base = 'animate-pulse rounded-lg bg-slate-200 dark:bg-slate-700';
@endphp
@if ($variant === 'metric')
    <div class="space-y-3 {{ $attributes->get('class') }}">
        <div class="{{ $base }} h-10 w-24"></div>
        <div class="{{ $base }} h-4 w-32"></div>
    </div>
@elseif ($variant === 'chart')
    <div class="{{ $base }} h-[200px] w-full {{ $attributes->get('class') }}"></div>
@else
    <div class="space-y-2 {{ $attributes->get('class') }}">
        @for ($i = 0; $i < 4; $i++)
            <div class="{{ $base }} h-4" style="width: {{ $i === 0 ? '85%' : ($i === 1 ? '60%' : '70%') }}"></div>
        @endfor
    </div>
@endif
