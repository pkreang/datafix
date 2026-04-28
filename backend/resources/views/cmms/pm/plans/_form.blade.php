@php
    $isEdit = $plan->exists;
    $action = $isEdit ? route('cmms.pm.plans.update', $plan) : route('cmms.pm.plans.store');
    $initialTasks = old('tasks', $plan->taskItems->map(fn ($t) => [
        'description' => $t->description,
        'task_type' => $t->task_type,
        'expected_value' => $t->expected_value,
        'unit' => $t->unit,
        'requires_photo' => (bool) $t->requires_photo,
        'requires_signature' => (bool) $t->requires_signature,
        'spare_part_id' => $t->spare_part_id,
        'estimated_minutes' => $t->estimated_minutes,
        'loto_required' => (bool) $t->loto_required,
        'is_critical' => (bool) $t->is_critical,
    ])->values()->all());
@endphp

<div x-data="pmPlanForm({{ Js::from($initialTasks) }}, {{ Js::from($spareParts) }})">
    <form method="POST" action="{{ $action }}" class="space-y-6">
        @csrf
        @if($isEdit) @method('PUT') @endif

        @if($errors->any())
            <div class="alert-error">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- SECTION 1: Plan details --}}
        <div class="card p-4 sm:p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">{{ __('common.pm_plan_details') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('common.equipment') }} <span class="text-red-500">*</span></label>
                    <select name="equipment_id" required class="form-input">
                        <option value="">{{ __('common.please_select') }}</option>
                        @foreach($equipmentList as $eq)
                            <option value="{{ $eq->id }}" {{ old('equipment_id', $plan->equipment_id) == $eq->id ? 'selected' : '' }}>{{ $eq->code }} — {{ $eq->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">{{ __('common.pm_plan_name') }} <span class="text-red-500">*</span></label>
                    <input name="name" value="{{ old('name', $plan->name) }}" required maxlength="255" class="form-input" />
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">{{ __('common.description') }}</label>
                    <textarea name="description" rows="2" class="form-input resize-y">{{ old('description', $plan->description) }}</textarea>
                </div>

                <div>
                    <label class="form-label">{{ __('common.pm_frequency_type') }} <span class="text-red-500">*</span></label>
                    <select name="frequency_type" x-model="freqType" required class="form-input">
                        <option value="date">{{ __('common.pm_frequency_date') }}</option>
                        <option value="runtime">{{ __('common.pm_frequency_runtime') }}</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" x-text="freqType === 'date' ? '{{ __('common.pm_interval_days') }} *' : '{{ __('common.pm_interval_hours') }} *'"></label>
                    <input type="number" min="1" step="0.01" x-bind:name="freqType === 'date' ? 'interval_days' : 'interval_hours'"
                           value="{{ old('interval_days', $plan->interval_days) ?: old('interval_hours', $plan->interval_hours) }}"
                           required class="form-input" />
                </div>

                <div>
                    <label class="form-label">{{ __('common.pm_assigned_position') }}</label>
                    <select name="assigned_to_position_id" class="form-input">
                        <option value="">{{ __('common.please_select') }}</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('assigned_to_position_id', $plan->assigned_to_position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">{{ __('common.pm_estimated_duration_minutes') }}</label>
                    <input type="number" min="1" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes', $plan->estimated_duration_minutes) }}" class="form-input" />
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-600">
                    <label for="is_active" class="text-sm text-slate-700 dark:text-slate-300">{{ __('common.is_active') }}</label>
                </div>
            </div>
        </div>

        {{-- SECTION 2: Task checklist --}}
        <div class="card p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('common.pm_checklist') }}</h3>
                <button type="button" @click="addTask()" class="btn-secondary text-sm">+ {{ __('common.pm_add_task') }}</button>
            </div>

            <template x-if="tasks.length === 0">
                <p class="text-sm text-slate-500 dark:text-slate-400 italic py-4 text-center">{{ __('common.pm_no_tasks_yet') }}</p>
            </template>

            <div class="space-y-3">
                <template x-for="(task, idx) in tasks" :key="task._rowId">
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-800/50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400"><span x-text="'#' + (idx + 1)"></span></span>
                            <button type="button" @click="removeTask(idx)" class="text-xs text-red-600 hover:text-red-700">{{ __('common.remove') }}</button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <div class="md:col-span-2">
                                <label class="text-xs text-slate-500">{{ __('common.pm_task_type') }} *</label>
                                <select x-model="task.task_type" :name="`tasks[${idx}][task_type]`" required class="form-input mt-1 text-sm">
                                    <option value="visual">{{ __('common.pm_task_type_visual') }}</option>
                                    <option value="measurement">{{ __('common.pm_task_type_measurement') }}</option>
                                    <option value="lubrication">{{ __('common.pm_task_type_lubrication') }}</option>
                                    <option value="replacement">{{ __('common.pm_task_type_replacement') }}</option>
                                    <option value="cleaning">{{ __('common.pm_task_type_cleaning') }}</option>
                                    <option value="tightening">{{ __('common.pm_task_type_tightening') }}</option>
                                    <option value="other">{{ __('common.pm_task_type_other') }}</option>
                                </select>
                            </div>

                            <div class="md:col-span-4">
                                <label class="text-xs text-slate-500">{{ __('common.description') }} *</label>
                                <input type="text" x-model="task.description" :name="`tasks[${idx}][description]`" required maxlength="500" class="form-input mt-1 text-sm" />
                            </div>

                            <div x-show="task.task_type === 'measurement'">
                                <label class="text-xs text-slate-500">{{ __('common.pm_expected_value') }}</label>
                                <input type="text" x-model="task.expected_value" :name="`tasks[${idx}][expected_value]`" placeholder="50-80" class="form-input mt-1 text-sm" />
                            </div>

                            <div x-show="task.task_type === 'measurement'">
                                <label class="text-xs text-slate-500">{{ __('common.pm_unit') }}</label>
                                <input type="text" x-model="task.unit" :name="`tasks[${idx}][unit]`" placeholder="%, bar, °C" class="form-input mt-1 text-sm" />
                            </div>

                            <div x-show="['replacement','lubrication'].includes(task.task_type)" class="md:col-span-2">
                                <label class="text-xs text-slate-500">{{ __('common.pm_spare_part') }}</label>
                                <select x-model="task.spare_part_id" :name="`tasks[${idx}][spare_part_id]`" class="form-input mt-1 text-sm">
                                    <option value="">{{ __('common.please_select') }}</option>
                                    <template x-for="sp in spareParts" :key="sp.id">
                                        <option :value="sp.id" x-text="sp.code + ' — ' + sp.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-slate-500">{{ __('common.pm_estimated_minutes') }}</label>
                                <input type="number" min="1" max="480" x-model.number="task.estimated_minutes" :name="`tasks[${idx}][estimated_minutes]`" class="form-input mt-1 text-sm" />
                            </div>

                            <div class="md:col-span-6 flex flex-wrap gap-x-4 gap-y-2 pt-1 border-t border-slate-200 dark:border-slate-700">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                    <input type="hidden" :name="`tasks[${idx}][requires_photo]`" value="0">
                                    <input type="checkbox" :name="`tasks[${idx}][requires_photo]`" value="1" x-model="task.requires_photo" class="rounded border-slate-300 dark:border-slate-600">
                                    {{ __('common.pm_requires_photo') }}
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                    <input type="hidden" :name="`tasks[${idx}][requires_signature]`" value="0">
                                    <input type="checkbox" :name="`tasks[${idx}][requires_signature]`" value="1" x-model="task.requires_signature" class="rounded border-slate-300 dark:border-slate-600">
                                    {{ __('common.pm_requires_signature') }}
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                    <input type="hidden" :name="`tasks[${idx}][loto_required]`" value="0">
                                    <input type="checkbox" :name="`tasks[${idx}][loto_required]`" value="1" x-model="task.loto_required" class="rounded border-slate-300 dark:border-slate-600">
                                    {{ __('common.pm_loto_required') }}
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs text-red-700 dark:text-red-400">
                                    <input type="hidden" :name="`tasks[${idx}][is_critical]`" value="0">
                                    <input type="checkbox" :name="`tasks[${idx}][is_critical]`" value="1" x-model="task.is_critical" class="rounded border-red-300 dark:border-red-600">
                                    {{ __('common.pm_is_critical') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('cmms.pm.plans.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $isEdit ? __('common.save') : __('common.create') }}</button>
        </div>
    </form>
</div>

<script>
    function pmPlanForm(initialTasks, sparePartsJs) {
        return {
            freqType: @json(old('frequency_type', $plan->frequency_type ?? 'date')),
            tasks: (initialTasks || []).map(t => ({
                ...t,
                _rowId: crypto?.randomUUID ? crypto.randomUUID() : ('r' + Math.random().toString(36).slice(2, 10)),
                requires_photo: !!t.requires_photo,
                requires_signature: !!t.requires_signature,
                loto_required: !!t.loto_required,
                is_critical: !!t.is_critical,
            })),
            spareParts: sparePartsJs || [],
            addTask() {
                this.tasks.push({
                    _rowId: crypto?.randomUUID ? crypto.randomUUID() : ('r' + Math.random().toString(36).slice(2, 10)),
                    description: '',
                    task_type: 'visual',
                    expected_value: '',
                    unit: '',
                    requires_photo: false,
                    requires_signature: false,
                    spare_part_id: '',
                    estimated_minutes: null,
                    loto_required: false,
                    is_critical: false,
                });
            },
            removeTask(idx) {
                this.tasks.splice(idx, 1);
            },
        };
    }
</script>
