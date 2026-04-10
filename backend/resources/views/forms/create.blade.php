@extends('layouts.app')

@section('title', $form->name)

@section('content')
<div class="document-form-page">
    <div class="mb-6">
        <a href="{{ route('forms.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2">{{ $form->name }}</h2>
        @if($form->description)
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $form->description }}</p>
        @endif
    </div>

    @if($errors->any())
        <div class="alert-error mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('forms.draft.store', $form->form_key) }}" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="card p-6">
            <x-document-form-fields-grid :columns="$form->layout_columns ?? 1">
                @foreach($form->fields as $field)
                    @php
                        $fKey   = $field->field_key;
                        $fName  = "fields[{$fKey}]";
                        $fValue = old("fields.{$fKey}");
                        $fSpan  = ($field->col_span && ($form->layout_columns ?? 1) > 1)
                            ? min($field->col_span, $form->layout_columns)
                            : 1;
                    @endphp
                    <div @if($fSpan > 1) style="grid-column: span {{ $fSpan }}" @endif>
                        @if($field->field_type !== 'section')
                            <label class="form-label">
                                {{ $field->label }}
                                @if($field->is_required) <span class="text-red-500">*</span> @endif
                            </label>
                        @endif
                        @include('components.dynamic-field', [
                            'field'      => $field,
                            'name'       => $fName,
                            'value'      => $fValue,
                            'editorRole' => 'requester',
                        ])
                    </div>
                @endforeach
            </x-document-form-fields-grid>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-primary">
                    {{ __('common.save_draft') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
