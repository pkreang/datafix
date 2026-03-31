@extends('layouts.app')

@section('title', __('common.add_user'))

@php
    $roleType = old('role_type', 'default');
    $oldPermissions = array_map('intval', (array) old('permissions', []));
    $actions = ['create', 'read', 'update', 'delete', 'export'];
    $actionLabels = [
        'create' => __('users.action_create'),
        'read' => __('users.action_read'),
        'update' => __('users.action_update'),
        'delete' => __('users.action_delete'),
        'export' => __('users.action_export')
    ];
@endphp

@section('content')
<div>

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 dark:text-gray-400 mb-2">
        <span>{{ __('common.settings') }}</span>
        <span class="mx-1">/</span>
        <a href="{{ route('users.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('common.user_and_access') }}</a>
    </nav>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('common.add_user') }}</h2>
        <a href="{{ route('users.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500">&larr; {{ __('common.back') }}</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}" id="user-create-form" x-data="{ roleType: '{{ $roleType }}' }">
        @csrf
        <input type="hidden" name="role_type" :value="roleType">

        {{-- Section 1: General Info --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('users.general_info') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                {{-- Row 1: First name / Last name --}}
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.first_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required maxlength="255"
                           placeholder="{{ __('users.placeholder_first_name') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('first_name') border-red-400 @enderror">
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.last_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required maxlength="255"
                           placeholder="{{ __('users.placeholder_last_name') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('last_name') border-red-400 @enderror">
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Row 2: Email / Department --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.email') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           placeholder="{{ __('users.placeholder_email') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.department') }}
                    </label>
                    <select name="department_id" id="department_id"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('department_id') border-red-400 @enderror">
                        <option value="">{{ __('common.choose_department') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Row 3: Position / Phone --}}
                <div>
                    <label for="position_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('common.position') }}
                    </label>
                    <select name="position_id" id="position_id"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('position_id') border-red-400 @enderror">
                        <option value="">{{ __('common.choose_position') }}</option>
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(old('position_id') == $pos->id)>{{ $pos->name }} ({{ $pos->code }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('users.position_from_master_hint') }}</p>
                    @error('position_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('users.phone') }}
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" maxlength="50"
                           placeholder="{{ __('users.placeholder_phone') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('phone') border-red-400 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Row 4: Remark --}}
                <div class="md:col-span-2">
                    <label for="remark" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('users.remark') }}
                    </label>
                    <textarea name="remark" id="remark" rows="1" maxlength="1000"
                              placeholder="{{ __('users.placeholder_remark') }}"
                              class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-y bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 @error('remark') border-red-400 @enderror">{{ old('remark') }}</textarea>
                    @error('remark')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Row 4: Active/Inactive toggle --}}
                <div class="md:col-span-2" x-data="{ active: {{ old('is_active', '1') ? 'true' : 'false' }} }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('common.status') }}</label>
                    <input type="hidden" name="is_active" :value="active ? '1' : '0'">
                    <button type="button" @click="active = !active"
                            :class="active ? 'bg-blue-600' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span :class="active ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 translate-y-0.5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                    <span class="ml-3 text-sm" :class="active ? 'text-green-700 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400'"
                          x-text="active ? '{{ __('common.active') }}' : '{{ __('common.inactive') }}'"></span>
                </div>
            </div>
        </div>

        {{-- Section 2: Role & Access --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('common.role_and_access') }}</h3>

            {{-- Toggle tabs --}}
            <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-600 mb-6">
                <button type="button" @click="roleType = 'default'"
                        :class="roleType === 'default' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-4 py-2 text-sm font-medium rounded-l-lg transition border-gray-300 dark:border-gray-600">
                    {{ __('common.default_role') }}
                </button>
                <button type="button" @click="roleType = 'custom'"
                        :class="roleType === 'custom' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-4 py-2 text-sm font-medium rounded-r-lg border-l border-gray-300 dark:border-gray-600 transition">
                    {{ __('common.custom_role') }}
                </button>
            </div>

            {{-- Tab 1: Default role --}}
            <div x-show="roleType === 'default'" x-cloak
                 class="role-panel"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.select_role') }} <span class="text-red-500">*</span></label>
                <select name="role_id" id="role_id" :disabled="roleType !== 'default'"
                        class="w-full max-w-md px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 disabled:opacity-50 disabled:cursor-not-allowed @error('role_id') border-red-400 @enderror">
                    <option value="">{{ __('common.choose_role') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->display_name ?? ucfirst(str_replace('-', ' ', $role->name)) }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tab 2: Custom role (Permission matrix) --}}
            <div x-show="roleType === 'custom'" x-cloak
                 class="role-panel"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                @error('permissions')
                    <p class="mb-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @if(empty($permissionMatrix))
                    <p class="text-sm text-amber-600 dark:text-amber-400 py-4">{{ __('users.no_permissions_configured') }}</p>
                @else
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/80">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-56">
                                        {{ __('common.module') }}
                                    </th>
                                    @foreach ($actions as $action)
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-28">
                                            {{ $actionLabels[$action] ?? $action }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-gray-100 dark:bg-gray-800">
                                @foreach ($permissionMatrix as $row)
                                    <tr class="hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $row['label'] }}
                                        </td>
                                        @foreach ($actions as $action)
                                            <td class="px-6 py-3 text-center">
                                                @if(!empty($row['actions'][$action]))
                                                    <input type="checkbox" name="permissions[]" value="{{ $row['actions'][$action] }}"
                                                           :disabled="roleType !== 'custom'"
                                                           class="permission-cb rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                                           {{ in_array($row['actions'][$action], $oldPermissions) ? 'checked' : '' }}>
                                                @else
                                                    <span class="text-gray-300 dark:text-gray-500">&mdash;</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end pt-2 pb-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('users.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                    {{ __('common.save') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
