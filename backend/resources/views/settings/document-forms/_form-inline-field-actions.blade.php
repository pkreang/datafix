{{-- +ฟิลด์ / +หัวข้อ — in document flow after remark (scrolls with page) --}}
<div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-200/80 pt-4 mt-1 dark:border-gray-600">
    <button type="button" @click="addField()"
            class="px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">+ {{ __('common.document_form_add_field') }}</button>
    <button type="button" @click="addSection()"
            class="px-3 py-2 rounded bg-gray-500 text-white text-sm hover:bg-gray-600">+ {{ __('common.document_form_add_section') }}</button>
</div>
