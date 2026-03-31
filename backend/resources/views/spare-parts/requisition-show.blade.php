@extends('layouts.app')

@section('title', __('common.spare_parts_requisition_detail'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('spare-parts.requisition.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">{{ __('common.spare_parts_requisition_detail') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $instance->reference_no ?: ('#' . $instance->id) }}
            · {{ __('common.approval_status_' . $instance->status) }}
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left: Summary + Line Items --}}
        <div class="space-y-6">
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('common.payload_summary') }}</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('common.reference_no') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $instance->reference_no ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('common.department') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $instance->department?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('common.user') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $instance->requester?->full_name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('common.workflow_name') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $instance->workflow?->name ?? '—' }}</dd>
                    </div>
                </dl>
                @if (!empty($instance->payload) && is_array($instance->payload))
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-4 space-y-2">
                        @foreach ($instance->payload as $key => $val)
                            @if($key !== 'amount')
                                <div class="text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">{{ $fieldLabels[$key] ?? $key }}</span>
                                    <p class="text-gray-900 dark:text-gray-100 mt-0.5">{{ is_scalar($val) ? $val : json_encode($val, JSON_UNESCAPED_UNICODE) }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Line Items --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.spare_parts_items') }}</h3>
                @if($canIssue)
                    <form method="POST" action="{{ route('spare-parts.requisition.issue', $instance) }}">
                        @csrf
                @endif
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-2 py-2">{{ __('common.spare_part') }}</th>
                                <th class="px-2 py-2 text-right">{{ __('common.requested') }}</th>
                                <th class="px-2 py-2 text-right">{{ __('common.issued') }}</th>
                                <th class="px-2 py-2 text-right">{{ __('common.unit_cost') }}</th>
                                <th class="px-2 py-2 text-right">{{ __('common.subtotal') }}</th>
                                @if($canIssue)
                                    <th class="px-2 py-2 text-right">{{ __('common.issue_qty') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @php $grandTotal = 0; @endphp
                            @foreach($lineItems as $li)
                                @php $grandTotal += $li->quantity_requested * $li->unit_cost; @endphp
                                <tr class="text-gray-900 dark:text-gray-100">
                                    <td class="px-2 py-2">
                                        <span class="font-medium">{{ $li->sparePart?->code }}</span>
                                        <span class="text-gray-500 dark:text-gray-400 ml-1">{{ $li->sparePart?->name }}</span>
                                    </td>
                                    <td class="px-2 py-2 text-right">{{ number_format($li->quantity_requested, 0) }}</td>
                                    <td class="px-2 py-2 text-right {{ $li->quantity_issued >= $li->quantity_requested ? 'text-green-600 dark:text-green-400' : '' }}">
                                        {{ number_format($li->quantity_issued, 0) }}
                                    </td>
                                    <td class="px-2 py-2 text-right">{{ number_format($li->unit_cost, 2) }}</td>
                                    <td class="px-2 py-2 text-right">{{ number_format($li->quantity_requested * $li->unit_cost, 2) }}</td>
                                    @if($canIssue)
                                        <td class="px-2 py-2 text-right">
                                            @if($li->quantity_issued < $li->quantity_requested)
                                                <input type="hidden" name="issue[{{ $loop->index }}][item_id]" value="{{ $li->id }}">
                                                <input type="number" step="1" min="0"
                                                       max="{{ $li->quantity_requested - $li->quantity_issued }}"
                                                       value="{{ $li->quantity_requested - $li->quantity_issued }}"
                                                       name="issue[{{ $loop->index }}][quantity]"
                                                       class="w-20 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm text-right">
                                            @else
                                                <span class="text-green-600 dark:text-green-400 text-xs">{{ __('common.issued_complete') }}</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold text-gray-900 dark:text-gray-100 border-t border-gray-300 dark:border-gray-600">
                                <td class="px-2 py-2" colspan="{{ $canIssue ? 4 : 4 }}">{{ __('common.total') }}</td>
                                <td class="px-2 py-2 text-right">{{ number_format($grandTotal, 2) }} {{ __('common.baht') }}</td>
                                @if($canIssue)
                                    <td></td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($canIssue)
                    <button class="mt-3 px-4 py-2 bg-green-600 text-white rounded-lg text-sm">{{ __('common.issue_items') }}</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Right: Approval Steps --}}
        <div class="space-y-6">
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.approval_steps') }}</h3>
                <ol class="space-y-2 text-sm">
                    @foreach ($instance->steps as $step)
                        <li class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/20 px-3 py-2">
                            <span class="font-medium text-gray-900 dark:text-gray-100">#{{ $step->step_no }} {{ $step->stage_name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ $step->approver_type }}: {{ $step->approver_ref }}</span>
                            <div class="text-xs mt-1">
                                @if ($step->action === 'pending')
                                    <span class="text-amber-600 dark:text-amber-400">{{ __('common.approval_status_pending') }}</span>
                                @elseif ($step->action === 'approved')
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('common.approval_status_approved') }}</span>
                                @elseif ($step->action === 'rejected')
                                    <span class="text-gray-600 dark:text-gray-300">{{ __('common.approval_status_rejected') }}</span>
                                @else
                                    <span class="text-gray-600 dark:text-gray-300">{{ $step->action }}</span>
                                @endif
                            </div>
                            @if ($step->comment)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $step->comment }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>

            @if ($canAct)
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.approval_actions_title') }}</h3>
                    <form method="POST" action="{{ route('approvals.act', $instance) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.approval_comment') }}</label>
                            <textarea name="comment" rows="2" placeholder="{{ __('common.approval_comment_placeholder') }}"
                                      class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" name="action" value="approved" class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm">{{ __('common.approve') }}</button>
                            <button type="submit" name="action" value="rejected" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm">{{ __('common.reject') }}</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
