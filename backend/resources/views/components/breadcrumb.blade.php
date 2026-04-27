@props(['items' => []])

@php
    $rawItems = collect($items)->filter(fn ($i) => ! empty($i['label']))->values();

    $homeUrl = null;
    foreach (['dashboard', 'home'] as $candidate) {
        try { $homeUrl = route($candidate); break; } catch (\Throwable $e) { /* fall through */ }
    }
    $homeUrl = $homeUrl ?? url('/');

    $startsWithHome = $rawItems->isNotEmpty() && ($rawItems->first()['url'] ?? null) === $homeUrl;
    // Auto-prepend Home only when the caller built a trail of 3+ items.
    // Top-level (1) and section-level (2) trails render their own intermediate
    // as the visible root — Dashboard is already reachable from the sidebar
    // and the page <h1>, so an extra "Dashboard /" prefix is just noise.
    $shouldPrepend = $rawItems->count() >= 3 && ! $startsWithHome;

    $trail = $shouldPrepend
        ? $rawItems->prepend(['label' => __('common.dashboard'), 'url' => $homeUrl])
        : $rawItems;

    $last = $trail->count() - 1;
@endphp

<nav aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-y-1">
        @foreach($trail as $idx => $item)
            <li class="flex items-center">
                @if($idx < $last && ! empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $item['label'] }}</a>
                @else
                    <span class="text-slate-700 dark:text-slate-300" @if($idx === $last) aria-current="page" @endif>{{ $item['label'] }}</span>
                @endif
                @if($idx < $last)
                    <span class="mx-1.5 text-slate-400 dark:text-slate-500" aria-hidden="true">/</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
