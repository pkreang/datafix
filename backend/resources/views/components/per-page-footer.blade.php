@props([
    'paginator',
    'perPage' => 10,
    'id' => 'pagination-footer',
])
{{--
    Per-page selector + pagination links footer.
    Usage: <x-per-page-footer :paginator="$users" :perPage="$perPage" id="users-pagination" />
--}}
<div id="{{ $id }}" class="mt-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
        <span>{{ __('common.show') }}</span>
        <select onchange="window.location.href=this.value"
                class="form-input py-1 px-2 text-sm w-auto">
            @foreach ([10, 25, 50] as $option)
                <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}"
                        @selected($perPage === $option)>{{ $option }}</option>
            @endforeach
        </select>
        <span>{{ __('common.per_page') }}</span>
    </div>
    @if ($paginator->hasPages())
        <div>{{ $paginator->links() }}</div>
    @endif
</div>
