@props([
    'status',
    'map' => [],
])
{{--
    Usage:
    <x-status-badge status="active" :map="['active' => 'green', 'inactive' => 'gray']" />
    <x-status-badge status="approved" :map="['approved' => 'green', 'pending' => 'yellow', 'rejected' => 'red']" />

    If no map provided, falls back to common status → color mapping.
--}}
@php
    $defaultMap = [
        'active'    => 'green',
        'inactive'  => 'gray',
        'approved'  => 'green',
        'pending'   => 'yellow',
        'rejected'  => 'red',
        'draft'     => 'gray',
        'submitted' => 'blue',
        'cancelled' => 'gray',
        'completed' => 'green',
        'in_progress' => 'blue',
        'overdue'   => 'red',
    ];

    $mergedMap = array_merge($defaultMap, $map);
    $color = $mergedMap[strtolower($status)] ?? 'gray';
    $badgeClass = "badge-{$color}";
    $label = __("common.status_{$status}",  [], app()->getLocale());
    if ($label === "common.status_{$status}") {
        $label = ucfirst(str_replace('_', ' ', $status));
    }
@endphp

<span class="{{ $badgeClass }}">{{ $label }}</span>
