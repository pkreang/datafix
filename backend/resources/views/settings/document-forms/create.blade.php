@extends('layouts.app')

@section('title', __('common.add') . ' ' . __('common.document_forms'))

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.add') }} {{ __('common.document_forms') }}</h2>
        <a href="{{ route('settings.document-forms.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
    </div>
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        @include('settings.document-forms._form')
    </div>
@endsection
