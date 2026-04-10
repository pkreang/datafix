@extends('layouts.app')

@section('title', $submission->form->name)

@section('content')
<div class="document-form-page">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('forms.my-submissions') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.my_submissions') }}</a>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2">{{ $submission->form->name }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                <span class="badge-yellow">
                    {{ __('common.draft') }}
                </span>
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php $form = $submission->form; @endphp

    {{-- Update draft form --}}
    <form id="update-draft-form"
          method="POST"
          action="{{ route('forms.draft.update', $submission) }}"
          enctype="multipart/form-data" novalidate>
        @csrf @method('PUT')
        <div class="card p-6">
            <x-document-form-fields-grid :columns="$form->layout_columns ?? 1">
                @foreach($form->fields as $field)
                    @php
                        $fKey   = $field->field_key;
                        $fName  = "fields[{$fKey}]";
                        $fValue = old("fields.{$fKey}", $submission->payload[$fKey] ?? null);
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
        </div>
    </form>

    {{-- Action bar --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        {{-- Delete draft --}}
        <form method="POST"
              action="{{ route('forms.draft.destroy', $submission) }}"
              onsubmit="return confirm('{{ __('common.confirm_delete') }}')" novalidate>
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">
                {{ __('common.delete_draft') }}
            </button>
        </form>

        <div class="flex gap-3">
            {{-- Save draft --}}
            <button type="submit" form="update-draft-form" class="btn-secondary">
                {{ __('common.save_draft') }}
            </button>

            {{-- Submit to workflow --}}
            <form method="POST"
                  action="{{ route('forms.draft.submit', $submission) }}"
                  onsubmit="return confirm('{{ __('common.confirm_submit_form') }}')" novalidate>
                @csrf
                <button type="submit" class="btn-primary">
                    {{ __('common.submit_form') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
