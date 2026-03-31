@extends('layouts.app')

@section('title', __('auth.settings_title'))

@section('content')
    <div class="w-full">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('auth.settings_title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('auth.settings_subtitle') }}</p>
        </div>

        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">{{ __('auth.settings_where_title') }}</h3>
            <p class="text-sm text-blue-800 dark:text-blue-200">{{ __('auth.settings_where_body') }}</p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->has('auth'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first('auth') }}</p>
            </div>
        @endif

        @if ($errors->any() && ! $errors->has('auth'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.auth.save') }}"
              x-data="{
                  entra: {{ old('auth_entra_enabled', $settings['auth_entra_enabled'] ?? '0') == '1' ? 'true' : 'false' }},
                  ldap: {{ old('auth_ldap_enabled', $settings['auth_ldap_enabled'] ?? '0') == '1' ? 'true' : 'false' }}
              }">
            @csrf

            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('auth.settings_methods') }}</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="auth_local_enabled" value="0">
                        <input type="checkbox" name="auth_local_enabled" value="1"
                               {{ old('auth_local_enabled', $settings['auth_local_enabled'] ?? '1') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('auth.settings_local') }}</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer ml-6">
                        <input type="hidden" name="auth_local_super_admin_only" value="0">
                        <input type="checkbox" name="auth_local_super_admin_only" value="1"
                               {{ old('auth_local_super_admin_only', $settings['auth_local_super_admin_only'] ?? '0') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('auth.settings_local_super_admin_only') }}</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="auth_entra_enabled" value="0">
                        <input type="checkbox" name="auth_entra_enabled" value="1"
                               x-model="entra"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('auth.settings_entra') }}</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="auth_ldap_enabled" value="0">
                        <input type="checkbox" name="auth_ldap_enabled" value="1"
                               x-model="ldap"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('auth.settings_ldap') }}</span>
                    </label>
                </div>
            </div>

            <div x-show="entra || ldap" x-transition class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ __('auth.settings_password_help_section') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('auth.settings_password_help_hint') }}</p>
                <div>
                    <label for="auth_password_help_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_password_help_url_label') }}</label>
                    <input type="url" name="auth_password_help_url" id="auth_password_help_url"
                           value="{{ old('auth_password_help_url', $settings['auth_password_help_url'] ?? '') }}"
                           placeholder="https://passwordreset.microsoftonline.com/"
                           class="w-full max-w-2xl px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    @error('auth_password_help_url')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div x-show="entra || ldap" x-transition class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('auth.settings_jit_role') }}</h3>
                <div>
                    <label for="auth_default_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_default_role') }}</label>
                    <select name="auth_default_role" id="auth_default_role"
                            class="w-full max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ old('auth_default_role', $settings['auth_default_role'] ?? 'viewer') === $role->name ? 'selected' : '' }}>
                                {{ $role->display_name ?? $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div x-show="entra || ldap" x-transition class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ __('auth.settings_group_role_map_section') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('auth.settings_group_role_map_hint') }}</p>
                <pre class="text-xs text-gray-500 dark:text-gray-400 mb-3 p-3 bg-white/50 dark:bg-gray-900/40 rounded-lg border border-gray-200 dark:border-gray-600 overflow-x-auto font-mono">{{ __('auth.settings_group_role_map_example') }}</pre>
                <div>
                    <label for="auth_directory_group_role_map" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_group_role_map_label') }}</label>
                    <textarea name="auth_directory_group_role_map" id="auth_directory_group_role_map" rows="12"
                              class="w-full max-w-4xl px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono"
                              spellcheck="false">{{ old('auth_directory_group_role_map', $settings['auth_directory_group_role_map'] ?? '[]') }}</textarea>
                    @error('auth_directory_group_role_map')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div x-show="entra" x-transition class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ __('auth.settings_entra_section') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('auth.settings_entra_secret_hint') }}
                    @if ($entraEnvOk)
                        <span class="text-green-600 dark:text-green-400 font-medium">{{ __('auth.env_configured') }}</span>
                    @else
                        <span class="text-amber-600 dark:text-amber-400 font-medium">{{ __('auth.env_missing_entra') }}</span>
                    @endif
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="entra_tenant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_entra_tenant') }}</label>
                        <input type="text" name="entra_tenant_id" id="entra_tenant_id"
                               value="{{ old('entra_tenant_id', $settings['entra_tenant_id'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="entra_client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_entra_client_id') }}</label>
                        <input type="text" name="entra_client_id" id="entra_client_id"
                               value="{{ old('entra_client_id', $settings['entra_client_id'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">{{ __('auth.settings_entra_redirect', ['url' => route('auth.entra.callback', [], true)]) }}</p>
            </div>

            <div x-show="ldap" x-transition class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ __('auth.settings_ldap_section') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('auth.settings_ldap_secret_hint') }}
                    @if ($ldapEnvOk)
                        <span class="text-green-600 dark:text-green-400 font-medium">{{ __('auth.env_configured') }}</span>
                    @else
                        <span class="text-amber-600 dark:text-amber-400 font-medium">{{ __('auth.env_missing_ldap') }}</span>
                    @endif
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ldap_host" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_ldap_host') }}</label>
                        <input type="text" name="ldap_host" id="ldap_host"
                               value="{{ old('ldap_host', $settings['ldap_host'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="ldap_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_ldap_port') }}</label>
                        <input type="number" name="ldap_port" id="ldap_port"
                               value="{{ old('ldap_port', $settings['ldap_port'] ?? 389) }}"
                               class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div class="md:col-span-2">
                        <label for="ldap_base_dn" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_ldap_base_dn') }}</label>
                        <input type="text" name="ldap_base_dn" id="ldap_base_dn"
                               value="{{ old('ldap_base_dn', $settings['ldap_base_dn'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div class="md:col-span-2">
                        <label for="ldap_bind_dn" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_ldap_bind_dn') }}</label>
                        <input type="text" name="ldap_bind_dn" id="ldap_bind_dn"
                               value="{{ old('ldap_bind_dn', $settings['ldap_bind_dn'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div class="md:col-span-2">
                        <label for="ldap_user_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('auth.settings_ldap_filter') }}</label>
                        <input type="text" name="ldap_user_filter" id="ldap_user_filter"
                               value="{{ old('ldap_user_filter', $settings['ldap_user_filter'] ?? '(mail=%s)') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono text-xs">
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer md:col-span-2">
                        <input type="hidden" name="ldap_use_tls" value="0">
                        <input type="checkbox" name="ldap_use_tls" value="1"
                               {{ old('ldap_use_tls', $settings['ldap_use_tls'] ?? '0') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('auth.settings_ldap_tls') }}</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    {{ __('common.save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
