@extends('layouts.app')

@section('title', __('common.api_tokens'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.my_profile'), 'url' => route('profile.edit')],
        ['label' => __('common.api_tokens')],
    ]" />
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 hover:underline">&larr; {{ __('common.my_profile') }}</a>
    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-2 mb-4">{{ __('common.api_tokens') }}</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('common.api_tokens_desc') }}</p>

    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if(session('new_api_token'))
        <div class="card p-4 mb-4 border-2 border-amber-400 bg-amber-50 dark:bg-amber-900/20">
            <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-2">{{ __('common.api_token_one_time_display') }}</h3>
            <div class="font-mono text-xs break-all bg-white dark:bg-slate-900 p-3 rounded border border-amber-200">
                {{ session('new_api_token') }}
            </div>
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-2">{{ __('common.api_token_save_now_warning') }}</p>
        </div>
    @endif

    {{-- Create form --}}
    <div class="card p-4 mb-4">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">{{ __('common.api_token_create') }}</h3>
        <form method="POST" action="{{ route('profile.api-tokens.create') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">{{ __('common.api_token_name') }}</label>
                <input type="text" name="name" required maxlength="64"
                       placeholder="e.g. Zapier, Postman, MyScript"
                       class="form-input">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">{{ __('common.api_token_expires_days') }}</label>
                <input type="number" name="expires_days" min="1" max="365" placeholder="—"
                       class="form-input w-32">
                <p class="text-xs text-slate-400 mt-1">{{ __('common.api_token_expires_hint') }}</p>
            </div>
            <button type="submit" class="btn-primary">{{ __('common.api_token_create') }}</button>
        </form>
    </div>

    {{-- List --}}
    <div class="card overflow-hidden">
        @if($tokens->isEmpty())
            <div class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('common.api_tokens_empty') }}</div>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($tokens as $token)
                    @php $displayName = str_starts_with($token->name, 'personal:') ? substr($token->name, 9) : $token->name; @endphp
                    <li class="p-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $displayName }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ __('common.created') }}: {{ $token->created_at?->diffForHumans() ?? '—' }}
                                @if($token->last_used_at)
                                    · {{ __('common.last_used') }}: {{ $token->last_used_at->diffForHumans() }}
                                @else
                                    · {{ __('common.api_token_never_used') }}
                                @endif
                                @if($token->expires_at)
                                    · {{ __('common.api_token_expires_at') }}: {{ $token->expires_at->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('profile.api-tokens.revoke', $token->id) }}"
                              onsubmit="return confirm('{{ __('common.api_token_revoke_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger text-xs">{{ __('common.api_token_revoke') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
