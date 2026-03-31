@extends('layouts.app')

@section('title', __('common.add') . ' ' . __('common.running_numbers'))

@section('content')
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('common.add') }} {{ __('common.running_numbers') }}</h2>

    @include('settings.running-numbers._form')
@endsection
