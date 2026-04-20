# UX/UI Fix Spec — Data Flow (CMMS)

**Stack:** Laravel 12 · Blade + Alpine.js · Tailwind v4  
**Layout file:** `backend/resources/views/layouts/app.blade.php`  
**CSS:** `backend/resources/css/app.css`

---

## Priority 1 — Critical (Accessibility)

### 1.1 Base font size ≥ 16px
**File:** `app.css`  
**Issue:** Body uses 14px — below WCAG minimum for mobile  
**Fix:** Set base font to 16px; use 14px (`text-sm`) only for secondary/meta text

```css
/* app.css */
body {
  font-size: 1rem; /* 16px */
}
```

Keep `text-sm` on: badges, table headers, timestamps, helper text only.

---

### 1.2 Touch targets ≥ 44px
**Files:** all table action buttons, pagination controls  
**Issue:** Action dropdown trigger buttons are too small on mobile  
**Fix:** Ensure min `h-11 w-11` (44px) on icon-only buttons

```html
<!-- before -->
<button class="p-1.5">...</button>
<!-- after -->
<button class="p-2.5 min-h-[44px] min-w-[44px]">...</button>
```

---

## Priority 2 — High (UX)

### 2.1 Breadcrumb component
**File:** `layouts/app.blade.php` + add `@stack('breadcrumb')`  
**Issue:** Users lose context on nested pages (form create/edit, detail views)  
**Fix:** Add breadcrumb slot below header; push from each page

```blade
{{-- layouts/app.blade.php — add after header --}}
@hasSection('breadcrumb')
<div class="px-4 sm:px-6 lg:px-10 py-2 border-b border-slate-100 dark:border-slate-800 text-sm text-slate-500 dark:text-slate-400">
    @yield('breadcrumb')
</div>
@endif

{{-- usage in pages (e.g. users/create.blade.php) --}}
@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="hover:text-blue-600">ผู้ใช้งาน</a>
    <span class="mx-1.5">/</span>
    <span class="text-slate-700 dark:text-slate-300">เพิ่มผู้ใช้</span>
@endsection
```

---

### 2.2 Approval card — status urgency
**File:** `approvals/my-approvals.blade.php`  
**Issue:** All approval cards look identical — no visual priority cue  
**Fix:** Add left color border based on age/status

```blade
{{-- compute $urgencyClass in controller or blade --}}
@php
  $urgencyClass = match(true) {
    $item->created_at->diffInDays() >= 3 => 'border-l-4 border-l-red-500',
    $item->created_at->diffInDays() >= 1 => 'border-l-4 border-l-amber-400',
    default => 'border-l-4 border-l-slate-200 dark:border-l-slate-700',
  };
@endphp

<div class="card p-4 {{ $urgencyClass }}">
```

---

### 2.3 Empty state — actionable
**Files:** all `index.blade.php` table empty rows  
**Issue:** Shows only "ไม่มีข้อมูล" — no guidance  
**Fix:** Replace with icon + message + CTA button

```blade
{{-- replace empty <td> text with: --}}
<tr>
  <td colspan="{{ $colCount }}" class="px-6 py-16 text-center">
    <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-500">
      <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4" />
      </svg>
      <p class="text-sm font-medium text-slate-500 dark:text-slate-400">ยังไม่มีข้อมูล</p>
      {{-- add CTA per page, e.g.: --}}
      @can('create-users')
      <a href="{{ route('users.create') }}" class="btn-primary text-xs">+ เพิ่มผู้ใช้งาน</a>
      @endcan
    </div>
  </td>
</tr>
```

---

### 2.4 Skeleton loader — table & dashboard widgets
**Files:** all index pages, `reports/dashboards/show.blade.php`  
**Issue:** Blank/jumping layout during API/page load  
**Fix:** Add skeleton rows while `$loading` (Alpine.js)

```blade
{{-- skeleton row component (add to components/skeleton-rows.blade.php) --}}
@props(['rows' => 5, 'cols' => 4])
@for ($r = 0; $r < $rows; $r++)
<tr class="animate-pulse">
  @for ($c = 0; $c < $cols; $c++)
  <td class="px-6 py-4">
    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-{{ $c === 0 ? '3/4' : '1/2' }}"></div>
  </td>
  @endfor
</tr>
@endfor

{{-- usage with Alpine.js --}}
<template x-if="loading"><x-skeleton-rows :rows="5" :cols="4" /></template>
<template x-if="!loading"><!-- actual rows --></template>
```

---

## Priority 3 — Medium (Polish)

### 3.1 Real-time field validation
**File:** any page with `form-input`  
**Issue:** Errors only show on submit  
**Fix:** Add Alpine.js blur validation for required fields

```blade
<input
  type="text"
  class="form-input"
  x-model="field"
  @blur="if (!field) $el.classList.add('form-input-error')"
  @input="$el.classList.remove('form-input-error')"
>
```

---

### 3.2 prefers-reduced-motion
**File:** `app.css`  
**Issue:** Animations play even when OS has motion disabled  
**Fix:** Add media query guard

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

### 3.3 Consistent button loading state
**Files:** all forms with submit button  
**Issue:** Some buttons show spinner on submit, some don't  
**Fix:** Standardize with Alpine.js

```blade
<button
  type="submit"
  class="btn-primary"
  x-data="{ loading: false }"
  @click="loading = true"
  :disabled="loading"
  :class="{ 'opacity-60 cursor-not-allowed': loading }"
>
  <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
  </svg>
  <span x-text="loading ? 'กำลังบันทึก...' : 'บันทึก'"></span>
</button>
```

---

## Reference

| ไฟล์ | บทบาท |
|------|-------|
| `layouts/app.blade.php` | Layout หลัก — breadcrumb slot, header |
| `resources/css/app.css` | CSS variables, base styles |
| `approvals/my-approvals.blade.php` | Approval urgency card |
| `users/index.blade.php` | Empty state ตัวอย่าง |
| `reports/dashboards/show.blade.php` | Skeleton loader (widget) |
| `components/` | เพิ่ม `skeleton-rows.blade.php` ใหม่ |

**Design tokens ที่ใช้อยู่:** `btn-primary`, `btn-secondary`, `btn-danger`, `form-input`, `form-input-error`, `card`, `badge-*`, `alert-*`  
**อย่าเปลี่ยน** token เหล่านี้ — แก้เฉพาะ definition ใน `app.css` ถ้าจำเป็น
