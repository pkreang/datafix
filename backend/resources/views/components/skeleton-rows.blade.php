@props(['rows' => 5, 'cols' => 4])
@for ($r = 0; $r < $rows; $r++)
<tr class="animate-pulse">
    @for ($c = 0; $c < $cols; $c++)
    <td class="px-6 py-4">
        <div @class([
            'h-4 bg-slate-200 dark:bg-slate-700 rounded max-w-full',
            'w-3/4' => $c === 0,
            'w-1/2' => $c !== 0,
        ])></div>
    </td>
    @endfor
</tr>
@endfor
