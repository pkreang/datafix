@php
    $isEdit = $lookup !== null;
    $initialItems = old('items', $isEdit ? $lookup->items->map(fn ($i) => [
        'value' => $i->value,
        'label_en' => $i->label_en,
        'label_th' => $i->label_th,
        'parent_id' => $i->parent_id,
        'sort_order' => $i->sort_order,
        'is_active' => (bool) $i->is_active,
        'extra' => $i->extra ? json_encode($i->extra, JSON_UNESCAPED_UNICODE) : '',
    ])->values()->toArray() : []);
    // All items across all active lookup lists, so admin can pick a parent from any list.
    // Grouped by list for a labeled optgroup.
    $parentOptions = \App\Models\LookupList::active()->with('items')->get()->map(fn ($l) => [
        'group' => $l->label_th ?: $l->label_en,
        'items' => $l->items->map(fn ($i) => ['id' => $i->id, 'label' => ($i->label_th ?: $i->label_en).' ['.$i->value.']'])->values(),
    ]);
@endphp

@if (session('success'))
    <div class="alert-success mb-4">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert-error mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card p-4 md:p-6 mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="form-label">{{ __('common.lookup_list_key') }}</label>
        <input type="text" name="key" value="{{ old('key', $lookup?->key) }}"
               class="form-input font-mono"
               placeholder="equipment_brand"
               {{ $isEdit && $lookup->is_system ? 'readonly' : '' }}>
        <p class="text-xs text-slate-400 mt-1">{{ __('common.lookup_list_key_help') }}</p>
    </div>
    <div>
        <label class="form-label">{{ __('common.sort_order') }}</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $lookup?->sort_order ?? 0) }}" class="form-input" min="0">
    </div>
    <div>
        <label class="form-label">{{ __('common.name') }} (TH)</label>
        <input type="text" name="label_th" value="{{ old('label_th', $lookup?->label_th) }}" class="form-input">
    </div>
    <div>
        <label class="form-label">{{ __('common.name') }} (EN)</label>
        <input type="text" name="label_en" value="{{ old('label_en', $lookup?->label_en) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="form-label">{{ __('common.description') }}</label>
        <textarea name="description" rows="2" class="form-input">{{ old('description', $lookup?->description) }}</textarea>
    </div>
    <div>
        <label class="form-label">{{ __('common.lookup_required_permission') }}</label>
        <input type="text" name="required_permission" value="{{ old('required_permission', $lookup?->required_permission) }}"
               class="form-input font-mono text-xs" placeholder="e.g. hr.view_sensitive_lists">
        <p class="text-xs text-slate-400 mt-1">{{ __('common.lookup_required_permission_help') }}</p>
    </div>
    <div>
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $lookup?->is_active ?? true) ? 'checked' : '' }}>
            <span class="text-sm">{{ __('common.active') }}</span>
        </label>
    </div>
</div>

<div class="card p-4 md:p-6 mb-4" x-data="lookupItemsEditor({{ Js::from($initialItems) }})">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('common.lookup_items') }}</h3>
        <button type="button" @click="addItem()" class="btn-secondary text-sm">
            + {{ __('common.add') }}
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500">
                    <th class="py-2 pr-2 w-32">{{ __('common.lookup_item_value') }}</th>
                    <th class="py-2 pr-2">{{ __('common.name') }} (TH)</th>
                    <th class="py-2 pr-2">{{ __('common.name') }} (EN)</th>
                    <th class="py-2 pr-2 w-48">{{ __('common.lookup_parent_item') }}</th>
                    <th class="py-2 pr-2 w-20">{{ __('common.sort_order') }}</th>
                    <th class="py-2 pr-2 w-16 text-center">{{ __('common.active') }}</th>
                    <th class="py-2 pr-2 w-40">extra (JSON)</th>
                    <th class="py-2 w-10"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, idx) in items" :key="idx">
                    <tr class="border-t border-slate-200 dark:border-slate-700">
                        <td class="py-2 pr-2"><input type="text" :name="`items[${idx}][value]`" x-model="item.value" class="form-input font-mono text-xs" placeholder="code"></td>
                        <td class="py-2 pr-2"><input type="text" :name="`items[${idx}][label_th]`" x-model="item.label_th" class="form-input text-sm"></td>
                        <td class="py-2 pr-2"><input type="text" :name="`items[${idx}][label_en]`" x-model="item.label_en" class="form-input text-sm"></td>
                        <td class="py-2 pr-2">
                            <select :name="`items[${idx}][parent_id]`" x-model="item.parent_id" class="form-input text-xs">
                                <option value="">—</option>
                                @foreach($parentOptions as $group)
                                    <optgroup label="{{ $group['group'] }}">
                                        @foreach($group['items'] as $opt)
                                            <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </td>
                        <td class="py-2 pr-2"><input type="number" :name="`items[${idx}][sort_order]`" x-model.number="item.sort_order" class="form-input text-sm" min="0"></td>
                        <td class="py-2 pr-2 text-center">
                            <input type="checkbox" :name="`items[${idx}][is_active]`" value="1" x-model="item.is_active">
                        </td>
                        <td class="py-2 pr-2"><input type="text" :name="`items[${idx}][extra]`" x-model="item.extra" class="form-input text-xs" placeholder='{"color":"red"}'></td>
                        <td class="py-2 text-right">
                            <button type="button" @click="removeItem(idx)" class="text-red-500 hover:text-red-700" title="{{ __('common.delete') }}">&times;</button>
                        </td>
                    </tr>
                </template>
                <template x-if="items.length === 0">
                    <tr><td colspan="8" class="py-6 text-center text-slate-400 text-sm">{{ __('common.lookups_empty_items') }}</td></tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<div class="flex items-center justify-end gap-2">
    <a href="{{ route('settings.lookups.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
    <button type="submit" class="btn-primary">{{ __('common.save') }}</button>
</div>

@push('scripts')
<script>
    function lookupItemsEditor(initial) {
        return {
            items: (initial || []).map((i) => ({ ...i, is_active: Boolean(i.is_active) })),
            addItem() {
                this.items.push({ value: '', label_th: '', label_en: '', parent_id: '', sort_order: this.items.length, is_active: true, extra: '' });
            },
            removeItem(idx) { this.items.splice(idx, 1); },
        };
    }
</script>
@endpush
