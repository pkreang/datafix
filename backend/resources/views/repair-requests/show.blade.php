@extends('layouts.app')

@section('title', __('common.repair_request_detail'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('repair-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">{{ __('common.repair_request_detail') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $instance->reference_no ?: ('#' . $instance->id) }}
                · {{ __('common.approval_status_' . $instance->status) }}
            </p>
        </div>
        @if($instance->status === 'approved')
            <a href="{{ route('spare-parts.requisition.create', ['parent_type' => 'repair_request', 'parent_id' => $instance->id]) }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">
                {{ __('common.request_spare_parts') }}
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                        <div class="text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ $fieldLabels[$key] ?? $key }}</span>
                            <p class="text-gray-900 dark:text-gray-100 mt-0.5 whitespace-pre-wrap">{{ is_scalar($val) ? $val : json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

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
                                    @if ($step->actor)
                                        · {{ $step->actor->full_name }}
                                    @endif
                                    @if ($step->acted_at)
                                        · {{ $step->acted_at->format('Y-m-d H:i') }}
                                    @endif
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
