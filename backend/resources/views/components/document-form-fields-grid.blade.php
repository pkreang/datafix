{{--
    Document form field layout: use inline grid columns so Tailwind JIT does not miss
    dynamic grid-cols-N utilities from Blade (see forms/create.blade.php).
--}}
@props(['columns' => 1])
@php
    $layoutColumns = max(1, min(4, (int) $columns));
@endphp
<div {{ $attributes->merge(['class' => 'grid gap-4']) }} style="grid-template-columns: repeat({{ $layoutColumns }}, minmax(0, 1fr))">
    {{ $slot }}
</div>
