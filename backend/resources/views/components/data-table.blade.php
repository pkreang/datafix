@props([
    'columns' => [],
    'rows' => null,
    'emptyMessage' => null,
    'emptyCtaHref' => null,
    'emptyCtaLabel' => null,
    'disablePagination' => false,
])
{{--
    Usage:
    <x-data-table :columns="$columns" :rows="$items">
        @foreach ($items as $item)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                <td class="table-primary">{{ $item->name }}</td>
                <td class="table-sub">{{ $item->code }}</td>
                <td class="table-sub text-right">
                    <x-row-actions :items="[...]" />
                </td>
            </tr>
        @endforeach
    </x-data-table>

    $columns format:
    [
        ['key' => 'name', 'label' => 'Name'],
        ['key' => 'code', 'label' => 'Code'],
        ['key' => 'actions', 'label' => 'Actions', 'class' => 'text-right'],
    ]
--}}
@php
    $emptyMessage = $emptyMessage ?? __('common.table_empty_title');
    $paginator = ($rows instanceof \Illuminate\Pagination\AbstractPaginator) ? $rows : null;
    $isEmpty = $rows !== null && (is_countable($rows) ? count($rows) === 0 : $rows->isEmpty());
@endphp

<div class="table-wrapper">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
        <thead class="bg-slate-50 dark:bg-slate-800/60">
            <tr>
                @foreach ($columns as $col)
                    <th class="table-header {{ $col['class'] ?? '' }}">
                        {{ $col['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            @if($isEmpty)
                <x-table-empty-state
                    :colspan="count($columns)"
                    :message="$emptyMessage"
                    :cta-href="$emptyCtaHref"
                    :cta-label="$emptyCtaLabel" />
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>

@if($paginator && $paginator->hasPages() && ! $disablePagination)
    <div class="mt-4">
        {{ $paginator->withQueryString()->links() }}
    </div>
@endif
