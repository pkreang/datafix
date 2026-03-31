@extends('layouts.app')

@php
    $isEdit = isset($documentType);
@endphp

@section('title', $isEdit ? __('common.edit_document_type') : __('common.add_document_type'))

@section('content')
<div>
    <nav class="text-sm text-gray-500 dark:text-gray-400 mb-2">
        <span>{{ __('common.settings') }}</span>
        <span class="mx-1">/</span>
        <a href="{{ route('settings.document-types.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('common.document_types') }}</a>
    </nav>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ $isEdit ? __('common.edit_document_type') : __('common.add_document_type') }}
        </h2>
        <a href="{{ route('settings.document-types.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('settings.document-types.update', $documentType) : route('settings.document-types.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.code') }} <span class="text-red-500">*</span>
                    </label>
                    <input name="code" value="{{ old('code', $documentType->code ?? '') }}" required maxlength="100"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('code') border-red-400 @enderror" />
                    @error('code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.icon') }}
                    </label>
                    <input name="icon" value="{{ old('icon', $documentType->icon ?? '') }}" maxlength="50"
                           placeholder="e.g. wrench, cube, clipboard-document-check"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.label') }} (EN) <span class="text-red-500">*</span>
                    </label>
                    <input name="label_en" value="{{ old('label_en', $documentType->label_en ?? '') }}" required maxlength="255"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('label_en') border-red-400 @enderror" />
                    @error('label_en')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.label') }} (TH) <span class="text-red-500">*</span>
                    </label>
                    <input name="label_th" value="{{ old('label_th', $documentType->label_th ?? '') }}" required maxlength="255"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('label_th') border-red-400 @enderror" />
                    @error('label_th')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.sort_order') }}</label>
                    <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $documentType->sort_order ?? 0) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                </div>
                <div class="flex items-end pb-1">
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               @checked(old('is_active', $documentType->is_active ?? true))
                               class="rounded border-gray-300 dark:border-gray-600" />
                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">{{ __('common.active') }}</label>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.description') }}</label>
                    <textarea name="description" rows="2" maxlength="1000" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm resize-y bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description', $documentType->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-2 pb-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('settings.document-types.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg">{{ __('common.cancel') }}</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">{{ __('common.save') }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
