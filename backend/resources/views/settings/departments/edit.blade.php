@extends('layouts.app')

@section('title', __('common.edit') . ' ' . __('common.departments'))

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.edit') }} {{ __('common.departments') }}</h2>
        <a href="{{ route('settings.departments.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('common.edit') }} {{ __('common.departments') }}</h2>
            <form method="POST" action="{{ route('settings.departments.update', $department) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.code') }}</label>
                    <input name="code" value="{{ $department->code }}" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.name') }}</label>
                    <input name="name" value="{{ $department->name }}" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.remark') }}</label>
                    <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">{{ $department->description }}</textarea>
                </div>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">{{ __('common.update') }}</button>
            </form>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('common.workflow_binding') }}</h3>
            <div class="space-y-3">
                @forelse($documentTypes as $docType)
                    @php
                        $currentBinding = $department->workflowBindings->firstWhere('document_type', $docType);
                        $docLabel = \App\Models\DocumentType::allActive()->firstWhere('code', $docType)?->label()
                            ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $docType));
                        $options = $workflows->where('document_type', $docType);
                    @endphp
                    <div class="space-y-2">
                        <label class="text-xs text-gray-500">{{ $docLabel }}</label>
                        @if ($options->isEmpty())
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.no_workflows_for_document_type') }}</p>
                        @else
                            <form method="POST" action="{{ route('settings.departments.bindings.store', $department) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="document_type" value="{{ $docType }}">
                                <select name="workflow_id" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    @foreach ($options as $workflow)
                                        <option value="{{ $workflow->id }}" @selected(optional($currentBinding)->workflow_id === $workflow->id)>{{ $workflow->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs">{{ __('common.save') }}</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.department_workflow_bindings_no_types') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
