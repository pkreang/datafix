@extends('layouts.app')
@section('title', __('common.create_purchase_request'))
@section('content')
    <div class="mb-6">
        <a href="{{ route('purchase-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">{{ __('common.create_purchase_request') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.purchase_request_desc') }}</p>
    </div>

    @if ($errors->has('workflow'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200">
            {{ $errors->first('workflow') }}
        </div>
    @endif

    <div x-data="prForm()" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left: Form fields --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])
            <form id="pr-form" method="POST" action="{{ route('purchase-requests.store') }}" class="space-y-3">
                @csrf
                @if($form)
                    <input type="hidden" name="form_key" value="{{ $form->form_key }}">
                @endif
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.department') }}</label>
                    <select name="department_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <option value="">{{ __('common.department_not_selected') }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($form)
                    @foreach($form->fields as $field)
                        @php $name = "form_payload[{$field->field_key}]"; $value = old("form_payload.{$field->field_key}"); @endphp
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $field->label }}
                                @if($field->is_required) <span class="text-red-500">*</span> @endif
                            </label>
                            @include('components.dynamic-field', ['field' => $field, 'name' => $name, 'value' => $value, 'userDeptId' => $userDeptId ?? null])
                        </div>
                    @endforeach
                @endif
                <input type="hidden" name="amount" :value="totalAmount">
            </form>
        </div>

        {{-- Right: Line items --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.line_items') }}</h4>
            <template x-for="(item, index) in items" :key="index">
                <div class="mb-3 p-3 bg-white dark:bg-gray-900/30 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
                    <div>
                        <label class="text-xs text-gray-500">{{ __('common.item_name') }}</label>
                        <input :name="'items['+index+'][item_name]'" x-model="item.item_name" required
                               class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.qty') }}</label>
                            <input :name="'items['+index+'][qty]'" x-model="item.qty" type="number" min="0.01" step="0.01"
                                   @input="updateTotal(item)" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.unit_label') }}</label>
                            <input :name="'items['+index+'][unit]'" x-model="item.unit" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">{{ __('common.unit_price') }}</label>
                            <input :name="'items['+index+'][unit_price]'" x-model="item.unit_price" type="number" min="0" step="0.01"
                                   @input="updateTotal(item)" required
                                   class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        </div>
                    </div>
                    <input type="hidden" :name="'items['+index+'][total_price]'" :value="item.total_price">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            {{ __('common.total_price') }}:
                            <span x-text="Number(item.total_price).toLocaleString('th-TH', {minimumFractionDigits:2})"
                                  class="font-medium text-gray-800 dark:text-gray-200"></span>
                        </span>
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                class="text-xs text-red-500 hover:text-red-700">{{ __('common.remove') }}</button>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">{{ __('common.notes') }}</label>
                        <input :name="'items['+index+'][notes]'" x-model="item.notes"
                               class="w-full mt-0.5 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                </div>
            </template>
            <button type="button" @click="addItem"
                    class="w-full py-2 text-sm text-blue-600 dark:text-blue-400 border border-dashed border-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">
                + {{ __('common.add_line_item') }}
            </button>
            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('common.total_price') }}</span>
                <span x-text="Number(totalAmount).toLocaleString('th-TH', {minimumFractionDigits:2})"
                      class="text-lg font-bold text-blue-600 dark:text-blue-400"></span>
            </div>
            <div class="mt-4">
                <button type="submit" form="pr-form"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    {{ __('common.submit') }}
                </button>
            </div>
        </div>
    </div>

    <script>
    function prForm() {
        return {
            items: [{ item_name: '', qty: 1, unit: '', unit_price: 0, total_price: 0, notes: '' }],
            get totalAmount() {
                return this.items.reduce((s, i) => s + (parseFloat(i.total_price) || 0), 0);
            },
            addItem() {
                this.items.push({ item_name: '', qty: 1, unit: '', unit_price: 0, total_price: 0, notes: '' });
            },
            removeItem(i) {
                if (this.items.length > 1) this.items.splice(i, 1);
            },
            updateTotal(item) {
                item.total_price = ((parseFloat(item.qty) || 0) * (parseFloat(item.unit_price) || 0)).toFixed(2);
            },
        };
    }
    </script>
@endsection
