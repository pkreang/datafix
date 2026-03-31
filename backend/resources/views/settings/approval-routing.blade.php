@extends('layouts.app')

@section('title', __('common.approval_routing'))

@section('content')
    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.approval_routing') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.approval_routing_subtitle') }}</p>
            </div>
            <a href="{{ route('settings.workflow.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        <div class="mb-6 p-4 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50/80 dark:bg-blue-900/20 text-sm text-gray-700 dark:text-gray-300">
            <p class="font-medium text-gray-900 dark:text-gray-100 mb-1">{{ __('common.approval_routing_hint_title') }}</p>
            <p>{{ __('common.approval_routing_per_doctype_hint') }}</p>
        </div>

        <form method="POST" action="{{ route('settings.approval-routing.save') }}">
            @csrf

            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.document_type') }}</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('common.routing_mode') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($documentTypes as $docType)
                            <tr class="hover:bg-gray-200 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $docType->label() }}</td>
                                <td class="px-5 py-3">
                                    <select name="routing_modes[{{ $docType->code }}]"
                                            class="w-full max-w-xs text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-gray-900 dark:text-gray-100">
                                        <option value="hybrid" @selected(old("routing_modes.{$docType->code}", $docType->routing_mode) === 'hybrid')>
                                            {{ __('common.routing_mode_by_department') }}
                                        </option>
                                        <option value="organization_wide" @selected(old("routing_modes.{$docType->code}", $docType->routing_mode) === 'organization_wide')>
                                            {{ __('common.routing_mode_org_wide') }}
                                        </option>
                                    </select>
                                    @error("routing_modes.{$docType->code}")
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    {{ __('common.save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
