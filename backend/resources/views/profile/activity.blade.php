@extends('layouts.app')

@section('title', __('common.activity_page_title'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.profile'), 'url' => route('profile.edit')],
        ['label' => __('common.activity_page_title')],
    ]" />
@endsection

@section('content')
<div style="width:100%;max-width:100%">
    <div class="mb-6">
        <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.profile') }}</a>
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2">{{ __('common.activity_page_title') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.activity_page_subtitle') }}</p>
    </div>

    {{-- Filter chips --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        @foreach(['all', 'submission', 'login'] as $kind)
            <a href="{{ route('profile.activity', ['kind' => $kind]) }}"
               class="px-3 py-1 rounded-full text-xs font-medium {{ ($kindFilter ?? 'all') === $kind
                    ? 'bg-blue-600 text-white'
                    : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                {{ __('common.activity_filter_'.$kind) }}
            </a>
        @endforeach
    </div>

    <div class="card p-0 overflow-hidden">
        @if($items->isEmpty())
            <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                {{ __('common.activity_page_empty') }}
            </div>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($items as $item)
                    <li class="flex items-start gap-4 p-4">
                        <div class="shrink-0 mt-0.5 w-9 h-9 rounded-full flex items-center justify-center
                            {{ $item['kind'] === 'login' ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' }}">
                            @if($item['kind'] === 'login')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ __('common.activity_'.$item['action']) }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                @if($item['href'])
                                    <a href="{{ $item['href'] }}" class="text-blue-600 hover:underline">{{ $item['subject'] }}</a>
                                @else
                                    <span class="font-mono">{{ $item['subject'] }}</span>
                                @endif
                                @if(! empty($item['subject_secondary']))
                                    <span class="text-slate-400">· {{ $item['subject_secondary'] }}</span>
                                @endif
                                · {{ $item['when']?->format('d M Y H:i') ?? '—' }}
                                · <span class="text-slate-400">{{ $item['when']?->diffForHumans() }}</span>
                            </p>
                            @if(! empty($item['meta']) && is_array($item['meta']))
                                @php
                                    // Compact meta render — diff details are already shown on submission-history; here we keep it short.
                                    $simple = collect($item['meta'])
                                        ->reject(fn ($v, $k) => $k === 'changed_fields' || $k === '_truncated')
                                        ->take(3);
                                @endphp
                                @if($simple->isNotEmpty())
                                    <dl class="mt-1 text-xs text-slate-400 dark:text-slate-500 flex flex-wrap gap-x-3 gap-y-0.5">
                                        @foreach($simple as $k => $v)
                                            <div class="flex gap-1">
                                                <dt class="font-mono">{{ $k }}:</dt>
                                                <dd>{{ is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE) }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                @endif
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
