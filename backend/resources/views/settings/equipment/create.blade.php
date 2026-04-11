@extends('layouts.app')

@section('title', __('common.add_equipment_category'))

@section('content')
<div>
    <nav class="text-sm text-slate-500 dark:text-slate-400 mb-2">
        <span>{{ __('common.settings') }}</span>
        <span class="mx-1">/</span>
        <a href="{{ route('settings.equipment.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('common.equipment_categories') }}</a>
    </nav>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.add_equipment_category') }}</h2>
        <a href="{{ route('settings.equipment.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
    </div>

    @if ($errors->any())
        <div class="alert-error mb-4">
            <ul class="text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.equipment.store') }}" novalidate>
        @csrf
        <div class="card p-6 mb-6">
            <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-4">{{ __('common.equipment_categories') }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <label class="form-label">
                        {{ __('common.code') }} <span class="text-red-500">*</span>
                    </label>
                    <input name="code" value="{{ old('code') }}" required maxlength="50"
                           class="form-input @error('code') form-input-error @enderror" />
                    @error('code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">
                        {{ __('common.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input name="name" value="{{ old('name') }}" required maxlength="255"
                           class="form-input @error('name') form-input-error @enderror" />
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">
                        {{ __('common.remark') }}
                    </label>
                    <textarea name="description" rows="2" maxlength="1000"
                              class="form-input resize-y @error('description') form-input-error @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-form.active-toggle name="is_active" :checked="true" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-2 pb-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('settings.equipment.index') }}" class="btn-secondary">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit" class="btn-primary">
                    {{ __('common.save') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
