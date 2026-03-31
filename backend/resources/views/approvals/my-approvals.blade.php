@extends('layouts.app')

@section('title', __('common.my_approvals'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.my_approvals') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.my_approvals_desc') }}</p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('approval'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200">
            {{ $errors->first('approval') }}
        </div>
    @endif

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
        @forelse($instances as $instance)
            @php
                $current = $instance->steps->firstWhere('step_no', $instance->current_step_no);
                $payload = $instance->payload ?? [];
                $title = $payload['title'] ?? null;
                $detail = $payload['detail'] ?? null;
            @endphp
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/20 p-4 space-y-3">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $instance->reference_no ?: ('#' . $instance->id) }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if($instance->document_type === 'repair_request')
                                {{ __('common.doc_type_repair_request') }}
                            @elseif($instance->document_type === 'pm_am_plan')
                                {{ __('common.doc_type_pm_am_plan') }}
                            @elseif($instance->document_type === 'spare_parts_requisition')
                                {{ __('common.doc_type_spare_parts_requisition') }}
                            @else
                                {{ $instance->document_type }}
                            @endif
                            | {{ optional($instance->requester)->full_name ?? '—' }}
                        </p>
                        @if($title)
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-2">{{ $title }}</p>
                        @endif
                        @if($detail)
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-3 whitespace-pre-wrap">{{ $detail }}</p>
                        @endif
                        @if($current)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                {{ __('common.approval_current_step_label', ['step' => $current->step_no, 'name' => $current->stage_name]) }}
                            </p>
                            @if(($current->min_approvals ?? 1) > 1)
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                    {{ __('common.approval_progress', ['approved' => count($current->approved_by ?? []), 'required' => $current->min_approvals]) }}
                                </p>
                                @if(!empty($current->approved_by))
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($current->approved_by as $approver)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">{{ $approver['name'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        @endif
                        @if($instance->document_type === 'repair_request')
                            <a href="{{ route('repair-requests.show', $instance) }}" class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-700">{{ __('common.view_full_detail') }}</a>
                        @elseif($instance->document_type === 'pm_am_plan')
                            <a href="{{ route('maintenance.show', $instance) }}" class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-700">{{ __('common.view_full_detail') }}</a>
                        @elseif($instance->document_type === 'spare_parts_requisition')
                            <a href="{{ route('spare-parts.requisition.show', $instance) }}" class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-700">{{ __('common.view_full_detail') }}</a>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('approvals.act', $instance) }}" class="space-y-2 border-t border-gray-200 dark:border-gray-600 pt-3">
                    @csrf
                    <div>
                        <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.approval_comment') }}</label>
                        <textarea name="comment" rows="2" placeholder="{{ __('common.approval_comment_placeholder') }}"
                                  class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"></textarea>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" name="action" value="approved" class="px-3 py-2 rounded-lg bg-green-600 text-white text-sm">{{ __('common.approve') }}</button>
                        <button type="submit" name="action" value="rejected" class="px-3 py-2 rounded-lg bg-red-600 text-white text-sm">{{ __('common.reject') }}</button>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.no_pending_approvals') }}</p>
        @endforelse
    </div>
@endsection
