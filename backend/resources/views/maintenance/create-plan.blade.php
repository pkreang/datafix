@extends('layouts.app')

@section('title', __('common.create_pm_am_plan'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('maintenance.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">{{ __('common.create_pm_am_plan') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.create_pm_am_plan_desc') }}</p>
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

    <div class="max-w-2xl">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.submit') }}</h3>
            <form method="POST" action="{{ route('maintenance.create-plan.submit') }}" class="space-y-3">
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
                            @if($field->field_key === 'equipment_id')
                                <select name="{{ $name }}" @required($field->is_required) class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <option value="">{{ __('common.please_select') }}</option>
                                    @foreach($equipmentList as $eq)
                                        <option value="{{ $eq->id }}" @selected($value == $eq->id)>[{{ $eq->code }}] {{ $eq->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                @include('components.dynamic-field', ['field' => $field, 'name' => $name, 'value' => $value])
                            @endif
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
    </div>
@endsection
