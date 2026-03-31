@extends('layouts.app')

@section('title', __('common.create_requisition'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('spare-parts.requisition.index') }}" class="text-sm text-blue-600 hover:text-blue-700">&larr; {{ __('common.back') }}</a>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-2">{{ __('common.create_requisition') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.create_requisition_desc') }}</p>
    </div>

    @if ($errors->has('workflow'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200">
            {{ $errors->first('workflow') }}
        </div>
    @endif

    <div x-data="requisitionForm()" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left: Form --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            @include('repair-requests._company_header', ['company' => $company ?? null, 'branch' => $branch ?? null])
            <form method="POST" action="{{ route('spare-parts.requisition.submit') }}" class="space-y-3">
                @csrf
                @if($form)
                    <input type="hidden" name="form_key" value="{{ $form->form_key }}">
                @endif
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.reference_no') }}</label>
                    <input name="reference_no" value="{{ old('reference_no') }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.department') }}</label>
                    <select name="department_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <option value="">{{ __('common.department_not_selected') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($form)
                    @php
                        $layoutCols = (int) ($form->layout_columns ?? 1);
                        $layoutClass = match($layoutCols) {
                            2 => 'grid grid-cols-1 md:grid-cols-2 gap-4',
                            3 => 'grid grid-cols-1 md:grid-cols-3 gap-4',
                            4 => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4',
                            default => 'grid grid-cols-1 gap-4',
                        };
                    @endphp
                    <div class="{{ $layoutClass }}">
                    @foreach($form->fields as $field)
                        @php
                            $name = "form_payload[{$field->field_key}]";
                            $value = old("form_payload.{$field->field_key}");
                            if ($field->field_key === 'parent_reference' && !$value && $parentType && $parentId) {
                                $value = ucfirst(str_replace('_', ' ', $parentType)) . " #{$parentId}";
                            }
                            $isSection = $field->field_type === 'section';
                            $span = $isSection ? $layoutCols : (($field->col_span && $layoutCols > 1) ? min($field->col_span, $layoutCols) : 1);
                        @endphp
                        <div @if($span > 1) style="grid-column: span {{ $span }}" @endif>
                            @if(!$isSection)
                                <label class="text-sm text-gray-600 dark:text-gray-300">{{ $field->label }}</label>
                            @endif
                            @include('components.dynamic-field', ['field' => $field, 'name' => $name, 'value' => $value])
                        </div>
                    @endforeach
                    </div>
                @endif

                <input type="hidden" name="amount" :value="totalAmount">

                {{-- Line Items --}}
                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('common.spare_parts_items') }}</h4>
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex flex-wrap items-end gap-2 mb-2 p-2 bg-white dark:bg-gray-900/20 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex-1 min-w-[180px]">
                                <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.spare_part') }}</label>
                                <select :name="'items['+index+'][spare_part_id]'" x-model="item.spare_part_id" required
                                        @change="updateCost(index)"
                                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                                    <option value="">{{ __('common.please_select') }}</option>
                                    @foreach($spareParts as $sp)
                                        <option value="{{ $sp->id }}" data-cost="{{ $sp->unit_cost }}" data-stock="{{ $sp->current_stock }}">
                                            [{{ $sp->code }}] {{ $sp->name }} ({{ __('common.stock') }}: {{ number_format($sp->current_stock, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24">
                                <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.quantity') }}</label>
                                <input type="number" step="1" min="1" :name="'items['+index+'][quantity]'" x-model="item.quantity" required
                                       @input="calcTotal()"
                                       class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            </div>
                            <div class="w-28">
                                <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.subtotal') }}</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 py-2" x-text="formatNumber(item.quantity * item.unit_cost)"></p>
                            </div>
                            <input type="hidden" :name="'items['+index+'][note]'" value="">
                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 text-sm pb-2">&times;</button>
                        </div>
                    </template>
                    <button type="button" @click="addItem()" class="text-sm text-blue-600 hover:text-blue-700">+ {{ __('common.add_item') }}</button>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-2">
                        {{ __('common.total') }}: <span x-text="formatNumber(totalAmount)"></span> {{ __('common.baht') }}
                    </p>
                </div>

                @error('items')
                    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">{{ __('common.submit') }}</button>
            </form>
        </div>

        {{-- Right: Spare parts catalog quick view --}}
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('common.spare_parts_catalog') }}</h3>
            <div class="space-y-1 max-h-96 overflow-y-auto">
                @foreach($spareParts as $sp)
                    <div class="text-sm p-2 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/20">
                        <span class="font-medium text-gray-900 dark:text-gray-100">[{{ $sp->code }}]</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $sp->name }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ __('common.stock') }}: {{ number_format($sp->current_stock, 0) }} {{ $sp->unit }} · ฿{{ number_format($sp->unit_cost, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        function requisitionForm() {
            const partsData = @json($spareParts->mapWithKeys(fn($sp) => [$sp->id => ['unit_cost' => $sp->unit_cost, 'stock' => $sp->current_stock]]));
            return {
                items: [{ spare_part_id: '', quantity: 1, unit_cost: 0 }],
                totalAmount: 0,
                addItem() {
                    this.items.push({ spare_part_id: '', quantity: 1, unit_cost: 0 });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        this.calcTotal();
                    }
                },
                updateCost(index) {
                    const partId = this.items[index].spare_part_id;
                    this.items[index].unit_cost = partsData[partId]?.unit_cost ?? 0;
                    this.calcTotal();
                },
                calcTotal() {
                    this.totalAmount = this.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost), 0);
                },
                formatNumber(n) {
                    return Number(n || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            };
        }
    </script>
@endsection
