@extends('layouts.app')

@section('title', __('company.edit_company'))

@section('content')
<div>
    <div class="flex items-center justify-between gap-4 mb-6">
        <nav class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('companies.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('company.companies') }}</a>
            <span class="mx-1">/</span>
            <span class="text-slate-700 dark:text-slate-300">{{ __('company.edit_company') }}</span>
        </nav>
        <a href="{{ route('companies.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 shrink-0">&larr; {{ __('common.back') }}</a>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert-error mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error mb-4">
            <ul class="text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data"
          novalidate
          class="card p-6">
        @csrf
        @method('PUT')
        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ __('company.company_info_section') }}</h3>
        @include('companies._form', ['company' => $company])

        <div class="pt-6 flex flex-wrap justify-end gap-3">
            <a href="{{ route('companies.index') }}" class="btn-secondary">
                {{ __('common.cancel') }}
            </a>
            <button type="submit" class="btn-primary">
                {{ __('common.save') }}
            </button>
        </div>
    </form>

    @if ($branchesManagementEnabled ?? true)
        <div class="card p-6 mt-6">
            @include('companies._branches', ['company' => $company])
        </div>
    @else
        <div class="mt-6 p-4 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 text-sm text-slate-600 dark:text-slate-400">
            {{ __('company.branches_section_hidden_hint') }}
        </div>
    @endif
</div>
@endsection
