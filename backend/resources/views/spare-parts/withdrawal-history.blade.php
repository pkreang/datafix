@extends('layouts.app')

@section('title', __('common.spare_parts_withdrawal_history'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.spare_parts'), 'url' => route('spare-parts.stock')],
        ['label' => __('common.spare_parts_withdrawal_history')],
    ]" />
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.spare_parts_withdrawal_history') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.spare_parts_withdrawal_history_desc') }}</p>
    </div>

    <x-data-table
        :columns="[
            ['key' => 'date', 'label' => __('common.date')],
            ['key' => 'spare_part', 'label' => __('common.spare_part')],
            ['key' => 'quantity', 'label' => __('common.quantity'), 'class' => 'text-right'],
            ['key' => 'reference', 'label' => __('common.reference')],
            ['key' => 'performed_by', 'label' => __('common.performed_by')],
            ['key' => 'note', 'label' => __('common.note')],
        ]"
        :rows="$transactions"
        :disable-pagination="true"
        :empty-message="__('common.no_data')"
    >
        @foreach ($transactions as $tx)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                <td class="table-sub">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                <td class="table-primary">
                    <span class="font-medium">{{ $tx->sparePart?->code }}</span>
                    <span class="text-slate-500 dark:text-slate-400 ml-1">{{ $tx->sparePart?->name }}</span>
                </td>
                <td class="table-sub text-right">{{ number_format($tx->quantity, 0) }}</td>
                <td class="table-sub">
                    @if($tx->reference_type === 'approval_instance' && $tx->reference_id)
                        #{{ $tx->reference_id }}
                    @else
                        —
                    @endif
                </td>
                <td class="table-primary">{{ $tx->performedBy?->full_name ?? '—' }}</td>
                <td class="table-sub">{{ $tx->note ?? '—' }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <x-per-page-footer :paginator="$transactions" :perPage="$perPage" id="spare-parts-withdrawal-pagination" />
@endsection
