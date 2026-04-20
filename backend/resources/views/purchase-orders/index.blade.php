@extends('layouts.app')
@section('title', __('common.purchase_orders'))
@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.purchasing')],
        ['label' => __('common.purchase_orders')],
    ]" />
@endsection
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.purchase_orders') }}</h2>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Status filter tabs --}}
    <div class="flex gap-2 mb-4">
        @foreach (['' => __('common.all'), 'pending' => __('common.status_pending'), 'approved' => __('common.status_approved'), 'rejected' => __('common.status_rejected')] as $val => $label)
            <a href="{{ route('purchase-orders.index', $val !== '' ? ['status' => $val] : []) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ ($status ?? '') === $val ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-data-table
        :columns="[
            ['key' => 'reference_no', 'label' => __('common.reference_no')],
            ['key' => 'pr_reference', 'label' => __('common.pr_reference')],
            ['key' => 'status', 'label' => __('common.status')],
            ['key' => 'created_at', 'label' => __('common.created_at')],
        ]"
        :rows="$myInstances"
        :disable-pagination="true"
        :empty-message="__('common.no_purchase_orders')"
    >
        @foreach($myInstances as $instance)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                <td class="table-primary">
                    <a href="{{ route('purchase-orders.show', $instance) }}"
                       class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                        {{ $instance->reference_no ?? '#'.$instance->id }}
                    </a>
                </td>
                <td class="table-sub">{{ $instance->payload['parent_reference'] ?? '—' }}</td>
                <td class="px-4 py-2">
                    @php $s = $instance->status; @endphp
                    @if($s === 'approved')
                        <span class="badge-green">{{ __('common.approval_status_' . $s) }}</span>
                    @elseif($s === 'rejected')
                        <span class="badge-red">{{ __('common.approval_status_' . $s) }}</span>
                    @else
                        <span class="badge-yellow">{{ __('common.approval_status_' . $s) }}</span>
                    @endif
                </td>
                <td class="table-sub">{{ $instance->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <x-per-page-footer :paginator="$myInstances" :perPage="$perPage" id="purchase-orders-pagination" />
@endsection
