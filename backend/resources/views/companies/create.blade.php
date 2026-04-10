@extends('layouts.app')

@section('title', __('company.add_company'))

@section('content')
<div>
    <div class="flex items-center justify-between gap-4 mb-6">
        <nav class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('companies.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('company.companies') }}</a>
            <span class="mx-1">/</span>
            <span class="text-slate-700 dark:text-slate-300">{{ __('company.add_company') }}</span>
        </nav>
        <a href="{{ route('companies.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 shrink-0">&larr; {{ __('common.back') }}</a>
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

    <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data"
          novalidate
          class="card p-6">
        @csrf
        @include('companies._form')

        <div class="pt-6 flex flex-wrap justify-end gap-3">
            <a href="{{ route('companies.index') }}" class="btn-secondary">
                {{ __('common.cancel') }}
            </a>
            <button type="submit" class="btn-primary">
                {{ __('common.save') }}
            </button>
        </div>
    </form>
</div>
@endsection
