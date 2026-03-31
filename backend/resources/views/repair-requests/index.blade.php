@extends('layouts.app')

@section('title', __('common.repair_request'))

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.repair_request') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.repair_request_desc') }}</p>
    </div>

    @if ($errors->has('workflow'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200">
            {{ $errors->first('workflow') }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (!empty($showAdminHints))
        <div class="mb-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-sm text-amber-900 dark:text-amber-100">
            <p class="font-medium mb-2">{{ __('common.repair_admin_setup_intro') }}</p>
            <ul class="list-disc list-inside space-y-1 text-amber-800 dark:text-amber-200">
                <li><a href="{{ route('settings.workflow.index') }}" class="underline hover:no-underline">{{ __('common.repair_admin_link_workflow') }}</a></li>
                <li><a href="{{ route('settings.document-forms.index') }}" class="underline hover:no-underline">{{ __('common.repair_admin_link_forms') }}</a></li>
                <li><a href="{{ route('settings.approval-routing') }}" class="underline hover:no-underline">{{ __('common.repair_admin_link_routing') }}</a></li>
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.submit') }}</h3>
            <form method="POST" action="{{ route('repair-requests.submit') }}" class="space-y-3">
                @csrf
                @if($form)
                    <input type="hidden" name="form_key" value="{{ $form->form_key }}">
                @endif
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.reference_no') }}</label>
                    <input name="reference_no" value="{{ old('reference_no') }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.department') }}</label>
                    <select name="department_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <option value="">{{ __('common.department_not_selected') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($form)
                    @php
                        $layoutCols = (int) ($form->layout_columns ?? 1);
                        $layoutClass = match($layoutCols) {
                            2 => 'grid grid-cols-1 md:grid-cols-2 gap-4',
                            3 => 'grid grid-cols-1 md:grid-cols-3 gap-4',
                            4 => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4',
                            default => 'grid grid-cols-1 gap-4',
                        };
                    @endphp
                    <div class="{{ $layoutClass }}">
                    @foreach($form->fields as $field)
                        @php
                            $name = "form_payload[{$field->field_key}]";
                            $value = old("form_payload.{$field->field_key}");
                            $isSection = $field->field_type === 'section';
                            $span = $isSection ? $layoutCols : (($field->col_span && $layoutCols > 1) ? min($field->col_span, $layoutCols) : 1);
                        @endphp
                        <div @if($span > 1) style="grid-column: span {{ $span }}" @endif>
                            @if(!$isSection)
                                <label class="text-sm text-gray-600 dark:text-gray-300">{{ $field->label }}</label>
                            @endif
                            @include('components.dynamic-field', ['field' => $field, 'name' => $name, 'value' => $value])
                            @if(!$isSection)
                                @error('form_payload.' . $field->field_key)
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    @endforeach
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.amount_for_workflow') }}</label>
                        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    </div>
                @endif
                @error('form_payload.title')
                    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">{{ __('common.submit') }}</button>
            </form>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('common.my_submitted_requests') }}</h3>
                @if(in_array('approval.approve', session('user_permissions', []), true))
                    <a href="{{ route('approvals.my') }}" class="text-sm text-blue-600 hover:text-blue-700 whitespace-nowrap">{{ __('common.my_approvals') }}</a>
                @endif
            </div>

            <form method="GET" action="{{ route('repair-requests.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
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
                    <a href="{{ route('repair-requests.show', $item) }}" class="block rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-white dark:bg-gray-900/20 hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
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
    </div>
@endsection
