@extends('layouts.app')

@section('title', __('common.login_history'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.my_profile'), 'url' => route('profile.edit')],
        ['label' => __('common.login_history')],
    ]" />
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 hover:underline">&larr; {{ __('common.my_profile') }}</a>
    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2 mb-4">{{ __('common.login_history') }}</h2>

    <div class="card overflow-hidden">
        @if($entries->isEmpty())
            <div class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('common.login_history_empty') }}</div>
        @else
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-2">{{ __('common.date') ?? 'เมื่อ' }}</th>
                        <th class="px-4 py-2">{{ __('common.status') }}</th>
                        <th class="px-4 py-2">IP</th>
                        <th class="px-4 py-2">Device</th>
                        <th class="px-4 py-2">Provider</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($entries as $entry)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300">
                                {{ $entry->created_at->format('d M Y H:i') }}
                                <span class="text-xs text-slate-400 ml-1">({{ $entry->created_at->diffForHumans() }})</span>
                            </td>
                            <td class="px-4 py-2">
                                @if($entry->result === 'success')
                                    <span class="badge-green">{{ __('common.login_result_success') }}</span>
                                @else
                                    <span class="badge-red">{{ __('common.login_result_failed') }}</span>
                                    @if($entry->failure_reason)
                                        <span class="text-xs text-slate-400 ml-1">· {{ $entry->failure_reason }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-2 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $entry->ip_address ?: '—' }}</td>
                            <td class="px-4 py-2 text-slate-600 dark:text-slate-400">
                                {{ \App\Services\Auth\LoginHistoryRecorder::summarizeUserAgent($entry->user_agent) }}
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-500">{{ $entry->auth_provider ?: 'local' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
