@props([
    'colspan' => 1,
    'message' => null,
    'ctaHref' => null,
    'ctaLabel' => null,
])
@php
    $message = $message ?? __('common.table_empty_title');
@endphp
<tr>
    <td colspan="{{ $colspan }}" class="px-6 py-16 text-center">
        <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-500">
            <svg class="w-10 h-10 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"/>
            </svg>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $message }}</p>
            @if (filled($ctaHref) && filled($ctaLabel))
                <a href="{{ $ctaHref }}" class="btn-primary text-xs">{{ $ctaLabel }}</a>
            @endif
            {{ $slot }}
        </div>
    </td>
</tr>
