@extends('layouts.app')

@section('title', __('common.add_pm_plan'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.cmms'), 'url' => null],
        ['label' => __('common.pm_plans'), 'url' => route('cmms.pm.plans.index')],
        ['label' => __('common.add_pm_plan')],
    ]" />
@endsection

@section('content')
<div class="w-full max-w-6xl">
    <div class="mb-6">
        <a href="{{ route('cmms.pm.plans.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2">{{ __('common.add_pm_plan') }}</h2>
    </div>

    @include('cmms.pm.plans._form')
</div>
@endsection
