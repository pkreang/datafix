@extends('layouts.app')

@section('title', __('common.edit') . ' ' . __('common.workflow'))

@section('content')
<div x-data="workflowBuilderEdit({{ Js::from($workflow->stages->map(fn($s) => [
    'step_no' => $s->step_no,
    'name' => $s->name,
    'approver_type' => $s->approver_type,
    'approver_ref' => $s->approver_ref,
    'min_approvals' => $s->min_approvals,
])->values()) }}, {{ Js::from($roles->values()) }}, {{ Js::from($users->values()) }}, {{ Js::from($positions->values()) }}, {{ Js::from([
    'untitled' => __('common.workflow_stage_untitled'),
    'minLabel' => __('common.workflow_preview_min_label'),
]) }})">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.edit') }} {{ __('common.workflow') }}</h2>
        <a href="{{ route('settings.workflow.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <form method="POST" action="{{ route('settings.workflow.update', $workflow) }}" class="space-y-5" @submit="return canSubmit()">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.document_type') }}</label>
                        <select name="document_type" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            @foreach(\App\Models\DocumentType::allActive() as $dt)
                                <option value="{{ $dt->code }}" @selected($workflow->document_type === $dt->code)>{{ $dt->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.name') }}</label>
                        <input name="name" value="{{ $workflow->name }}" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                    </div>
                </div>

                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.remark') }}</label>
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">{{ $workflow->description }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="addStage()" class="px-3 py-2 rounded bg-blue-600 text-white text-sm">+ {{ __('common.workflow_add_stage') }}</button>
                </div>

                <template x-for="(stage, idx) in stages" :key="stage.uuid">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/20 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium">{{ __('common.workflow_step_short') }} <span x-text="stage.step_no"></span></h3>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="moveUp(idx)" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs">{{ __('common.move_up') }}</button>
                                <button type="button" @click="moveDown(idx)" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs">{{ __('common.move_down') }}</button>
                                <button type="button" @click="cloneStage(idx)" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs">{{ __('common.workflow_clone') }}</button>
                                <button type="button" @click="removeStage(idx)" class="px-2 py-1 rounded bg-red-600 text-white text-xs">{{ __('common.delete') }}</button>
                            </div>
                        </div>
                        <input type="hidden" :name="`stages[${idx}][step_no]`" x-model="stage.step_no">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div>
                                <label class="text-xs text-gray-500">{{ __('common.workflow_stage_name') }}</label>
                                <input :name="`stages[${idx}][name]`" x-model="stage.name" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ __('common.workflow_approver_type') }}</label>
                                <select :name="`stages[${idx}][approver_type]`" x-model="stage.approver_type" @change="stage.approver_ref=''; checkValidity()" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    <option value="role">{{ __('common.workflow_approver_role') }}</option>
                                    <option value="user">{{ __('common.workflow_approver_user') }}</option>
                                    <option value="position">{{ __('common.workflow_approver_position') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ __('common.workflow_approver_ref') }}</label>
                                <template x-if="stage.approver_type === 'role'">
                                    <select :name="`stages[${idx}][approver_ref]`" x-model="stage.approver_ref" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <option value="">{{ __('common.workflow_placeholder_select_role') }}</option>
                                        <template x-for="role in roles" :key="`role-${role}`">
                                            <option :value="role" x-text="role"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="stage.approver_type === 'user'">
                                    <select :name="`stages[${idx}][approver_ref]`" x-model="stage.approver_ref" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <option value="">{{ __('common.workflow_placeholder_select_user') }}</option>
                                        <template x-for="user in users" :key="`user-${user.id}`">
                                            <option :value="String(user.id)" x-text="user.label"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="stage.approver_type === 'position'">
                                    <select :name="`stages[${idx}][approver_ref]`" x-model="stage.approver_ref" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                        <option value="">{{ __('common.workflow_placeholder_select_position') }}</option>
                                        <template x-for="p in positions" :key="`pos-${p.id}`">
                                            <option :value="String(p.id)" x-text="p.label"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">{{ __('common.workflow_min_approvals') }}</label>
                                <input type="number" min="1" :name="`stages[${idx}][min_approvals]`" x-model="stage.min_approvals" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" />
                            </div>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-end pt-2">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('settings.workflow.index') }}" class="px-4 py-2 rounded bg-gray-300 dark:bg-gray-700 text-sm">{{ __('common.cancel') }}</a>
                        <button :disabled="!isValid" :class="isValid ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'" class="px-4 py-2 text-white rounded-lg text-sm">{{ __('common.update') }}</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="xl:col-span-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.workflow_flow_preview') }}</h3>
            <div class="space-y-2">
                <template x-for="stage in stages" :key="stage.uuid + '-preview'">
                    <div class="rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/20 px-3 py-2 text-sm">
                        <span class="font-medium">#<span x-text="stage.step_no"></span> <span x-text="stage.name || i18n.untitled"></span></span>
                        <div class="text-xs text-gray-500 mt-1"><span x-text="stage.approver_type"></span>: <span x-text="stage.approver_ref || '-'"></span> | <span x-text="i18n.minLabel"></span> <span x-text="stage.min_approvals"></span></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function workflowBuilderEdit(initialStages, roles, users, positions, i18n) {
        return {
            roles: roles || [],
            users: users || [],
            positions: positions || [],
            i18n: i18n || { untitled: '', minLabel: 'min.' },
            stages: [],
            seq: 1,
            isValid: true,
            init() {
                this.stages = (initialStages || []).map((s) => ({
                    uuid: this.seq++,
                    step_no: Number(s.step_no || 1),
                    name: s.name || '',
                    approver_type: s.approver_type || 'role',
                    approver_ref: s.approver_ref || '',
                    min_approvals: Number(s.min_approvals || 1),
                }));
                if (!this.stages.length) this.addStage();
                this.normalize();
                this.checkValidity();
            },
            addStage() {
                this.stages.push({uuid: this.seq++, step_no: this.stages.length + 1, name: '', approver_type: 'role', approver_ref: '', min_approvals: 1});
                this.normalize();
                this.checkValidity();
            },
            removeStage(idx) {
                this.stages.splice(idx, 1);
                if (!this.stages.length) this.addStage();
                this.normalize();
                this.checkValidity();
            },
            moveUp(idx) {
                if (idx <= 0) return;
                [this.stages[idx - 1], this.stages[idx]] = [this.stages[idx], this.stages[idx - 1]];
                this.normalize();
                this.checkValidity();
            },
            moveDown(idx) {
                if (idx >= this.stages.length - 1) return;
                [this.stages[idx + 1], this.stages[idx]] = [this.stages[idx], this.stages[idx + 1]];
                this.normalize();
                this.checkValidity();
            },
            cloneStage(idx) {
                const s = this.stages[idx];
                this.stages.splice(idx + 1, 0, {uuid: this.seq++, step_no: s.step_no, name: s.name, approver_type: s.approver_type, approver_ref: s.approver_ref, min_approvals: s.min_approvals});
                this.normalize();
                this.checkValidity();
            },
            normalize() {
                this.stages.forEach((s, i) => s.step_no = i + 1);
            },
            checkValidity() {
                this.isValid = this.stages.length > 0 && this.stages.every((s) =>
                    String(s.name || '').trim() !== '' &&
                    String(s.approver_ref || '').trim() !== '' &&
                    Number(s.min_approvals || 0) >= 1
                );
            },
            canSubmit() {
                this.checkValidity();
                return this.isValid;
            }
        };
    }
</script>
@endsection
