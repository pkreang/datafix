@php
    $cssClass = $class ?? 'btn-secondary text-sm';
    $label = $action['label'];
    $target = $action['target'] ?? null;
    $method = $action['method'] ?? null;
@endphp

@if($method && $method !== 'GET')
    <form method="POST" action="{{ $action['action'] }}" class="inline"
          @if(!empty($action['confirm'])) onsubmit="return confirm('{{ $action['confirm'] }}')" @endif>
        @csrf
        @method($method)
        <button type="submit" class="{{ $cssClass }}">{{ $label }}</button>
    </form>
@else
    <a href="{{ $action['href'] }}" @if($target) target="{{ $target }}" @endif class="{{ $cssClass }}">
        {{ $label }}
    </a>
@endif
