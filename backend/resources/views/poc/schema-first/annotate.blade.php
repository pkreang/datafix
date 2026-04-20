@extends('layouts.app')

@section('title', 'Annotate '.$table)

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'PoC Schema-first'],
        ['label' => $table, 'url' => route('poc.schema-first.show', $table)],
        ['label' => 'Annotate'],
    ]" />
@endsection

@section('content')
@php
    $uiTypes = [
        'text', 'textarea', 'number', 'currency', 'date', 'time', 'datetime',
        'select', 'multi_select', 'radio', 'checkbox', 'email', 'phone',
        'file', 'image', 'signature', 'lookup', 'section',
    ];
@endphp
<div>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                Annotate columns — <code class="text-blue-600 dark:text-blue-400">{{ $table }}</code>
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Schema = source of truth. Annotate UI metadata only — no DDL is run.
            </p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('poc.schema-first.bootstrap', $table) }}">
                @csrf
                <button type="submit" class="btn-secondary"
                        onclick="return confirm('Re-introspect table {{ $table }}? Adds annotations for missing columns; existing rows kept.');">
                    Re-bootstrap
                </button>
            </form>
            <a href="{{ route('poc.schema-first.show', $table) }}" class="btn-primary">Open form →</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('poc.schema-first.annotate.save', $table) }}">
        @csrf
        <div class="table-wrapper">
            <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="table-header text-left">DB column</th>
                        <th class="table-header text-left">DB type</th>
                        <th class="table-header text-left">Label (EN)</th>
                        <th class="table-header text-left">Label (TH)</th>
                        <th class="table-header text-left">UI type</th>
                        <th class="table-header text-center">Sort</th>
                        <th class="table-header text-center">Visible</th>
                        <th class="table-header text-center">Required</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @foreach ($annotations as $i => $ann)
                        @php
                            $db = $introspection->get($ann->column_name);
                            $isReserved = $db['is_reserved'] ?? false;
                        @endphp
                        <tr class="{{ $isReserved ? 'bg-slate-50/50 dark:bg-slate-800/30 text-slate-500' : '' }}">
                            <td class="px-3 py-2">
                                <input type="hidden" name="annotations[{{ $i }}][column_name]" value="{{ $ann->column_name }}">
                                <code class="text-xs">{{ $ann->column_name }}</code>
                                @if ($isReserved)
                                    <span class="ml-1 text-[10px] uppercase tracking-wide text-slate-400">reserved</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                {{ $db['type'] ?? '?' }}
                                @if (! ($db['nullable'] ?? true))
                                    <span class="text-red-500">NOT NULL</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="annotations[{{ $i }}][label_en]"
                                       value="{{ $ann->label_en }}" class="form-input w-full">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="annotations[{{ $i }}][label_th]"
                                       value="{{ $ann->label_th }}" class="form-input w-full">
                            </td>
                            <td class="px-3 py-2">
                                <select name="annotations[{{ $i }}][ui_type]" class="form-input">
                                    @foreach ($uiTypes as $t)
                                        <option value="{{ $t }}" @selected($ann->ui_type === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="number" name="annotations[{{ $i }}][sort_order]"
                                       value="{{ $ann->sort_order }}" class="form-input w-16 text-center" min="0">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="hidden" name="annotations[{{ $i }}][is_visible]" value="0">
                                <input type="checkbox" name="annotations[{{ $i }}][is_visible]" value="1"
                                       @checked($ann->is_visible) class="h-4 w-4">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="hidden" name="annotations[{{ $i }}][is_required]" value="0">
                                <input type="checkbox" name="annotations[{{ $i }}][is_required]" value="1"
                                       @checked($ann->is_required) class="h-4 w-4">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <button type="submit" class="btn-primary">Save annotations</button>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                No DDL is executed. To add a column, write a Laravel migration and click "Re-bootstrap".
            </span>
        </div>
    </form>
</div>
@endsection
