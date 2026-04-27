@extends('layouts.app')

@section('title', __('common.system_change_log_title'))

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => __('common.settings'), 'url' => '#'],
        ['label' => __('common.system_change_log_title')],
    ]" />
@endsection

@section('content')
<div style="width:100%;max-width:100%">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('common.system_change_log_title') }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('common.system_change_log_subtitle') }}</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('settings.system-change-log') }}" class="card p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="text-xs text-slate-500">{{ __('common.entity_type') }}</label>
            <select name="entity_type" class="form-input mt-1 text-sm">
                <option value="">{{ __('common.all') }}</option>
                @foreach($entityTypes as $type)
                    <option value="{{ $type }}" @selected($filters['entityType'] === $type)>{{ __('common.entity_type_'.$type) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('common.actor') }}</label>
            <select name="actor_user_id" class="form-input mt-1 text-sm">
                <option value="">{{ __('common.all') }}</option>
                @foreach($actors as $a)
                    <option value="{{ $a->id }}" @selected($filters['actorId'] === $a->id)>{{ trim($a->first_name.' '.$a->last_name) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('common.from') }}</label>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="form-input mt-1 text-sm">
        </div>
        <div>
            <label class="text-xs text-slate-500">{{ __('common.to') }}</label>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="form-input mt-1 text-sm">
        </div>
        <div class="md:col-span-4 flex justify-end gap-2">
            <a href="{{ route('settings.system-change-log') }}" class="btn-secondary text-sm">{{ __('common.reset') }}</a>
            <button type="submit" class="btn-primary text-sm">{{ __('common.filter') }}</button>
        </div>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        @if($logs->isEmpty())
            <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                {{ __('common.system_change_log_empty') }}
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">{{ __('common.when') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('common.actor') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('common.entity_type') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('common.entity_id') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('common.action') }}</th>
                        <th class="px-4 py-2 text-left">{{ __('common.changed_fields') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($logs as $log)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-xs">
                                {{ $log->created_at?->format('d M Y H:i:s') }}
                                <div class="text-slate-400">{{ $log->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if($log->actor)
                                    {{ trim($log->actor->first_name.' '.$log->actor->last_name) }}
                                @else
                                    <span class="text-slate-400 italic">{{ __('common.system') }}</span>
                                @endif
                                @if($log->ip_address)
                                    <div class="text-xs text-slate-400 font-mono">{{ $log->ip_address }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <span class="badge-blue">{{ __('common.entity_type_'.$log->entity_type) }}</span>
                            </td>
                            <td class="px-4 py-2 font-mono text-xs">{{ $log->entity_id ?? '—' }}</td>
                            <td class="px-4 py-2">
                                @if($log->action === 'created')
                                    <span class="badge-green">{{ __('common.action_created') }}</span>
                                @elseif($log->action === 'deleted')
                                    <span class="badge-red">{{ __('common.action_deleted') }}</span>
                                @else
                                    <span class="badge-gray">{{ __('common.action_updated') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @php $cf = $log->changed_fields ?? []; @endphp
                                @if(! empty($cf) && is_array($cf))
                                    <dl class="text-xs space-y-0.5">
                                        @foreach($cf as $key => $change)
                                            <div class="flex flex-wrap items-baseline gap-2">
                                                <dt class="font-mono text-slate-500">{{ $key }}:</dt>
                                                <dd>
                                                    <span class="line-through text-slate-400">{{ is_scalar($change['from'] ?? null) ? $change['from'] : json_encode($change['from'] ?? null) }}</span>
                                                    <span class="mx-1 text-slate-400">→</span>
                                                    <span>{{ is_scalar($change['to'] ?? null) ? $change['to'] : json_encode($change['to'] ?? null) }}</span>
                                                </dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($logs->hasPages())
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
