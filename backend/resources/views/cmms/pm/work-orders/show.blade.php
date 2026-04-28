@extends('layouts.app')

@section('title', $workOrder->code)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.cmms'), 'url' => null],
        ['label' => __('common.pm_work_orders'), 'url' => route('cmms.pm.work-orders.index')],
        ['label' => $workOrder->code],
    ]" />
@endsection

@php
    $statusClasses = [
        'due' => 'badge-blue',
        'in_progress' => 'badge-yellow',
        'overdue' => 'badge-red',
        'done' => 'badge-green',
        'skipped' => 'badge-gray',
        'cancelled' => 'badge-gray',
    ];
    $taskTypeLabels = [
        'visual' => __('common.pm_task_type_visual'),
        'measurement' => __('common.pm_task_type_measurement'),
        'lubrication' => __('common.pm_task_type_lubrication'),
        'replacement' => __('common.pm_task_type_replacement'),
        'cleaning' => __('common.pm_task_type_cleaning'),
        'tightening' => __('common.pm_task_type_tightening'),
        'other' => __('common.pm_task_type_other'),
    ];
    $canExecute = auth()->user()?->can('pm.execute');
@endphp

@section('content')
<div class="w-full max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('cmms.pm.work-orders.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
        <div class="flex items-center justify-between mt-2">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $workOrder->code }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $workOrder->plan?->name ?? __('common.pm_wo_adhoc') }}</p>
            </div>
            <span class="{{ $statusClasses[$workOrder->status] ?? 'badge-gray' }}">{{ __('common.pm_wo_status_' . $workOrder->status) }}</span>
        </div>
    </div>

    @if(session('success'))<div class="alert-success mb-4"><p class="text-sm">{{ session('success') }}</p></div>@endif
    @if(session('error'))<div class="alert-error mb-4"><p class="text-sm">{{ session('error') }}</p></div>@endif
    @if($errors->any())
        <div class="alert-error mb-4">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Header card --}}
    <div class="card p-4 sm:p-6 mb-4">
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.equipment') }}</dt>
                <dd class="text-slate-900 dark:text-slate-100 font-medium">{{ $workOrder->equipment?->code }}</dd>
                <dd class="text-xs text-slate-500">{{ $workOrder->equipment?->name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_due_date') }}</dt>
                <dd class="text-slate-900 dark:text-slate-100">{{ $workOrder->due_date?->format('Y-m-d') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_wo_assignee') }}</dt>
                <dd class="text-slate-900 dark:text-slate-100">{{ $workOrder->assignee ? $workOrder->assignee->first_name . ' ' . $workOrder->assignee->last_name : '—' }}</dd>
            </div>
            @if($workOrder->started_at)
                <div>
                    <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_started_at') }}</dt>
                    <dd class="text-slate-900 dark:text-slate-100">{{ $workOrder->started_at->format('Y-m-d H:i') }}</dd>
                </div>
            @endif
            @if($workOrder->completed_at)
                <div>
                    <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_completed_at') }}</dt>
                    <dd class="text-slate-900 dark:text-slate-100">{{ $workOrder->completed_at->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_wo_completed_by') }}</dt>
                    <dd class="text-slate-900 dark:text-slate-100">{{ $workOrder->completedBy ? $workOrder->completedBy->first_name . ' ' . $workOrder->completedBy->last_name : '—' }}</dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- Start button (for due/overdue) --}}
    @if(in_array($workOrder->status, ['due', 'overdue'], true) && $canExecute)
        <div class="card p-4 sm:p-6 mb-4 flex items-center justify-between">
            <p class="text-sm text-slate-700 dark:text-slate-300">{{ __('common.pm_wo_ready_to_start') }}</p>
            <form method="POST" action="{{ route('cmms.pm.work-orders.start', $workOrder) }}">
                @csrf
                <button type="submit" class="btn-primary">{{ __('common.pm_wo_start_button') }}</button>
            </form>
        </div>
    @endif

    {{-- Execute checklist (in_progress) --}}
    @if($workOrder->status === 'in_progress' && $canExecute)
        <form method="POST" action="{{ route('cmms.pm.work-orders.complete', $workOrder) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @foreach($workOrder->items as $item)
                <div class="card p-4 sm:p-6 {{ $item->is_critical ? 'border-l-4 border-red-500' : '' }}">
                    <div class="flex items-start gap-3 mb-3">
                        <span class="flex-none w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sm font-semibold">{{ $item->step_no }}</span>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item->description }}</h4>
                            <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1 text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ __('common.pm_task_type') }}: <span class="text-slate-700 dark:text-slate-300">{{ $taskTypeLabels[$item->task_type] ?? $item->task_type }}</span></span>
                                @if($item->expected_value)
                                    <span>{{ __('common.pm_expected_value') }}: <span class="text-slate-700 dark:text-slate-300">{{ $item->expected_value }} {{ $item->unit }}</span></span>
                                @endif
                                @if($item->sparePart)
                                    <span>{{ __('common.pm_spare_part') }}: <span class="text-slate-700 dark:text-slate-300">{{ $item->sparePart->code }} — {{ $item->sparePart->name }}</span></span>
                                @endif
                                @if($item->estimated_minutes)
                                    <span>{{ __('common.pm_estimated_minutes') }}: <span class="text-slate-700 dark:text-slate-300">{{ $item->estimated_minutes }} {{ __('common.minutes') }}</span></span>
                                @endif
                                @if($item->loto_required)
                                    <span class="text-amber-600 dark:text-amber-400">⚠ {{ __('common.pm_loto_required') }}</span>
                                @endif
                                @if($item->is_critical)
                                    <span class="text-red-600 dark:text-red-400">★ {{ __('common.pm_is_critical') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.status') }} *</label>
                            <select name="items[{{ $loop->index }}][status]" required class="form-input mt-1 text-sm">
                                <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>{{ __('common.pm_item_status_pending') }}</option>
                                <option value="done" {{ $item->status === 'done' ? 'selected' : '' }}>{{ __('common.pm_item_status_done') }}</option>
                                <option value="skipped" {{ $item->status === 'skipped' ? 'selected' : '' }}>{{ __('common.pm_item_status_skipped') }}</option>
                                <option value="fail" {{ $item->status === 'fail' ? 'selected' : '' }}>{{ __('common.pm_item_status_fail') }}</option>
                            </select>
                        </div>

                        @if($item->task_type === 'measurement')
                            <div>
                                <label class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_actual_value') }}{{ $item->unit ? ' ('.$item->unit.')' : '' }}</label>
                                <input type="text" name="items[{{ $loop->index }}][actual_value]" value="{{ $item->actual_value }}" class="form-input mt-1 text-sm" />
                            </div>
                        @endif

                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_note') }}</label>
                            <textarea name="items[{{ $loop->index }}][note]" rows="2" maxlength="1000" class="form-input mt-1 text-sm resize-y">{{ $item->note }}</textarea>
                        </div>

                        @if($item->requires_photo)
                            <div class="md:col-span-2">
                                <label class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_photo') }} {{ $item->requires_photo ? '*' : '' }}</label>
                                @if($item->photo_path)
                                    <p class="text-xs mb-1"><a href="{{ \Illuminate\Support\Facades\Storage::url($item->photo_path) }}" target="_blank" class="text-blue-600 hover:underline">{{ __('common.pm_current_photo') }}</a></p>
                                @endif
                                <input type="file" name="items[{{ $loop->index }}][photo]" accept="image/*" class="form-input mt-1 text-sm" />
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Overall findings --}}
            <div class="card p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">{{ __('common.pm_overall_summary') }}</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-slate-500">{{ __('common.pm_findings') }}</label>
                        <textarea name="findings" rows="3" maxlength="2000" class="form-input mt-1 text-sm resize-y">{{ $workOrder->findings }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">{{ __('common.notes') }}</label>
                        <textarea name="notes" rows="2" maxlength="2000" class="form-input mt-1 text-sm resize-y">{{ $workOrder->notes }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-slate-500">{{ __('common.pm_current_runtime_hours') }}</label>
                            <input type="number" step="0.01" min="0" name="current_runtime_hours" value="{{ $workOrder->equipment->runtime_hours }}" class="form-input mt-1 text-sm" />
                            <p class="text-xs text-slate-400 mt-1">{{ __('common.pm_current_runtime_help') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <form method="POST" action="{{ route('cmms.pm.work-orders.cancel', $workOrder) }}" onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-700">{{ __('common.pm_wo_cancel') }}</button>
                </form>
                <button type="submit" class="btn-primary">{{ __('common.pm_wo_complete_button') }}</button>
            </div>
        </form>
    @endif

    {{-- Read-only display for completed/cancelled --}}
    @if(in_array($workOrder->status, ['done', 'skipped', 'cancelled'], true))
        <div class="space-y-4">
            @foreach($workOrder->items as $item)
                <div class="card p-4 sm:p-6 {{ $item->is_critical ? 'border-l-4 border-red-500' : '' }}">
                    <div class="flex items-start gap-3">
                        <span class="flex-none w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-sm font-semibold">{{ $item->step_no }}</span>
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $item->description }}</h4>
                                <span class="ml-2 text-xs font-medium px-2 py-0.5 rounded
                                    {{ $item->status === 'done' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                    {{ $item->status === 'fail' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                    {{ $item->status === 'skipped' ? 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' : '' }}
                                    {{ $item->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}">
                                    {{ __('common.pm_item_status_' . $item->status) }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1 text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ $taskTypeLabels[$item->task_type] ?? $item->task_type }}</span>
                                @if($item->actual_value)
                                    <span>{{ __('common.pm_actual_value') }}: <span class="text-slate-700 dark:text-slate-300">{{ $item->actual_value }} {{ $item->unit }}</span></span>
                                @endif
                            </div>
                            @if($item->note)
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 italic">{{ $item->note }}</p>
                            @endif
                            @if($item->photo_path)
                                <div class="mt-2">
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($item->photo_path) }}" target="_blank" class="text-xs text-blue-600 hover:underline">{{ __('common.pm_view_photo') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            @if($workOrder->findings || $workOrder->notes)
                <div class="card p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('common.pm_overall_summary') }}</h3>
                    @if($workOrder->findings)
                        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap mb-2"><strong>{{ __('common.pm_findings') }}:</strong> {{ $workOrder->findings }}</p>
                    @endif
                    @if($workOrder->notes)
                        <p class="text-sm text-slate-500 dark:text-slate-400 whitespace-pre-wrap">{{ $workOrder->notes }}</p>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
