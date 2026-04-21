@extends('layouts.app')

@section('title', __('common.submission_history_title').' · '.$submission->form->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.forms_index_title'), 'url' => route('forms.index')],
        ['label' => $submission->form->name, 'url' => route('forms.list-by-form', $submission->form)],
        ['label' => $submission->reference_no ?: ('#'.$submission->id), 'url' => route('forms.submission.show', $submission)],
        ['label' => __('common.submission_history_title')],
    ]" />
@endsection

@section('content')
<div style="width:100%;max-width:900px">
    <div class="mb-6">
        <a href="{{ route('forms.submission.show', $submission) }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.view') }}</a>
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2">{{ __('common.submission_history_title') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            {{ $submission->form->name }}
            · {{ $submission->reference_no ?: ('#'.$submission->id) }}
            @if($submission->instance)
                · {{ __('common.approval_status_'.$submission->instance->status) }}
            @endif
        </p>
    </div>

    <div class="card p-0 overflow-hidden">
        @if($activities->isEmpty())
            <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                {{ __('common.submission_history_empty') }}
            </div>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($activities as $log)
                    <li class="flex items-start gap-4 p-4">
                        <div class="shrink-0 mt-0.5 w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ __('common.activity_'.$log->action) }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                @if($log->user)
                                    {{ $log->user->full_name ?? trim($log->user->first_name.' '.$log->user->last_name) }}
                                @else
                                    {{ __('common.system') }}
                                @endif
                                · {{ $log->created_at->format('d M Y H:i:s') }}
                                · {{ $log->created_at->diffForHumans() }}
                            </p>
                            @if(! empty($log->meta))
                                <dl class="mt-2 text-xs text-slate-500 dark:text-slate-400 space-y-0.5">
                                    @foreach($log->meta as $key => $value)
                                        <div class="flex gap-2">
                                            <dt class="font-mono text-slate-400 dark:text-slate-500 shrink-0">{{ $key }}:</dt>
                                            <dd class="truncate">{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if($activities->hasPages())
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    @endif
</div>
@endsection
