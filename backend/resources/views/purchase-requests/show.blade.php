@extends('layouts.app')
@section('title', __('common.purchase_request') . ' ' . ($instance->reference_no ?? '#'.$instance->id))
@section('content')
    <div class="mb-6 flex items-start justify-between">
        <div>
            <a href="{{ route('purchase-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">
                {{ __('common.purchase_request') }}: {{ $instance->reference_no ?? '#'.$instance->id }}
            </h2>
        </div>
        <div class="flex items-center gap-3">
            @php $s = $instance->status; @endphp
            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium
                {{ $s === 'approved' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' :
                  ($s === 'rejected' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' :
                   'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
                {{ __('common.approval_status_' . $s) }}
            </span>
            @if($canCreatePo)
                <a href="{{ route('purchase-orders.create', ['from_pr' => $instance->id]) }}"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                    {{ __('common.create_po_from_pr') }}
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left: Document form fields --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])

            <div class="space-y-3">
                <div class="flex justify-between gap-4 text-sm">
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('common.reference_no') }}</dt>
                    <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $instance->reference_no ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('common.department') }}</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $instance->department?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('common.user') }}</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $instance->requester?->full_name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('common.workflow_name') }}</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $instance->workflow?->name ?? '—' }}</dd>
                </div>
            </div>

            @if($formFields->count())
                <div class="border-t border-gray-200 dark:border-gray-600 mt-4 pt-4">
                    @if($editorRole !== 'view_only')
                        <form method="POST" action="{{ route('approvals.update-fields', $instance) }}">
                            @csrf @method('PATCH')
                    @endif
                    <div class="grid grid-cols-{{ $formForLabels->layout_columns ?? 1 }} gap-4">
                        @foreach($formFields as $field)
                            @php
                                $fValue = $instance->payload[$field->field_key] ?? null;
                                $fName  = "field_updates[{$field->field_key}]";
                                $fSpan  = ($field->col_span && ($formForLabels->layout_columns ?? 1) > 1)
                                    ? min($field->col_span, $formForLabels->layout_columns)
                                    : 1;
                            @endphp
                            <div @if($fSpan > 1) style="grid-column: span {{ $fSpan }}" @endif>
                                @if($field->field_type !== 'section')
                                    <label class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                                        {{ $field->label }}
                                        @if($field->is_required) <span class="text-red-500">*</span> @endif
                                    </label>
                                @endif
                                @include('components.dynamic-field', [
                                    'field'      => $field,
                                    'name'       => $fName,
                                    'value'      => $fValue,
                                    'userDeptId' => $userDeptId,
                                    'editorRole' => $editorRole,
                                ])
                            </div>
                        @endforeach
                    </div>
                    @if($editorRole !== 'view_only')
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">{{ __('common.save_fields') }}</button>
                        </div>
                        </form>
                    @endif
                </div>
            @elseif(!empty($instance->payload) && is_array($instance->payload))
                <div class="border-t border-gray-200 dark:border-gray-600 mt-4 pt-4 space-y-2">
                    @foreach ($instance->payload as $key => $val)
                        @if($key !== 'amount')
                            <div class="text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ $key }}</span>
                                <p class="text-gray-900 dark:text-gray-100 mt-0.5">{{ is_scalar($val) ? $val : json_encode($val, JSON_UNESCAPED_UNICODE) }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Right: Approval steps --}}
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
                                    <span class="text-green-600 dark:text-green-400">{{ __('common.approval_status_approved') }}</span>
                                @elseif ($step->action === 'rejected')
                                    <span class="text-red-600 dark:text-red-400">{{ __('common.approval_status_rejected') }}</span>
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
                            <button type="submit" name="action" value="approved"
                                    class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm">{{ __('common.approve') }}</button>
                            <button type="submit" name="action" value="rejected"
                                    class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm">{{ __('common.reject') }}</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Line items --}}
    <div class="mt-6 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('common.line_items') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">{{ __('common.item_name') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.qty') }}</th>
                    <th class="px-4 py-2 text-left">{{ __('common.unit_label') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.unit_price') }}</th>
                    <th class="px-4 py-2 text-right">{{ __('common.total_price') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($lineItems as $i => $item)
                    <tr>
                        <td class="px-4 py-2 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-2">
                            {{ $item->item_name }}
                            @if($item->notes)
                                <span class="text-xs text-gray-400 block">{{ $item->notes }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">{{ number_format($item->qty, 2) }}</td>
                        <td class="px-4 py-2">{{ $item->unit }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-right font-medium">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right font-semibold text-gray-700 dark:text-gray-300">{{ __('common.total_price') }}</td>
                    <td class="px-4 py-2 text-right font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($lineItems->sum('total_price'), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
