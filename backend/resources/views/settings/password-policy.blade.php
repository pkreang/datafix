@extends('layouts.app')

@section('title', __('password_policy.title'))

@section('content')
    <div class="w-full">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('password_policy.title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('password_policy.subtitle') }}</p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $policyLabels = [
                'rule_min_chars' => __('password_policy.rule_min_chars', ['min' => '__MIN__']),
                'rule_max_chars' => __('password_policy.rule_max_chars', ['max' => '__MAX__']),
                'rule_uppercase' => __('password_policy.rule_uppercase'),
                'rule_lowercase' => __('password_policy.rule_lowercase'),
                'rule_number' => __('password_policy.rule_number'),
                'rule_special' => __('password_policy.rule_special'),
                'rule_expiry' => __('password_policy.rule_expiry', ['days' => '__DAYS__']),
                'rule_first_login' => __('password_policy.rule_first_login'),
                'rule_reuse' => __('password_policy.rule_reuse', ['n' => '__N__']),
                'password_must' => __('password_policy.password_must'),
                'no_rules' => __('password_policy.no_rules'),
            ];
        @endphp
        <form method="POST" action="{{ route('settings.password-policy.save') }}" x-data="passwordPolicyForm">
            @csrf

            {{-- Password Requirements --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-5">{{ __('password_policy.requirements') }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label for="password_min_length" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('password_policy.min_length') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="password_min_length" id="password_min_length"
                                   value="{{ old('password_min_length', $settings['password_min_length'] ?? 8) }}"
                                   min="1" max="128"
                                   class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('password_policy.characters') }}</span>
                        </div>
                    </div>
                    <div>
                        <label for="password_max_length" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('password_policy.max_length') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="password_max_length" id="password_max_length"
                                   value="{{ old('password_max_length', $settings['password_max_length'] ?? 255) }}"
                                   min="1" max="255"
                                   class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('password_policy.characters') }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="password_require_uppercase" value="0">
                        <input type="checkbox" name="password_require_uppercase" value="1"
                               {{ old('password_require_uppercase', $settings['password_require_uppercase'] ?? '1') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('password_policy.require_uppercase') }} <span class="text-gray-400 dark:text-gray-400">(A-Z)</span></span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="password_require_lowercase" value="0">
                        <input type="checkbox" name="password_require_lowercase" value="1"
                               {{ old('password_require_lowercase', $settings['password_require_lowercase'] ?? '1') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('password_policy.require_lowercase') }} <span class="text-gray-400 dark:text-gray-400">(a-z)</span></span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="password_require_number" value="0">
                        <input type="checkbox" name="password_require_number" value="1"
                               {{ old('password_require_number', $settings['password_require_number'] ?? '1') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('password_policy.require_number') }} <span class="text-gray-400 dark:text-gray-400">(0-9)</span></span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="password_require_special" value="0">
                        <input type="checkbox" name="password_require_special" value="1"
                               {{ old('password_require_special', $settings['password_require_special'] ?? '1') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('password_policy.require_special') }} <span class="text-gray-400 dark:text-gray-400">(!@#$%...)</span></span>
                    </label>
                </div>
            </div>

            {{-- Password Expiry --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-5">{{ __('password_policy.expiry') }}</h3>

                <div class="mb-5">
                    <label for="password_expires_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('password_policy.expires_after') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="password_expires_days" id="password_expires_days"
                               value="{{ old('password_expires_days', $settings['password_expires_days'] ?? 0) }}"
                               min="0" max="365"
                               class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('password_policy.days') }}</span>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-400 mt-1">{{ __('password_policy.never_expire_hint') }}</p>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="password_force_change_first_login" value="0">
                        <input type="checkbox" name="password_force_change_first_login" value="1"
                               {{ old('password_force_change_first_login', $settings['password_force_change_first_login'] ?? '1') == '1' ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('password_policy.force_change_first_login') }}</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="password_prevent_reuse_enabled" value="0">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="prevent_reuse_toggle"
                                   {{ old('password_prevent_reuse', $settings['password_prevent_reuse'] ?? '0') > 0 ? 'checked' : '' }}
                                   x-model="preventReuse"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('password_policy.prevent_reuse') }}</span>
                        </label>
                        <input type="number" name="password_prevent_reuse"
                               :value="preventReuse ? preventReuseCount : 0"
                               x-show="preventReuse"
                               min="1" max="24"
                               x-model="preventReuseCount"
                               class="w-16 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <input type="hidden" name="password_prevent_reuse" value="0" x-show="!preventReuse" :disabled="preventReuse">
                        <span class="text-sm text-gray-500 dark:text-gray-400" x-show="preventReuse">{{ __('password_policy.passwords') }}</span>
                    </div>
                </div>
            </div>

            {{-- Account Lockout --}}
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-5">{{ __('password_policy.lockout') }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="lockout_max_attempts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('password_policy.lockout_after') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="lockout_max_attempts" id="lockout_max_attempts"
                                   value="{{ old('lockout_max_attempts', $settings['lockout_max_attempts'] ?? 5) }}"
                                   min="0" max="100"
                                   class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('password_policy.failed_attempts') }}</span>
                        </div>
                    </div>
                    <div>
                        <label for="lockout_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('password_policy.lockout_duration') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="lockout_duration_minutes" id="lockout_duration_minutes"
                                   value="{{ old('lockout_duration_minutes', $settings['lockout_duration_minutes'] ?? 30) }}"
                                   min="0" max="1440"
                                   class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('password_policy.minutes') }}</span>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-400 mt-1">{{ __('password_policy.manual_unlock_hint') }}</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-2 pb-4"
                 x-data="passwordPreview(@js($policyLabels))">
                <div class="relative">
                    <button type="button" @click="showPreview = !showPreview"
                            class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                        {{ __('password_policy.preview_rules') }}
                    </button>
                    <div x-show="showPreview" @click.outside="showPreview = false" x-cloak
                         x-transition
                         class="absolute bottom-full left-0 mb-2 w-80 bg-gray-900 text-white text-xs rounded-lg p-4 shadow-lg z-50">
                        <p class="font-semibold mb-2" x-text="labels?.password_must"></p>
                        <ul class="space-y-1.5 list-disc list-inside">
                            <template x-for="rule in rules" :key="rule">
                                <li x-text="rule"></li>
                            </template>
                        </ul>
                        <p x-show="rules.length === 0" class="text-gray-400 italic" x-text="labels?.no_rules"></p>
                    </div>
                </div>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                    {{ __('common.save') }}
                </button>
            </div>
        </form>
    </div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('passwordPolicyForm', () => ({
        preventReuse: {{ old('password_prevent_reuse', $settings['password_prevent_reuse'] ?? '0') > 0 ? 'true' : 'false' }},
        preventReuseCount: {{ old('password_prevent_reuse', $settings['password_prevent_reuse'] ?? '0') ?: 5 }},
    }));

    Alpine.data('passwordPreview', (labelsFromServer = {}) => ({
        showPreview: false,
        labels: labelsFromServer,
        get rules() {
            const form = this.$root.closest('form') || document.querySelector('form');
            if (!form) return [];
            const L = this.labels || {};
                const val = (name) => {
                    const el = form.querySelector(`[name="${name}"]`);
                    if (!el) return null;
                    if (el.type === 'checkbox') return el.checked ? '1' : '0';
                    return el.value;
                };
                const num = (name) => parseInt(val(name)) || 0;
                const checked = (name) => {
                    const els = form.querySelectorAll(`[name="${name}"]`);
                    for (const el of els) {
                        if (el.type === 'checkbox') return el.checked;
                    }
                    return false;
                };
                const r = [];
                const min = num('password_min_length');
                const max = num('password_max_length');
                if (min > 0) r.push((L.rule_min_chars || '').replace('__MIN__', min));
                if (max > 0 && max < 255) r.push((L.rule_max_chars || '').replace('__MAX__', max));
                if (checked('password_require_uppercase')) r.push(L.rule_uppercase || '');
                if (checked('password_require_lowercase')) r.push(L.rule_lowercase || '');
                if (checked('password_require_number')) r.push(L.rule_number || '');
                if (checked('password_require_special')) r.push(L.rule_special || '');
                const expiry = num('password_expires_days');
                if (expiry > 0) r.push((L.rule_expiry || '').replace('__DAYS__', expiry));
                if (checked('password_force_change_first_login')) r.push(L.rule_first_login || '');
                const reuse = num('password_prevent_reuse');
                if (reuse > 0) r.push((L.rule_reuse || '').replace('__N__', reuse));
                return r.filter(Boolean);
            },
    }));
});
</script>
@endsection
