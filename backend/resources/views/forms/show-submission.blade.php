@extends('layouts.app')

@section('title', $submission->form->name)

@section('content')
<div class="document-form-show-page">
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Form payload (read-only) --}}
        <div class="card p-5">
            <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ __('common.payload_summary') }}</h3>

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
                            'field'      => $field,
                            'name'       => $fName,
                            'value'      => $fValue,
                            'editorRole' => 'view_only',
                        ])
                    </div>
                @endforeach
            </x-document-form-fields-grid>
        </div>

        {{-- Approval status --}}
        @if($submission->instance)
            @php $instance = $submission->instance; @endphp
            <div class="card p-5 space-y-4">
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('common.approval_status') }}</h3>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('common.workflow_name') }}</dt>
                        <dd class="text-slate-900 dark:text-slate-100">{{ $instance->workflow?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('common.status') }}</dt>
                        <dd>
                            @if($instance->status === 'pending')
                                <span class="badge-blue">{{ __('common.approval_status_' . $instance->status) }}</span>
                            @elseif($instance->status === 'approved')
                                <span class="badge-green">{{ __('common.approval_status_' . $instance->status) }}</span>
                            @elseif($instance->status === 'rejected')
                                <span class="badge-red">{{ __('common.approval_status_' . $instance->status) }}</span>
                            @else
                                <span class="badge-gray">{{ __('common.approval_status_' . $instance->status) }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if($instance->steps->count())
                    <div class="border-t border-slate-200 dark:border-slate-600 pt-4 space-y-3">
                        <h4 class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('common.approval_steps') }}</h4>
                        @foreach($instance->steps as $step)
                            <div class="flex items-start gap-3 text-sm">
                                <span class="mt-0.5 flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $step->action === 'approved' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' :
                                       ($step->action === 'rejected' ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' :
                                        'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400') }}">
                                    {{ $step->step_no }}
                                </span>
                                <div class="flex-1">
                                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ $step->stage_name }}</p>
                                    @if($step->comment)
                                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ $step->comment }}</p>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400 dark:text-slate-500">{{ __('common.approval_status_' . $step->action) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
