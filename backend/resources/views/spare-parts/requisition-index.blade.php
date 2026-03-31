@extends('layouts.app')

@section('title', __('common.spare_parts_requisition'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.spare_parts_requisition') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.spare_parts_requisition_desc') }}</p>
        </div>
        <a href="{{ route('spare-parts.requisition.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">{{ __('common.create_requisition') }}</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <form method="GET" action="{{ route('spare-parts.requisition.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">{{ __('common.filter_by_status') }}</label>
                <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="">{{ __('common.status_all') }}</option>
                    <option value="pending" @selected(($status ?? '') === 'pending')>{{ __('common.approval_status_pending') }}</option>
                    <option value="approved" @selected(($status ?? '') === 'approved')>{{ __('common.approval_status_approved') }}</option>
                    <option value="rejected" @selected(($status ?? '') === 'rejected')>{{ __('common.approval_status_rejected') }}</option>
                </select>
            </div>
        </form>

        <div class="space-y-2">
            @forelse($myInstances as $item)
                <a href="{{ route('spare-parts.requisition.show', $item) }}" class="block rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-white dark:bg-gray-900/20 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item->reference_no ?: ('#' . $item->id) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('common.approval_status_' . $item->status) }}
                        · {{ __('common.workflow_step_short') }} {{ $item->current_step_no }}
                        @if($item->department)
                            · {{ $item->department->name }}
                        @endif
                    </p>
                </a>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.no_data') }}</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $myInstances->links() }}
        </div>
    </div>
@endsection
