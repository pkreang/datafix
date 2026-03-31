@extends('layouts.app')

@section('title', __('users.import_title'))

@section('content')
<div>
    <nav class="text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('users.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('common.users') }}</a>
        <span class="mx-1">/</span>
        <span>{{ __('common.import') }}</span>
    </nav>
    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ __('users.import_title') }}</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('users.import_subtitle') }}</p>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('import_errors'))
        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-2">{{ __('common.error') }}</p>
            <ul class="text-sm text-yellow-700 dark:text-yellow-400 space-y-1 list-disc list-inside">
                @foreach (session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <form method="POST" action="{{ route('users.import.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('users.import_upload_label') }}
                    </label>
                    <input type="file" name="file" accept=".csv,.txt" required
                           class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50" />
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('users.import_upload_hint') }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('users.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        {{ __('common.cancel') }}
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                        {{ __('common.import') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ __('users.import_template_title') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('users.import_template_hint') }}</p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 text-left font-medium text-gray-600 dark:text-gray-300">Column</th>
                            <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-300">Required</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 dark:text-gray-300">
                        <tr><td class="py-1.5 pr-4 font-mono">email</td><td>*</td></tr>
                        <tr><td class="py-1.5 pr-4 font-mono">first_name</td><td></td></tr>
                        <tr><td class="py-1.5 pr-4 font-mono">last_name</td><td></td></tr>
                        <tr><td class="py-1.5 pr-4 font-mono">department</td><td></td></tr>
                        <tr><td class="py-1.5 pr-4 font-mono">position</td><td></td></tr>
                        <tr><td class="py-1.5 pr-4 font-mono">phone</td><td></td></tr>
                        <tr><td class="py-1.5 pr-4 font-mono">remark</td><td></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
