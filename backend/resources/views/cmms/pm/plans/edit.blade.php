@extends('layouts.app')

@section('title', __('common.edit_pm_plan'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.cmms'), 'url' => null],
        ['label' => __('common.pm_plans'), 'url' => route('cmms.pm.plans.index')],
        ['label' => $plan->name],
    ]" />
@endsection

@section('content')
<div class="w-full max-w-6xl">
    <div class="mb-6">
        <a href="{{ route('cmms.pm.plans.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2">{{ $plan->name }}</h2>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $plan->equipment?->code }} — {{ $plan->equipment?->name }}</p>
    </div>

    @if(session('success'))<div class="alert-success mb-4"><p class="text-sm">{{ session('success') }}</p></div>@endif
    @if(session('error'))<div class="alert-error mb-4"><p class="text-sm">{{ session('error') }}</p></div>@endif

    {{-- Generate WO now (utility for testing + on-demand ad-hoc PM) --}}
    @if($plan->is_active && $plan->taskItems->count() > 0)
        <div class="card p-4 mb-4 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('common.pm_generate_wo_now') }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('common.pm_generate_wo_help') }}</p>
            </div>
            <form method="POST" action="{{ route('cmms.pm.plans.generate-wo', $plan) }}">
                @csrf
                <button type="submit" class="btn-secondary">{{ __('common.pm_generate_wo_now_button') }}</button>
            </form>
        </div>
    @endif

    @include('cmms.pm.plans._form')
</div>
@endsection
