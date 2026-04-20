@extends('layouts.app')

@section('title', __('common.spare_parts_stock'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.spare_parts_stock')],
    ]" />
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.spare_parts_stock') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.spare_parts_stock_desc') }}</p>
    </div>

    <x-filter-bar :action="route('spare-parts.stock')">
        <div>
            <label class="form-label">{{ __('common.search') }}</label>
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   placeholder="{{ __('common.search_spare_parts') }}" class="form-input">
        </div>
        <button class="btn-primary">{{ __('common.search') }}</button>
    </x-filter-bar>

    <x-data-table
        :columns="[
            ['key' => 'code', 'label' => __('common.code')],
            ['key' => 'name', 'label' => __('common.name')],
            ['key' => 'category', 'label' => __('common.category')],
            ['key' => 'unit', 'label' => __('common.unit')],
            ['key' => 'current_stock', 'label' => __('common.current_stock'), 'class' => 'text-right'],
            ['key' => 'min_stock', 'label' => __('common.min_stock'), 'class' => 'text-right'],
            ['key' => 'unit_cost', 'label' => __('common.unit_cost'), 'class' => 'text-right'],
        ]"
        :rows="$parts"
        :disable-pagination="true"
        :empty-message="__('common.no_data')"
    >
        @foreach ($parts as $part)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                <td class="table-primary">{{ $part->code }}</td>
                <td class="table-primary">{{ $part->name }}</td>
                <td class="table-sub">{{ $part->category?->name ?? '—' }}</td>
                <td class="table-sub">{{ $part->unit }}</td>
                <td class="px-4 py-2 text-right text-sm {{ $part->current_stock <= $part->min_stock ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-slate-900 dark:text-slate-100' }}">
                    {{ number_format($part->current_stock, 0) }}
                </td>
                <td class="table-sub text-right">{{ number_format($part->min_stock, 0) }}</td>
                <td class="table-sub text-right">{{ number_format($part->unit_cost, 2) }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <x-per-page-footer :paginator="$parts" :perPage="$perPage" id="spare-parts-stock-pagination" />
@endsection
