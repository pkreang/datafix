@props([
    'items' => [],
])
{{--
    Each item in $items should be an array with:
    - label     (string, required)
    - href      (string, optional — for link actions)
    - method    (string, optional — 'DELETE', 'PUT', etc. renders as form)
    - action    (string, optional — form action URL, required when method is set)
    - icon      (string, optional — 'edit', 'delete', 'view', 'toggle')
    - class     (string, optional — extra CSS e.g. 'text-red-600 dark:text-red-400')
    - confirm   (string, optional — confirmation message)
--}}

<div class="relative z-10 inline-block text-left" x-data="{ open: false }">
    <button @click="open = !open" type="button" class="table-action-btn">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
        </svg>
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 bottom-full mb-2 w-40 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-[200]">

        @foreach ($items as $item)
            @php
                $iconName = $item['icon'] ?? null;
                $class = $item['class'] ?? 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700';
                $hasMethod = isset($item['method']) && $item['method'] !== 'GET';
            @endphp

            @if($hasMethod)
                <form method="POST" action="{{ $item['action'] }}" class="block"
                      @if(isset($item['confirm'])) onsubmit="return confirm('{{ $item['confirm'] }}')" @endif
                      novalidate>
                    @csrf
                    @method($item['method'])
                    <button type="submit"
                            class="flex items-center gap-2 w-full px-4 py-2 text-sm {{ $class }} text-left">
                        @include('components._row-action-icon', ['icon' => $iconName])
                        {{ $item['label'] }}
                    </button>
                </form>
            @else
                <a href="{{ $item['href'] }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm {{ $class }}">
                    @include('components._row-action-icon', ['icon' => $iconName])
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
