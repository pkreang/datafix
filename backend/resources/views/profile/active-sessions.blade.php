@extends('layouts.app')

@section('title', __('common.active_sessions'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.my_profile'), 'url' => route('profile.edit')],
        ['label' => __('common.active_sessions')],
    ]" />
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 hover:underline">&larr; {{ __('common.my_profile') }}</a>
    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2 mb-4">{{ __('common.active_sessions') }}</h2>

    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->has('session'))
        <div class="alert-error mb-4">{{ $errors->first('session') }}</div>
    @endif

    <div class="card overflow-hidden">
        @if($tokens->isEmpty())
            <div class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('common.no_active_sessions') }}</div>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                @foreach($tokens as $token)
                    @php $isCurrent = $token->id === $currentTokenId; @endphp
                    <li class="p-4 flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ \App\Services\Auth\LoginHistoryRecorder::summarizeUserAgent($token->user_agent) }}
                                </span>
                                @if($isCurrent)
                                    <span class="badge-green text-xs">{{ __('common.session_current') }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap gap-x-3 gap-y-1">
                                <span>IP: <span class="font-mono">{{ $token->ip_address ?: '—' }}</span></span>
                                <span>{{ __('common.last_used') }}: {{ $token->last_used_at?->diffForHumans() ?? '—' }}</span>
                                <span>{{ __('common.created') }}: {{ $token->created_at?->diffForHumans() ?? '—' }}</span>
                                @if($token->name !== 'web-browser')
                                    <span class="font-mono text-slate-400">[{{ $token->name }}]</span>
                                @endif
                            </div>
                        </div>
                        @unless($isCurrent)
                            <form method="POST" action="{{ route('profile.sessions.revoke', $token->id) }}"
                                  onsubmit="return confirm('{{ __('common.session_revoke_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger text-xs">{{ __('common.session_revoke') }}</button>
                            </form>
                        @endunless
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if($tokens->count() > 1)
        <form method="POST" action="{{ route('profile.sessions.revoke-others') }}" class="mt-4"
              onsubmit="return confirm('{{ __('common.session_revoke_others_confirm') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-secondary text-sm">
                {{ __('common.session_revoke_others') }}
            </button>
        </form>
    @endif
</div>
@endsection
