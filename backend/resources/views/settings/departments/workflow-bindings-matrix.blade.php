@extends('layouts.app')

@section('title', __('common.department_workflow_bindings'))

@section('content')
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.department_workflow_bindings') }}</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('common.department_workflow_bindings_intro') }}</p>
    </div>

    @if ($documentTypes->isEmpty())
        <div class="p-6 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
            {{ __('common.department_workflow_bindings_no_types') }}
        </div>
    @else
        <div x-data="{
            original: @js($initialBindings),
            current: JSON.parse(JSON.stringify(@js($initialBindings))),
            openDepts: { '{{ $departments->first()?->id }}': true },
            toggle(id) { this.openDepts[id] = !this.openDepts[id] },
            isOpen(id) { return !!this.openDepts[id] },
            isDirty(key) { return this.current[key] !== this.original[key] },
            get dirtyCount() { return Object.keys(this.current).filter(k => this.isDirty(k)).length },
            deptDirtyCount(deptId) {
                return Object.keys(this.current).filter(k => k.startsWith(deptId + '|') && this.isDirty(k)).length;
            },
            reset() { this.current = JSON.parse(JSON.stringify(this.original)) },
            submitForm(formEl) {
                formEl.querySelectorAll('.dynamic-input').forEach(el => el.remove());
                let i = 0;
                Object.keys(this.current).filter(k => this.isDirty(k)).forEach(key => {
                    const sep = key.indexOf('|');
                    const deptId = key.substring(0, sep);
                    const docType = key.substring(sep + 1);
                    const mk = (name, val) => {
                        let inp = document.createElement('input');
                        inp.type = 'hidden'; inp.name = name; inp.value = val;
                        inp.classList.add('dynamic-input');
                        formEl.appendChild(inp);
                    };
                    mk('bindings[' + i + '][department_id]', deptId);
                    mk('bindings[' + i + '][document_type]', docType);
                    mk('bindings[' + i + '][workflow_id]', this.current[key]);
                    i++;
                });
                formEl.submit();
            }
        }">
            <form method="POST" action="{{ route('settings.department-workflow-bindings.bulk') }}"
                  @submit.prevent="submitForm($el)">
                @csrf

                <div class="space-y-3">
                    @foreach ($departments as $department)
                        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-visible">
                            {{-- Accordion header --}}
                            <button type="button" @click="toggle('{{ $department->id }}')"
                                    class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors rounded-xl">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                                         :class="isOpen('{{ $department->id }}') && 'rotate-90'"
                                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $department->name }}</span>
                                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">({{ $department->code }})</span>
                                    </div>
                                </div>
                                <span x-show="deptDirtyCount('{{ $department->id }}') > 0"
                                      x-text="deptDirtyCount('{{ $department->id }}')"
                                      x-cloak
                                      class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-bold text-amber-700 bg-amber-100 dark:text-amber-300 dark:bg-amber-900/40 rounded-full">
                                </span>
                            </button>

                            {{-- Accordion body --}}
                            <div x-show="isOpen('{{ $department->id }}')" x-collapse x-cloak>
                                <div class="border-t border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($documentTypes as $docType)
                                        @php
                                            $cellKey = $department->id . '|' . $docType;
                                            $options = $workflows->where('document_type', $docType);
                                            $docLabel = $documentTypeLabels[$docType] ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $docType));
                                        @endphp
                                        <div class="px-5 py-3 flex items-center gap-4 transition-colors duration-150"
                                             :class="isDirty('{{ $cellKey }}') && 'bg-amber-50 dark:bg-amber-900/20'">
                                            <div class="w-44 shrink-0">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $docLabel }}</span>
                                            </div>
                                            <div class="flex-1 max-w-md">
                                                @if ($options->isEmpty())
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 italic">{{ __('common.no_workflows_for_document_type') }}</span>
                                                @else
                                                    <select x-model="current['{{ $cellKey }}']"
                                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm transition-shadow duration-150"
                                                            :class="isDirty('{{ $cellKey }}') && 'ring-2 ring-amber-400 dark:ring-amber-500'">
                                                        <option value="">-- {{ __('common.none') }} --</option>
                                                        @foreach ($options as $workflow)
                                                            <option value="{{ $workflow->id }}">{{ $workflow->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Sticky footer --}}
                <div class="sticky bottom-0 mt-4 flex items-center justify-between p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div>
                        <span x-show="dirtyCount > 0" class="text-sm text-amber-600 dark:text-amber-400 font-medium">
                            <span x-text="dirtyCount"></span> {{ __('common.changes_pending') }}
                        </span>
                        <span x-show="dirtyCount === 0" class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('common.no_unsaved_changes') }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="reset()" x-show="dirtyCount > 0" x-cloak
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
                            {{ __('common.reset') }}
                        </button>
                        <button type="submit" :disabled="dirtyCount === 0"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg"
                                :class="dirtyCount === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'">
                            {{ __('common.save_all') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
@endsection
