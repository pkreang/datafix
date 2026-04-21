@extends('layouts.app')

@section('title', $submission->form->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.forms_index_title'), 'url' => route('forms.index')],
        ['label' => __('common.my_submissions'), 'url' => route('forms.my-submissions')],
        ['label' => __('common.view')],
    ]" />
@endsection

@section('content')
<div style="width:100%;max-width:100%">
    <div class="mb-6">
        <a href="{{ route('forms.my-submissions') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.my_submissions') }}</a>
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2">{{ $submission->form->name }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            {{ $submission->reference_no ?: ('#' . $submission->id) }}
            @if($submission->instance)
                · {{ __('common.approval_status_' . $submission->instance->status) }}
            @endif
        </p>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($submission->trashed())
        @php $submission->load('deleter'); @endphp
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20 px-4 py-3 text-sm text-red-800 dark:text-red-200">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 19h14.14a2 2 0 001.73-3L13.73 4.99a2 2 0 00-3.46 0L3.2 16A2 2 0 004.93 19z"/></svg>
                <div>
                    <p class="font-medium">{{ __('common.approval_status_cancelled') }}</p>
                    <p class="mt-0.5">
                        {{ __('common.submission_cancelled_banner', [
                            'at' => $submission->deleted_at?->format('d M Y H:i') ?? '—',
                            'by' => $submission->deleter ? trim(($submission->deleter->first_name ?? '').' '.($submission->deleter->last_name ?? '')) : __('common.system'),
                        ]) }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Workflow status — compact horizontal bar on top --}}
    @if($submission->instance)
        @php $instance = $submission->instance; @endphp
        <div class="card p-4 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-3">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $instance->workflow?->name ?? '—' }}</h3>
                    @if($instance->status === 'pending')
                        <span class="badge-blue">{{ __('common.approval_status_' . $instance->status) }}</span>
                    @elseif($instance->status === 'approved')
                        <span class="badge-green">{{ __('common.approval_status_' . $instance->status) }}</span>
                    @elseif($instance->status === 'rejected')
                        <span class="badge-red">{{ __('common.approval_status_' . $instance->status) }}</span>
                    @else
                        <span class="badge-gray">{{ __('common.approval_status_' . $instance->status) }}</span>
                    @endif
                </div>
            </div>

            @if($instance->steps->count())
                <div class="flex flex-wrap items-center gap-2">
                    @foreach($instance->steps as $step)
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm
                            {{ $step->action === 'approved' ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' :
                               ($step->action === 'rejected' ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' :
                                'bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-400') }}">
                            <span class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $step->action === 'approved' ? 'bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200' :
                                   ($step->action === 'rejected' ? 'bg-red-200 dark:bg-red-800 text-red-800 dark:text-red-200' :
                                    'bg-slate-300 dark:bg-slate-600 text-slate-700 dark:text-slate-300') }}">
                                {{ $step->step_no }}
                            </span>
                            <span class="font-medium">{{ $step->stage_name }}</span>
                            @if(($step->min_approvals ?? 1) > 1)
                                <span class="opacity-75">({{ count($step->approved_by ?? []) }}/{{ $step->min_approvals }})</span>
                            @endif
                            <span class="text-xs opacity-75">{{ __('common.approval_status_' . $step->action) }}</span>
                        </div>
                        @if(!$loop->last)
                            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Activity log (audit trail) --}}
    @if(isset($activity) && $activity->isNotEmpty())
        <div class="card p-4 mb-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">{{ __('common.submission_activity') }}</h3>
            <ul class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                @foreach($activity as $log)
                    <li class="py-2 flex items-center justify-between gap-3">
                        <div>
                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ __('common.activity_'.$log->action) }}</span>
                            @if($log->user)
                                <span class="text-slate-500 dark:text-slate-400"> — {{ $log->user->full_name }}</span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form payload (read-only) — full width --}}
    <div class="card p-4 sm:p-6 lg:p-8">
        @php $form = $submission->form; @endphp
        <x-document-form-fields-grid :columns="$form->layout_columns ?? 1">
            @foreach($form->fields as $field)
                @php
                    $fKey   = $field->field_key;
                    $fName  = "fields[{$fKey}]";
                    $fValue = $submission->payload[$fKey] ?? null;
                    $fSpan  = ($field->col_span && ($form->layout_columns ?? 1) > 1)
                        ? min($field->col_span, $form->layout_columns)
                        : 1;
                @endphp
                <div @if($fSpan > 1) style="grid-column: span {{ $fSpan }}" @endif>
                    @if($field->field_type !== 'section')
                        <label class="block text-sm text-slate-500 dark:text-slate-400 mb-1">
                            {{ $field->label }}
                        </label>
                    @endif
                    @include('components.dynamic-field', [
                        'field'       => $field,
                        'name'        => $fName,
                        'value'       => $fValue,
                        'editorRole'  => 'view_only',
                        'referenceNo' => $submission->reference_no,
                    ])
                </div>
            @endforeach
        </x-document-form-fields-grid>
    </div>
</div>
@endsection
