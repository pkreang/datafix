@extends('layouts.app')

@section('title', $form->name)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.forms_index_title'), 'url' => route('forms.index')],
        ['label' => $form->name_th ?? $form->name_en ?? $form->name],
    ]" />
@endsection

@php
    $viewer = [
        'id' => (int) (session('user.id') ?? 0),
        'can_approve' => in_array('approval.approve', session('user_permissions', []), true),
        'is_super_admin' => (bool) session('user.is_super_admin', false),
    ];
@endphp

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $form->name }}</h2>
            @if($form->description)
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $form->description }}</p>
            @endif
        </div>
        <a href="{{ route('forms.create', $form->form_key) }}" class="btn-primary">
            {{ __('common.create') }}
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="card p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">
                    {{ __('common.reference_no') }}
                </label>
                <input type="text" name="reference_no" value="{{ $filters['reference_no'] ?? '' }}" class="form-input">
            </div>
            @foreach($searchable as $field)
                @include('forms._filter-input', ['field' => $field, 'filters' => $filters])
            @endforeach
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="btn-primary">{{ __('common.search') }}</button>
            <a href="{{ route('forms.list-by-form', $form->form_key) }}" class="btn-secondary">{{ __('common.reset') }}</a>
            <label class="ml-auto inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                <input type="checkbox" name="show_cancelled" value="1" @checked($showCancelled ?? false)
                       onchange="this.form.submit()"
                       class="rounded border-slate-300 dark:border-slate-600">
                {{ __('common.show_cancelled_toggle') }}
            </label>
        </div>
    </form>

    @if($submissions->isEmpty())
        <div class="card p-10 text-center">
            <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('common.no_submissions_yet') }}</p>
        </div>
    @else
        <form method="POST" action="{{ route('forms.submissions.bulk-delete-drafts') }}"
              onsubmit="return confirm('{{ __('common.bulk_delete_confirm') }}')"
              x-data="{ selected: [], get hasSelection() { return this.selected.length > 0; } }">
            @csrf

            {{-- Bulk toolbar (shows only when something is selected) --}}
            <div x-show="hasSelection" x-cloak class="flex items-center gap-2 mb-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900 rounded-lg text-sm">
                <span class="text-amber-800 dark:text-amber-200" x-text="`{{ __('common.bulk_selected_label') }} ${selected.length}`"></span>
                <div class="flex-1"></div>
                <button type="submit" class="btn-danger text-sm">{{ __('common.action_delete_draft') }}</button>
            </div>

        <div class="card divide-y divide-slate-200 dark:divide-slate-700 overflow-visible">
            @foreach($submissions as $submission)
                @php
                    $plan = $submission->actionPlan($viewer);
                    $status = $submission->effective_status;
                    $preview = $submission->preview;
                    $rowHref = $plan['primary']['href'] ?? null;
                    $statusBadgeClass = [
                        'draft' => 'badge-yellow',
                        'pending' => 'badge-blue',
                        'approved' => 'badge-green',
                        'rejected' => 'badge-red',
                        'submitted' => 'badge-gray',
                        'cancelled' => 'badge-gray',
                    ][$status] ?? 'badge-gray';
                @endphp
                <div class="relative group {{ $rowHref ? 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50' : '' }}"
                     @if($rowHref) onclick="if(!event.target.closest('[data-row-action]') && !event.target.closest('[data-row-select]')) window.location='{{ $rowHref }}'" @endif>

                    @if($submission->status === 'draft')
                        <label data-row-select class="absolute top-4 left-4 z-10" @click.stop>
                            <input type="checkbox" name="ids[]" value="{{ $submission->id }}"
                                   x-model="selected"
                                   class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </label>
                    @endif

                    <div class="p-4 {{ $submission->status === 'draft' ? 'pl-12' : '' }} pr-4 md:pr-40">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ $submission->reference_no ?: ('#' . $submission->id) }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }}">
                                {{ __('common.approval_status_' . $status) }}
                            </span>
                        </div>
                        @if($preview)
                            <p class="text-sm text-slate-700 dark:text-slate-300 line-clamp-1 mb-1">{{ $preview }}</p>
                        @endif
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $submission->created_at->format('d M Y H:i') }}
                        </p>
                        @if($submission->latestActivity)
                            @php $la = $submission->latestActivity; @endphp
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                <span class="font-medium text-slate-500 dark:text-slate-400">{{ __('common.last_activity') }}:</span>
                                {{ __('common.activity_'.$la->action) }}
                                @if($la->user)
                                    · {{ $la->user->first_name }} {{ $la->user->last_name }}
                                @endif
                                · {{ $la->created_at->diffForHumans() }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-2 px-4 pb-3 md:pb-0 md:px-0 md:absolute md:top-1/2 md:right-4 md:-translate-y-1/2">
                        @foreach($plan['secondary'] as $action)
                            <div data-row-action class="hidden md:block">
                                @include('forms._row-action-button', ['action' => $action, 'class' => 'btn-secondary text-sm'])
                            </div>
                        @endforeach

                        @if($plan['primary'])
                            <div data-row-action>
                                @include('forms._row-action-button', ['action' => $plan['primary'], 'class' => 'btn-primary text-sm'])
                            </div>
                        @endif

                        @if(!empty($plan['menu']))
                            <div data-row-action>
                                <x-row-actions :items="$plan['menu']" />
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $submissions->links() }}
        </div>
        </form>
    @endif
@endsection
