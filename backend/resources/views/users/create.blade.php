@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div x-data="userCreateForm">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-2">
        <span>Settings</span>
        <span class="mx-1">/</span>
        <a href="{{ route('users.index') }}" class="hover:text-blue-600">User & access</a>
    </nav>
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Add user</h2>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <ul class="text-sm text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <input type="hidden" name="role_type" :value="roleType">
        <template x-for="pid in permissions" :key="pid">
            <input type="hidden" name="permissions[]" :value="pid">
        </template>

        {{-- Section 1: General Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">General Info</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                {{-- Row 1: First name / Last name --}}
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                        First name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required maxlength="255"
                           placeholder="e.g. Andy"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none @error('first_name') border-red-400 @enderror">
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Last name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required maxlength="255"
                           placeholder="e.g. Worchen"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none @error('last_name') border-red-400 @enderror">
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Row 2: Email / Department --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           placeholder="e.g. andy@company.com"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">
                        Department
                    </label>
                    <input type="text" name="department" id="department" value="{{ old('department') }}" maxlength="255"
                           placeholder="e.g. Engineering"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none @error('department') border-red-400 @enderror">
                    @error('department')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Row 3: Position / Remark --}}
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">
                        Position
                    </label>
                    <input type="text" name="position" id="position" value="{{ old('position') }}" maxlength="255"
                           placeholder="e.g. Senior Developer"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none @error('position') border-red-400 @enderror">
                    @error('position')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="remark" class="block text-sm font-medium text-gray-700 mb-1">
                        Remark
                    </label>
                    <textarea name="remark" id="remark" rows="1" maxlength="1000"
                              placeholder="Optional notes..."
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-y @error('remark') border-red-400 @enderror">{{ old('remark') }}</textarea>
                    @error('remark')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Row 4: Active/Inactive toggle --}}
                <div class="md:col-span-2" x-data="{ active: {{ old('is_active', '1') ? 'true' : 'false' }} }">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <input type="hidden" name="is_active" :value="active ? '1' : '0'">
                    <button type="button" @click="active = !active"
                            :class="active ? 'bg-blue-600' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span :class="active ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                    <span class="ml-3 text-sm" :class="active ? 'text-green-700 font-medium' : 'text-gray-500'"
                          x-text="active ? 'Active' : 'Inactive'"></span>
                </div>
            </div>
        </div>

        {{-- Section 2: Role & Access --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Role & access</h3>

            {{-- Toggle tabs --}}
            <div class="inline-flex rounded-lg border border-gray-300 mb-6">
                <button type="button" @click="roleType = 'default'"
                        :class="roleType === 'default' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        class="px-4 py-2 text-sm font-medium rounded-l-lg transition">
                    Default role
                </button>
                <button type="button" @click="roleType = 'custom'"
                        :class="roleType === 'custom' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        class="px-4 py-2 text-sm font-medium rounded-r-lg border-l border-gray-300 transition">
                    Custom role
                </button>
            </div>

            {{-- Tab 1: Default role --}}
            <div x-show="roleType === 'default'" x-cloak>
                <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Select a role</label>
                <select name="role_id" id="role_id"
                        class="w-full max-w-md px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white">
                    <option value="">— Choose role —</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tab 2: Custom role (Permission matrix) --}}
            <div x-show="roleType === 'custom'" x-cloak>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-56">
                                    Module
                                </th>
                                <template x-for="action in allActions" :key="action">
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">
                                        <label class="inline-flex flex-col items-center gap-1 cursor-pointer">
                                            <input type="checkbox"
                                                   :checked="isColumnAllChecked(action)"
                                                   :indeterminate="isColumnPartial(action)"
                                                   @click="toggleColumnAll(action)"
                                                   class="rounded border-gray-300 text-gray-900 focus:ring-gray-500 w-4 h-4">
                                            <span x-text="action.charAt(0).toUpperCase() + action.slice(1)" class="text-xs"></span>
                                        </label>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <template x-for="(row, rowIdx) in matrix" :key="row.module">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox"
                                                   :checked="isModuleAllChecked(rowIdx)"
                                                   :indeterminate="isModulePartial(rowIdx)"
                                                   @click="toggleModuleRow(rowIdx)"
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                            <span x-text="row.label" class="text-sm font-medium text-gray-900"></span>
                                        </label>
                                    </td>
                                    <template x-for="action in allActions" :key="action">
                                        <td class="px-4 py-3 text-center">
                                            <template x-if="row.actions[action] !== null && row.actions[action] !== undefined">
                                                <input type="checkbox"
                                                       :checked="isChecked(row.actions[action])"
                                                       @click="togglePermission(row.actions[action])"
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                                            </template>
                                            <template x-if="row.actions[action] === null || row.actions[action] === undefined">
                                                <span class="text-gray-300">&mdash;</span>
                                            </template>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between pt-2 pb-4">
            <a href="{{ route('users.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                &larr; Back
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('users.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition">
                    Save
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('userCreateForm', () => ({
        roleType: '{{ old('role_type', 'default') }}',
        permissions: @json(old('permissions', [])).map(Number),
        allActions: ['create', 'read', 'update', 'delete', 'export'],
        matrix: @json($permissionMatrix),

        togglePermission(id) {
            if (!id) return;
            const idx = this.permissions.indexOf(id);
            if (idx > -1) this.permissions.splice(idx, 1);
            else this.permissions.push(id);
        },

        isChecked(id) {
            return id && this.permissions.includes(id);
        },

        getModuleIds(moduleIdx) {
            return Object.values(this.matrix[moduleIdx].actions).filter(id => id !== null);
        },

        toggleModuleRow(moduleIdx) {
            const ids = this.getModuleIds(moduleIdx);
            const allChecked = ids.every(id => this.permissions.includes(id));
            if (allChecked) {
                ids.forEach(id => { const i = this.permissions.indexOf(id); if (i > -1) this.permissions.splice(i, 1); });
            } else {
                ids.forEach(id => { if (!this.permissions.includes(id)) this.permissions.push(id); });
            }
        },

        isModuleAllChecked(moduleIdx) {
            const ids = this.getModuleIds(moduleIdx);
            return ids.length > 0 && ids.every(id => this.permissions.includes(id));
        },

        isModulePartial(moduleIdx) {
            const ids = this.getModuleIds(moduleIdx);
            const checked = ids.filter(id => this.permissions.includes(id)).length;
            return checked > 0 && checked < ids.length;
        },

        getColumnIds(action) {
            return this.matrix.map(r => r.actions[action]).filter(id => id !== null);
        },

        toggleColumnAll(action) {
            const ids = this.getColumnIds(action);
            const allChecked = ids.every(id => this.permissions.includes(id));
            if (allChecked) {
                ids.forEach(id => { const i = this.permissions.indexOf(id); if (i > -1) this.permissions.splice(i, 1); });
            } else {
                ids.forEach(id => { if (!this.permissions.includes(id)) this.permissions.push(id); });
            }
        },

        isColumnAllChecked(action) {
            const ids = this.getColumnIds(action);
            return ids.length > 0 && ids.every(id => this.permissions.includes(id));
        },

        isColumnPartial(action) {
            const ids = this.getColumnIds(action);
            const checked = ids.filter(id => this.permissions.includes(id)).length;
            return checked > 0 && checked < ids.length;
        }
    }));
});
</script>
@endsection
