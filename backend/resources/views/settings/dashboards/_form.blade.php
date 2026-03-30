@php
    $isEdit = isset($dashboard);
    $action = $isEdit
        ? route('settings.dashboards.update', $dashboard)
        : route('settings.dashboards.store');
    $initialWidgets = $initialWidgets ?? [];
@endphp

<div x-data="dashboardBuilder({{ Js::from($initialWidgets) }}, {{ Js::from($dataSources) }})">
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        {{-- Dashboard Metadata --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.name') }} <span class="text-red-500">*</span></label>
                <input name="name" value="{{ old('name', $dashboard->name ?? '') }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-300">Layout Columns</label>
                <select name="layout_columns"
                        class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    @php $layoutCols = (int) old('layout_columns', $dashboard->layout_columns ?? 2); @endphp
                    <option value="1" @selected($layoutCols === 1)>1 Column</option>
                    <option value="2" @selected($layoutCols === 2)>2 Columns</option>
                    <option value="3" @selected($layoutCols === 3)>3 Columns</option>
                    <option value="4" @selected($layoutCols === 4)>4 Columns</option>
                </select>
            </div>
            <div x-data="{ visibility: '{{ old('visibility', $dashboard->visibility ?? 'all') }}' }">
                <label class="text-sm text-gray-600 dark:text-gray-300">Visibility <span class="text-red-500">*</span></label>
                <select name="visibility" x-model="visibility"
                        class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="all">All Users</option>
                    <option value="permission">By Permission</option>
                </select>
                <div x-show="visibility === 'permission'" class="mt-2">
                    <label class="text-sm text-gray-600 dark:text-gray-300">Required Permission</label>
                    <input name="required_permission" value="{{ old('required_permission', $dashboard->required_permission ?? '') }}"
                           placeholder="e.g. dashboard.view_custom"
                           class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                </div>
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $dashboard->is_active ?? true))>
                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.active') }}</span>
                </label>
            </div>
        </div>

        <div>
            <label class="text-sm text-gray-600 dark:text-gray-300">{{ __('common.remark') }}</label>
            <textarea name="description" rows="2"
                      class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description', $dashboard->description ?? '') }}</textarea>
        </div>

        {{-- Widgets Section --}}
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Widgets</h3>
            <div class="flex gap-2">
                <button type="button" @click="showPreview = true"
                        class="px-3 py-2 rounded bg-gray-500 text-white text-sm">Preview</button>
                <button type="button" @click="addWidget()"
                        class="px-3 py-2 rounded bg-blue-600 text-white text-sm">+ Add Widget</button>
            </div>
        </div>

        <template x-for="(widget, idx) in widgets" :key="idx">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/20 p-4 space-y-3">
                {{-- Widget header --}}
                <div class="flex justify-between items-center">
                    <p class="font-medium text-gray-800 dark:text-gray-200">Widget <span x-text="idx + 1"></span></p>
                    <div class="space-x-2">
                        <button type="button" @click="moveUp(idx)"
                                class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-300">{{ __('common.move_up') }}</button>
                        <button type="button" @click="moveDown(idx)"
                                class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-300">{{ __('common.move_down') }}</button>
                        <button type="button" @click="removeWidget(idx)"
                                class="px-2 py-1 rounded bg-red-600 text-white text-xs">{{ __('common.delete') }}</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {{-- Title --}}
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Title</label>
                        <input :name="`widgets[${idx}][title]`" x-model="widget.title" required
                               class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                    </div>

                    {{-- Col Span --}}
                    <div>
                        <label class="text-xs text-gray-500">Col Span</label>
                        <select :name="`widgets[${idx}][col_span]`" x-model="widget.col_span"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="0">Auto</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4 (Full)</option>
                        </select>
                    </div>

                    {{-- Data Source --}}
                    <div>
                        <label class="text-xs text-gray-500">Data Source</label>
                        <select :name="`widgets[${idx}][data_source]`" x-model="widget.data_source"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <template x-for="[key, src] in Object.entries(dataSources)" :key="key">
                                <option :value="key" x-text="src.label_en"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Widget Type --}}
                    <div>
                        <label class="text-xs text-gray-500">Widget Type</label>
                        <select :name="`widgets[${idx}][widget_type]`" x-model="widget.widget_type"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="metric">Metric</option>
                            <option value="chart">Chart</option>
                            <option value="table">Table</option>
                        </select>
                    </div>
                </div>

                {{-- Metric config --}}
                <div x-show="widget.widget_type === 'metric'"
                     class="grid grid-cols-1 md:grid-cols-3 gap-3 border-t border-gray-100 dark:border-gray-700 pt-3">
                    <div>
                        <label class="text-xs text-gray-500">Aggregation</label>
                        <select x-model="widget.aggregation"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="count">Count</option>
                            <option value="sum">Sum</option>
                            <option value="avg">Average</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Field</label>
                        <select x-model="widget.config_field"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <template x-for="[fkey, flabel] in Object.entries(getSourceFields(widget.data_source, 'aggregate_fields'))" :key="fkey">
                                <option :value="fkey" x-text="flabel"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Date Field</label>
                        <select x-model="widget.date_field"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">None</option>
                            <template x-for="[dkey, dlabel] in Object.entries(getSourceFields(widget.data_source, 'date_fields'))" :key="dkey">
                                <option :value="dkey" x-text="dlabel"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Chart config --}}
                <div x-show="widget.widget_type === 'chart'"
                     class="grid grid-cols-1 md:grid-cols-3 gap-3 border-t border-gray-100 dark:border-gray-700 pt-3">
                    <div>
                        <label class="text-xs text-gray-500">Chart Type</label>
                        <select x-model="widget.chart_type"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="bar">Bar</option>
                            <option value="line">Line</option>
                            <option value="pie">Pie</option>
                            <option value="donut">Donut</option>
                            <option value="area">Area</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Aggregation</label>
                        <select x-model="widget.aggregation"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="count">Count</option>
                            <option value="sum">Sum</option>
                            <option value="avg">Average</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Field</label>
                        <select x-model="widget.config_field"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <template x-for="[fkey, flabel] in Object.entries(getSourceFields(widget.data_source, 'aggregate_fields'))" :key="fkey">
                                <option :value="fkey" x-text="flabel"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Group By</label>
                        <select x-model="widget.group_by"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">None</option>
                            <template x-for="[gkey, glabel] in Object.entries(getSourceFields(widget.data_source, 'group_by_fields'))" :key="gkey">
                                <option :value="gkey" x-text="glabel"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Date Field</label>
                        <select x-model="widget.date_field"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">None</option>
                            <template x-for="[dkey, dlabel] in Object.entries(getSourceFields(widget.data_source, 'date_fields'))" :key="dkey">
                                <option :value="dkey" x-text="dlabel"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Table config --}}
                <div x-show="widget.widget_type === 'table'"
                     class="grid grid-cols-1 md:grid-cols-2 gap-3 border-t border-gray-100 dark:border-gray-700 pt-3">
                    <div>
                        <label class="text-xs text-gray-500 block mb-2">Columns</label>
                        <div class="space-y-1">
                            <template x-for="[ckey, clabel] in Object.entries(getSourceFields(widget.data_source, 'display_columns'))" :key="ckey">
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="checkbox"
                                           :checked="isColumnSelected(widget, ckey)"
                                           @change="toggleColumn(widget, ckey)"
                                           class="rounded text-blue-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300" x-text="clabel"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Per Page</label>
                        <select x-model="widget.per_page"
                                class="mt-1 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>

                {{-- Hidden inputs --}}
                <input type="hidden" :name="`widgets[${idx}][title]`" :value="widget.title">
                <input type="hidden" :name="`widgets[${idx}][widget_type]`" :value="widget.widget_type">
                <input type="hidden" :name="`widgets[${idx}][data_source]`" :value="widget.data_source">
                <input type="hidden" :name="`widgets[${idx}][aggregation]`" :value="widget.aggregation">
                <input type="hidden" :name="`widgets[${idx}][config_field]`" :value="widget.config_field">
                <input type="hidden" :name="`widgets[${idx}][chart_type]`" :value="widget.chart_type">
                <input type="hidden" :name="`widgets[${idx}][group_by]`" :value="widget.group_by">
                <input type="hidden" :name="`widgets[${idx}][date_field]`" :value="widget.date_field">
                <input type="hidden" :name="`widgets[${idx}][table_columns]`" :value="widget.table_columns">
                <input type="hidden" :name="`widgets[${idx}][per_page]`" :value="widget.per_page">
                <input type="hidden" :name="`widgets[${idx}][col_span]`" :value="widget.col_span">
            </div>
        </template>

        <div x-show="widgets.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
            No widgets yet. Click "Add Widget" to get started.
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                {{ __('common.save') }}
            </button>
            <a href="{{ route('settings.dashboards.index') }}" class="px-6 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                {{ __('common.cancel') }}
            </a>
        </div>
    </form>

    {{-- Preview Modal --}}
    <div x-show="showPreview" x-cloak
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showPreview = false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl max-h-[80vh] overflow-y-auto p-6"
             @click.outside="showPreview = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dashboard Preview</h3>
                <button @click="showPreview = false" type="button"
                        class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid gap-4" :class="`grid-cols-2`">
                <template x-for="(widget, idx) in widgets" :key="idx">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 p-4"
                         :class="widget.col_span > 0 ? `col-span-${widget.col_span}` : ''">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="widget.title"></p>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                  :class="{
                                      'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400': widget.widget_type === 'metric',
                                      'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400': widget.widget_type === 'chart',
                                      'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400': widget.widget_type === 'table',
                                  }"
                                  x-text="widget.widget_type">
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Source: <span x-text="dataSources[widget.data_source]?.label_en || widget.data_source"></span>
                        </p>
                        <template x-if="widget.widget_type === 'chart'">
                            <p class="text-xs text-gray-400 mt-1" x-text="widget.chart_type + ' chart'"></p>
                        </template>
                    </div>
                </template>
                <div x-show="widgets.length === 0" class="col-span-2 text-center text-sm text-gray-500 py-8">
                    No widgets to preview.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardBuilder(initialWidgets, dataSources) {
    return {
        widgets: initialWidgets || [],
        dataSources: dataSources || {},
        showPreview: false,

        addWidget() {
            const firstSource = Object.keys(this.dataSources)[0] || '';
            this.widgets.push({
                title: 'New Widget',
                widget_type: 'metric',
                data_source: firstSource,
                aggregation: 'count',
                config_field: 'id',
                chart_type: 'bar',
                group_by: '',
                date_field: '',
                table_columns: '[]',
                per_page: 10,
                col_span: 0,
            });
        },

        removeWidget(idx) {
            this.widgets.splice(idx, 1);
        },

        moveUp(idx) {
            if (idx > 0) {
                [this.widgets[idx - 1], this.widgets[idx]] = [this.widgets[idx], this.widgets[idx - 1]];
                this.widgets = [...this.widgets];
            }
        },

        moveDown(idx) {
            if (idx < this.widgets.length - 1) {
                [this.widgets[idx + 1], this.widgets[idx]] = [this.widgets[idx], this.widgets[idx + 1]];
                this.widgets = [...this.widgets];
            }
        },

        getSourceFields(source, type) {
            return this.dataSources[source]?.[type] || {};
        },

        isColumnSelected(widget, colKey) {
            try {
                const cols = JSON.parse(widget.table_columns || '[]');
                return Array.isArray(cols) && cols.includes(colKey);
            } catch {
                return false;
            }
        },

        toggleColumn(widget, colKey) {
            try {
                let cols = JSON.parse(widget.table_columns || '[]');
                if (!Array.isArray(cols)) cols = [];
                const idx = cols.indexOf(colKey);
                if (idx === -1) {
                    cols.push(colKey);
                } else {
                    cols.splice(idx, 1);
                }
                widget.table_columns = JSON.stringify(cols);
            } catch {
                widget.table_columns = JSON.stringify([colKey]);
            }
        },
    };
}
</script>
