@php
    $isEdit = isset($runningNumberConfig);
    $action = $isEdit
        ? route('settings.running-numbers.update', $runningNumberConfig)
        : route('settings.running-numbers.store');
@endphp

<div x-data="{
        prefix: '{{ old('prefix', $runningNumberConfig->prefix ?? 'REP') }}',
        digitCount: {{ old('digit_count', $runningNumberConfig->digit_count ?? 5) }},
        resetMode: '{{ old('reset_mode', $runningNumberConfig->reset_mode ?? 'yearly') }}',
        includeYear: {{ old('include_year', $runningNumberConfig->include_year ?? true) ? 'true' : 'false' }},
        includeMonth: {{ old('include_month', $runningNumberConfig->include_month ?? false) ? 'true' : 'false' }},
        get preview() {
            let parts = [this.prefix];
            const now = new Date();
            if (this.includeYear) parts.push(now.getFullYear());
            if (this.includeMonth) parts.push(String(now.getMonth() + 1).padStart(2, '0'));
            parts.push('-');
            parts.push('1'.padStart(this.digitCount, '0'));
            return parts.join('');
        }
    }">

    @if ($errors->any())
        <div class="alert-error mb-4">
            <ul class="text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-5" novalidate>
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">{{ __('common.document_type') }}</label>
                <select name="document_type" @disabled($isEdit) class="form-input mt-1">
                    @if($isEdit)
                        <option value="{{ $runningNumberConfig->document_type }}" selected>{{ $runningNumberConfig->document_type }}</option>
                    @else
                        @foreach($documentTypes as $dt)
                            <option value="{{ $dt->code }}" @selected(old('document_type') === $dt->code)>{{ $dt->label() }} ({{ $dt->code }})</option>
                        @endforeach
                    @endif
                </select>
                @if($isEdit)
                    <input type="hidden" name="document_type" value="{{ $runningNumberConfig->document_type }}">
                @endif
            </div>
            <div>
                <label class="form-label">{{ __('common.running_number_prefix') }}</label>
                <input name="prefix" x-model="prefix" required maxlength="20"
                       class="form-input mt-1" />
            </div>
            <div>
                <label class="form-label">{{ __('common.running_number_digit_count') }}</label>
                <input type="number" name="digit_count" x-model.number="digitCount" min="1" max="10" required
                       class="form-input mt-1" />
            </div>
            <div>
                <label class="form-label">{{ __('common.running_number_reset_mode') }}</label>
                <select name="reset_mode" x-model="resetMode" class="form-input mt-1">
                    <option value="none">{{ __('common.running_number_reset_none') }}</option>
                    <option value="yearly">{{ __('common.running_number_reset_yearly') }}</option>
                    <option value="monthly">{{ __('common.running_number_reset_monthly') }}</option>
                </select>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="include_year" value="0">
                <input type="checkbox" name="include_year" value="1" x-model="includeYear">
                <span class="text-sm text-slate-600 dark:text-slate-300">{{ __('common.running_number_include_year') }}</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="include_month" value="0">
                <input type="checkbox" name="include_month" value="1" x-model="includeMonth">
                <span class="text-sm text-slate-600 dark:text-slate-300">{{ __('common.running_number_include_month') }}</span>
            </label>
            <div class="min-w-[12rem]">
                <x-form.active-toggle
                    name="is_active"
                    :checked="old('is_active', $runningNumberConfig->is_active ?? true)"
                    label-class="block text-sm text-slate-600 dark:text-slate-300 mb-1" />
            </div>
        </div>

        {{-- Live preview --}}
        <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
            <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">{{ __('common.running_number_preview') }}</p>
            <p class="text-lg font-mono font-semibold text-blue-800 dark:text-blue-300" x-text="preview"></p>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('settings.running-numbers.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
            <button class="btn-primary">{{ __('common.save') }}</button>
        </div>
    </form>
</div>
