@extends('layouts.app')
@section('title', __('common.purchase_orders'))
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.purchase_orders') }}</h2>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Status filter tabs --}}
    <div class="flex gap-2 mb-4">
        @foreach (['' => __('common.all'), 'pending' => __('common.status_pending'), 'approved' => __('common.status_approved'), 'rejected' => __('common.status_rejected')] as $val => $label)
            <a href="{{ route('purchase-orders.index', $val !== '' ? ['status' => $val] : []) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ ($status ?? '') === $val ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($myInstances->isEmpty())
            <p class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">{{ __('common.no_purchase_orders') }}</p>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('common.reference_no') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.pr_reference') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('common.created_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($myInstances as $instance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('purchase-orders.show', $instance) }}"
                                   class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                    {{ $instance->reference_no ?? '#'.$instance->id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $instance->payload['parent_reference'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @php $s = $instance->status; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $s === 'approved' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' :
                                      ($s === 'rejected' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' :
                                       'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
                                    {{ __('common.approval_status_' . $s) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $instance->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $myInstances->links() }}
            </div>
        @endif
    </div>
@endsection
