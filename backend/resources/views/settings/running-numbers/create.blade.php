@extends('layouts.app')

@section('title', __('common.add') . ' ' . __('common.running_numbers'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.settings')],
        ['label' => __('common.running_numbers'), 'url' => route('settings.running-numbers.index')],
        ['label' => __('common.add')],
    ]" />
@endsection

@section('content')
    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ __('common.add') }} {{ __('common.running_numbers') }}</h2>

    @include('settings.running-numbers._form')
@endsection
