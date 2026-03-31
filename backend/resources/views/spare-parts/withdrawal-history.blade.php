@extends('layouts.app')

@section('title', __('common.spare_parts_withdrawal_history'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.spare_parts_withdrawal_history') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.spare_parts_withdrawal_history_desc') }}</p>
    </div>

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-2">{{ __('common.date') }}</th>
                        <th class="px-3 py-2">{{ __('common.spare_part') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('common.quantity') }}</th>
                        <th class="px-3 py-2">{{ __('common.reference') }}</th>
                        <th class="px-3 py-2">{{ __('common.performed_by') }}</th>
                        <th class="px-3 py-2">{{ __('common.note') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($transactions as $tx)
                        <tr class="text-gray-900 dark:text-gray-100">
                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-3 py-2">
                                <span class="font-medium">{{ $tx->sparePart?->code }}</span>
                                <span class="text-gray-500 dark:text-gray-400 ml-1">{{ $tx->sparePart?->name }}</span>
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format($tx->quantity, 0) }}</td>
                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
                                @if($tx->reference_type === 'approval_instance' && $tx->reference_id)
                                    #{{ $tx->reference_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $tx->performedBy?->full_name ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $tx->note ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">{{ __('common.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
