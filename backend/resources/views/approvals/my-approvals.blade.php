@extends('layouts.app')

@section('title', __('common.my_approvals'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.my_approvals') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.my_approvals_desc') }}</p>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('approval'))
        <div class="alert-error mb-4">
            {{ $errors->first('approval') }}
        </div>
    @endif

    <div class="card p-5 space-y-4">
        @forelse($instances as $instance)
            @php
                $current = $instance->steps->firstWhere('step_no', $instance->current_step_no);
                $payload = $instance->payload ?? [];
                $title = $payload['title'] ?? null;
                $detail = $payload['detail'] ?? null;
            @endphp
            <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/20 p-4 space-y-3">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $instance->reference_no ?: ('#' . $instance->id) }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            @if($instance->document_type === 'repair_request')
                                {{ __('common.doc_type_repair_request') }}
                            @elseif($instance->document_type === 'pm_am_plan')
                                {{ __('common.doc_type_pm_am_plan') }}
                            @elseif($instance->document_type === 'spare_parts_requisition')
                                {{ __('common.doc_type_spare_parts_requisition') }}
                            @elseif($instance->document_type === 'school_leave_request')
                                {{ __('common.doc_type_school_leave_request') }}
                            @elseif($instance->document_type === 'school_procurement')
                                {{ __('common.doc_type_school_procurement') }}
                            @elseif($instance->document_type === 'school_activity')
                                {{ __('common.doc_type_school_activity') }}
                            @else
                                {{ $instance->document_type }}
                            @endif
                            | {{ optional($instance->requester)->full_name ?? '—' }}
                        </p>
                        @if($title)
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mt-2">{{ $title }}</p>
                        @endif
                        @if($detail)
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 line-clamp-3 whitespace-pre-wrap">{{ $detail }}</p>
                        @endif
                        @if($current)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                {{ __('common.approval_current_step_label', ['step' => $current->step_no, 'name' => $current->stage_name]) }}
                            </p>
                            @if(($current->min_approvals ?? 1) > 1)
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                    {{ __('common.approval_progress', ['approved' => count($current->approved_by ?? []), 'required' => $current->min_approvals]) }}
                                </p>
                                @if(!empty($current->approved_by))
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($current->approved_by as $approver)
                                            <span class="badge-green">{{ $approver['name'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        @endif
                        @if($instance->formSubmission)
                            <a href="{{ route('forms.submission.show', $instance->formSubmission) }}" class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-700">{{ __('common.view_full_detail') }}</a>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('approvals.act', $instance) }}" class="space-y-2 border-t border-slate-200 dark:border-slate-600 pt-3" novalidate>
                    @csrf
                    <div>
                        <label class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.approval_comment') }}</label>
                        <textarea name="comment" rows="2" placeholder="{{ __('common.approval_comment_placeholder') }}"
                                  class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-sm"></textarea>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" name="action" value="approved" class="btn-primary">{{ __('common.approve') }}</button>
                        <button type="submit" name="action" value="rejected" class="btn-danger">{{ __('common.reject') }}</button>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('common.no_pending_approvals') }}</p>
        @endforelse
    </div>
@endsection
