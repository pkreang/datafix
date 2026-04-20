@props([
    'action' => null,
    'method' => 'GET',
])

<form method="{{ $method }}" @if($action) action="{{ $action }}" @endif class="mb-5">
    <div class="flex flex-wrap items-end gap-3">
        {{ $slot }}
    </div>
</form>
