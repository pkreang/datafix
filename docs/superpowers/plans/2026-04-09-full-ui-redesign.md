# Full UI Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign all ~105 Blade views using a Design System First approach — CSS tokens and utility classes in `app.css`, then migrate every view to use them, producing a modern Soft UI Evolution look with white elevated cards on a slate-50 background.

**Architecture:** Define all design tokens (shadows, colors, radii) as CSS custom properties, then create Tailwind `@apply`-based utility classes (`.card`, `.btn-primary`, `.form-input`, etc.). Migrate all Blade views to use these classes instead of inline Tailwind chains. Zero PHP logic changes.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Tailwind v4, `resources/css/app.css`

**Spec:** `docs/superpowers/specs/2026-04-09-full-ui-redesign-design.md`

---

## Files Modified

| File | Change |
|------|--------|
| `resources/css/app.css` | Add design tokens + all utility classes |
| `resources/views/layouts/app.blade.php` | Body bg, sidebar gradient, header shadow |
| `resources/views/layouts/auth-guest.blade.php` | Left panel gradient, card radius/shadow |
| `resources/views/auth/login.blade.php` | Refactor to extend auth-guest, remove inline styles |
| `resources/views/auth/forgot-password.blade.php` | Replace login-form-* with utility classes |
| `resources/views/auth/reset-password.blade.php` | Replace login-form-* with utility classes |
| `resources/views/components/sidebar-menu.blade.php` | Active/hover state classes |
| `resources/views/components/kpi-card.blade.php` | `.card` wrapper |
| `resources/views/components/notification-bell.blade.php` | Border, shadow, bg classes |
| `resources/views/dashboard.blade.php` | `.card` |
| `resources/views/users/*.blade.php` (5 files) | `.card`, `.btn-*`, `.form-*`, `.alert-*`, `.badge-*` |
| `resources/views/roles/*.blade.php` (4 files) | same pattern |
| `resources/views/permissions/*.blade.php` (3 files) | same pattern |
| `resources/views/repair-requests/*.blade.php` (6 files) | same pattern |
| `resources/views/approvals/*.blade.php` (1 file) | same pattern |
| `resources/views/forms/*.blade.php` (5 files) | same pattern |
| `resources/views/equipment-registry/*.blade.php` (3 files) | same pattern |
| `resources/views/equipment-locations/*.blade.php` (1 file) | same pattern |
| `resources/views/spare-parts/*.blade.php` (5 files) | same pattern |
| `resources/views/maintenance/*.blade.php` (4 files) | same pattern |
| `resources/views/purchase-requests/*.blade.php` (3 files) | same pattern |
| `resources/views/purchase-orders/*.blade.php` (3 files) | same pattern |
| `resources/views/companies/*.blade.php` (5 files) | same pattern |
| `resources/views/profile/*.blade.php` (2 files) | same pattern |
| `resources/views/notifications/index.blade.php` | same pattern |
| `resources/views/reports/*.blade.php` (4 files) | same pattern |
| `resources/views/settings/**/*.blade.php` (~30 files) | same pattern |

---

## Class Migration Reference

Use this table for every view migration task:

| Old classes | New class |
|-------------|-----------|
| `bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700[/50]` | `.card` |
| `bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700` | `.card` |
| `px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition` | `.btn-primary` |
| `inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition` | `.btn-primary` |
| `px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition` | `.btn-secondary` |
| `w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm ... bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100` | `.form-input` |
| `block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1` | `.form-label` |
| `p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg` | `.alert-success` |
| `p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg` | `.alert-error` |
| `p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg` | `.alert-warning` |
| `p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg` | `.alert-info` |
| `bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 ... rounded-full` | `.badge-green` |
| `bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 ... rounded-full` | `.badge-red` |
| `bg-blue-100 text-blue-800 ... rounded-full` | `.badge-blue` |
| `bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-visible` (table container) | `.table-wrapper overflow-visible` |
| `text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider` (th) | `.table-header` |
| `hover:bg-gray-200 dark:hover:bg-gray-700` (tr) | `hover:bg-slate-50 dark:hover:bg-slate-700/50` |

---

## Task 1: CSS Design Tokens and Utility Classes

**Files:**
- Modify: `backend/resources/css/app.css`

- [ ] **Step 1: Add Plus Jakarta Sans to font import**

In `resources/views/layouts/app.blade.php` and `resources/views/layouts/auth-guest.blade.php`, replace the Google Fonts `<link>` with:

```html
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
```

- [ ] **Step 2: Update `@theme` font stack in `app.css`**

Replace the existing `@theme` block:

```css
@theme {
    --font-sans: 'Plus Jakarta Sans', 'Inter', 'Noto Sans Thai', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';
}
```

- [ ] **Step 3: Add design tokens after the `@theme` block**

```css
@layer base {
    :root {
        --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
    }
    .dark {
        --shadow-xs: 0 1px 2px rgba(0,0,0,0.2);
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.25), 0 1px 2px rgba(0,0,0,0.15);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.3), 0 2px 4px -1px rgba(0,0,0,0.2);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.35), 0 4px 6px -2px rgba(0,0,0,0.2);
    }
}
```

- [ ] **Step 4: Replace existing typography utilities and add all new component classes**

Replace the existing block of utility classes (`.page-title` through `.badge-base`) with:

```css
/* Typography */
.page-title    { @apply text-xl font-semibold text-slate-800 dark:text-slate-100; }
.section-title { @apply text-base font-semibold text-slate-800 dark:text-slate-200; }

/* Surface */
.card {
    @apply bg-white dark:bg-slate-800
           rounded-[12px]
           border border-slate-200 dark:border-slate-700
           shadow-[var(--shadow-sm)];
}
.card-header {
    @apply px-6 py-4 border-b border-slate-200 dark:border-slate-700;
}

/* Buttons */
.btn-primary {
    @apply inline-flex items-center justify-center gap-2
           px-4 py-2 rounded-lg text-sm font-medium
           bg-blue-600 hover:bg-blue-700 text-white
           transition-colors duration-200 cursor-pointer
           disabled:opacity-60 disabled:cursor-not-allowed
           shadow-[var(--shadow-xs)];
}
.btn-secondary {
    @apply inline-flex items-center justify-center gap-2
           px-4 py-2 rounded-lg text-sm font-medium
           bg-slate-100 hover:bg-slate-200
           dark:bg-slate-700 dark:hover:bg-slate-600
           text-slate-700 dark:text-slate-200
           transition-colors duration-200 cursor-pointer;
}
.btn-danger {
    @apply inline-flex items-center justify-center gap-2
           px-4 py-2 rounded-lg text-sm font-medium
           bg-red-600 hover:bg-red-700 text-white
           transition-colors duration-200 cursor-pointer;
}

/* Forms */
.form-label {
    @apply block text-sm font-medium
           text-slate-700 dark:text-slate-300
           mb-1;
}
.form-input {
    @apply w-full px-3 py-2 rounded-lg text-sm
           bg-white dark:bg-slate-900
           border border-slate-200 dark:border-slate-600
           text-slate-900 dark:text-slate-100
           placeholder:text-slate-400
           focus:ring-2 focus:ring-blue-500 focus:border-transparent
           outline-none transition duration-200;
}
.form-input-error {
    @apply border-red-500 ring-2 ring-red-500/30 focus:ring-red-500;
}

/* Badges */
.badge-base   { @apply text-xs font-medium px-2.5 py-0.5 rounded-full; }
.badge-green  { @apply badge-base bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400; }
.badge-red    { @apply badge-base bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400; }
.badge-blue   { @apply badge-base bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400; }
.badge-yellow { @apply badge-base bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400; }
.badge-gray   { @apply badge-base bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400; }

/* Alerts */
.alert-base    { @apply p-4 rounded-lg border-l-4 text-sm; }
.alert-success { @apply alert-base bg-green-50 border-green-500 text-green-800 dark:bg-green-900/20 dark:text-green-200; }
.alert-error   { @apply alert-base bg-red-50 border-red-500 text-red-800 dark:bg-red-900/20 dark:text-red-200; }
.alert-warning { @apply alert-base bg-amber-50 border-amber-500 text-amber-800 dark:bg-amber-900/20 dark:text-amber-200; }
.alert-info    { @apply alert-base bg-blue-50 border-blue-500 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200; }

/* Tables */
/* overflow-visible so absolute action-menu dropdowns aren't clipped (CLAUDE.md gotcha #2) */
.table-wrapper {
    @apply bg-white dark:bg-slate-800
           rounded-[12px]
           border border-slate-200 dark:border-slate-700
           shadow-[var(--shadow-sm)]
           overflow-visible;
}
.table-header  { @apply text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider; }
.table-primary { @apply text-sm font-medium text-slate-900 dark:text-slate-100; }
.table-sub     { @apply text-xs text-slate-500 dark:text-slate-400; }
```

- [ ] **Step 5: Run tests to confirm nothing broken**

```bash
cd backend && php artisan test
```
Expected: All tests pass (ExampleTest + PurchaseWorkflowTest).

- [ ] **Step 6: Commit**

```bash
git add backend/resources/css/app.css backend/resources/views/layouts/app.blade.php backend/resources/views/layouts/auth-guest.blade.php
git commit -m "feat(ui): add design tokens and utility classes to app.css"
```

---

## Task 2: Layout Shell — app.blade.php

**Files:**
- Modify: `backend/resources/views/layouts/app.blade.php`

- [ ] **Step 1: Update `<body>` background**

Change:
```html
<body class="h-full font-sans antialiased bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200"
```
To:
```html
<body class="h-full font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200"
```

- [ ] **Step 2: Update sidebar background to gradient**

Change:
```html
<aside class="fixed inset-y-0 left-0 z-30 bg-blue-600 flex flex-col transform transition-all duration-200 ease-in-out -translate-x-full lg:translate-x-0"
```
To:
```html
<aside class="fixed inset-y-0 left-0 z-30 bg-gradient-to-b from-blue-800 to-blue-700 flex flex-col transform transition-all duration-200 ease-in-out -translate-x-full lg:translate-x-0"
```

- [ ] **Step 3: Update sidebar border accents**

Change all `border-blue-500/40` to `border-white/10`:
- Line with `border-b border-blue-500/40` (brand header) → `border-b border-white/10`
- Line with `border-t border-blue-500/40` (user strip) → `border-t border-white/10`

- [ ] **Step 4: Update header classes**

Change:
```html
<header class="sticky top-0 z-20 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between gap-4 px-4 sm:px-8">
```
To:
```html
<header class="sticky top-0 z-20 h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 shadow-[0_1px_3px_rgba(0,0,0,0.06)] flex items-center justify-between gap-4 px-4 sm:px-8">
```

- [ ] **Step 5: Update header dark mode toggle button**

Change:
```html
class="p-1.5 rounded-lg transition-colors
       text-gray-500 dark:text-gray-400
       hover:bg-gray-100 dark:hover:bg-gray-700"
```
To:
```html
class="p-2 rounded-lg transition-colors
       text-slate-500 dark:text-slate-400
       hover:bg-slate-100 dark:hover:bg-slate-800"
```

- [ ] **Step 6: Update header page title**

Change:
```html
<h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 truncate">
```
To:
```html
<h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100 truncate">
```

- [ ] **Step 7: Update main wrapper — remove bg-white**

Change:
```html
<div class="flex-1 min-w-0 flex flex-col gap-4 bg-white dark:bg-gray-900">
```
To:
```html
<div class="flex-1 min-w-0 flex flex-col gap-4">
```

- [ ] **Step 8: Update user dropdown panel**

Change:
```html
class="absolute right-0 top-10 w-52 z-50
       bg-white dark:bg-gray-800
       border border-gray-200 dark:border-gray-700
       rounded-xl shadow-lg py-1"
```
To:
```html
class="absolute right-0 top-10 w-52 z-50
       bg-white dark:bg-slate-800
       border border-slate-200 dark:border-slate-700
       rounded-[12px] shadow-[var(--shadow-lg)] py-1"
```

- [ ] **Step 9: Update dropdown divider and hover states inside user dropdown**

Replace `border-gray-100 dark:border-gray-700` → `border-slate-100 dark:border-slate-700`
Replace `hover:bg-gray-50 dark:hover:bg-gray-700` → `hover:bg-slate-50 dark:hover:bg-slate-700`
Replace `hover:bg-red-50 dark:hover:bg-red-900/20` → keep (correct)

- [ ] **Step 10: Run tests and commit**

```bash
cd backend && php artisan test
```
Expected: All pass.

```bash
git add backend/resources/views/layouts/app.blade.php
git commit -m "feat(ui): update layout shell with gradient sidebar and slate surface colors"
```

---

## Task 3: Layout Shell — auth-guest.blade.php

**Files:**
- Modify: `backend/resources/views/layouts/auth-guest.blade.php`

- [ ] **Step 1: Update left welcome panel gradient**

Change:
```html
<div class="hidden lg:flex lg:w-[42%] flex-col justify-center items-center text-center p-8 lg:p-10 bg-blue-600 text-white login-welcome">
```
To:
```html
<div class="hidden lg:flex lg:w-[42%] flex-col justify-center items-center text-center p-8 lg:p-10 bg-gradient-to-b from-blue-800 to-blue-600 text-white login-welcome">
```

Also update the inline CSS `.login-welcome { background-color: #2563eb; }` to `background: linear-gradient(to bottom, #1e40af, #2563eb);`

- [ ] **Step 2: Update card radius and shadow in inline style block**

In the `<style>` block, update:
```css
.login-card { min-width: 0; max-width: 634px; }
@media (min-width: 1024px) {
    .login-card { box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05); border-radius: 16px; }
}
```

- [ ] **Step 3: Update outer card `rounded-2xl` to `rounded-[16px]`**

Change:
```html
<div class="relative z-10 w-full max-w-[634px] mx-auto flex flex-col lg:flex-row rounded-2xl shadow-2xl overflow-hidden border border-white/10 bg-white dark:bg-gray-900 login-card">
```
To:
```html
<div class="relative z-10 w-full max-w-[634px] mx-auto flex flex-col lg:flex-row rounded-[16px] shadow-2xl overflow-hidden border border-white/10 bg-white dark:bg-gray-900 login-card">
```

- [ ] **Step 4: Fix `<title>` to support Blade section (required for Task 5 login refactor)**

Change in `auth-guest.blade.php`:
```html
<title>{{ $pageTitle }} - {{ config('app.name') }}</title>
```
To:
```html
<title>@yield('page-title', $pageTitle ?? config('app.name')) - {{ config('app.name') }}</title>
```

This lets existing pages pass `$pageTitle` from controllers AND lets `login.blade.php` use `@section('page-title', __('common.login'))`.

- [ ] **Step 5: Commit**

```bash
git add backend/resources/views/layouts/auth-guest.blade.php
git commit -m "feat(ui): update auth layout with gradient left panel"
```

---

## Task 4: Shared Components

**Files:**
- Modify: `backend/resources/views/components/sidebar-menu.blade.php`
- Modify: `backend/resources/views/components/kpi-card.blade.php`
- Modify: `backend/resources/views/components/notification-bell.blade.php`

### sidebar-menu.blade.php

- [ ] **Step 1: Update group button hover**

Change:
```html
class="w-full flex items-center rounded-lg px-3 py-2 text-blue-100 hover:bg-blue-500/50 transition-colors"
```
To:
```html
class="w-full flex items-center rounded-lg px-3 py-2 text-blue-100 hover:bg-white/10 transition-colors duration-200"
```

- [ ] **Step 2: Update submenu border**

Change `border-blue-400/30` → `border-white/20`

- [ ] **Step 3: Update child item active/hover states**

Change:
```php
'bg-blue-500/50 text-white font-semibold' : 'text-blue-200 hover:bg-blue-500/30 hover:text-blue-100'
```
To:
```php
'bg-white/15 text-white font-semibold border-l-2 border-white -ml-[1px]' : 'text-blue-100 hover:bg-white/10 hover:text-white'
```

- [ ] **Step 4: Update top-level single-item active/hover states**

Change:
```php
$menuActive ? 'bg-blue-500/50 text-white font-semibold' : 'text-blue-100 hover:bg-blue-500/50'
```
To:
```php
$menuActive ? 'bg-white/15 text-white font-semibold' : 'text-blue-100 hover:bg-white/10 hover:text-white'
```

### kpi-card.blade.php

- [ ] **Step 5: Replace card wrapper class**

Change:
```html
class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-6 relative"
```
To:
```html
class="card p-6 relative"
```

### notification-bell.blade.php

- [ ] **Step 6: Update dropdown panel classes**

Change:
```html
class="absolute right-0 top-10 w-80 z-50
       bg-white dark:bg-gray-800
       border border-gray-200 dark:border-gray-700
       rounded-xl shadow-lg overflow-hidden"
```
To:
```html
class="absolute right-0 top-10 w-80 z-50
       bg-white dark:bg-slate-800
       border border-slate-200 dark:border-slate-700
       rounded-[12px] shadow-[var(--shadow-lg)] overflow-hidden"
```

- [ ] **Step 7: Update dividers and hover states in notification bell**

Replace `border-gray-100 dark:border-gray-700` → `border-slate-100 dark:border-slate-700`
Replace `hover:bg-gray-50 dark:hover:bg-gray-700/50` → `hover:bg-slate-50 dark:hover:bg-slate-700/50`
Replace `divide-gray-100 dark:divide-gray-700` → `divide-slate-100 dark:divide-slate-700`

- [ ] **Step 8: Commit**

```bash
git add backend/resources/views/components/
git commit -m "feat(ui): update shared components to new design tokens"
```

---

## Task 5: Auth Pages

**Files:**
- Modify: `backend/resources/views/auth/login.blade.php`
- Modify: `backend/resources/views/auth/forgot-password.blade.php`
- Modify: `backend/resources/views/auth/reset-password.blade.php`

Note: `forgot-password.blade.php` and `reset-password.blade.php` already extend `layouts/auth-guest`. Only `login.blade.php` is standalone.

### login.blade.php — refactor to extend auth-guest

- [ ] **Step 1: Replace entire `login.blade.php` content**

```blade
@extends('layouts.auth-guest')

@section('page-title', __('common.login'))

@section('content')
<div x-data="{ showPassword: false }">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6 text-center">{{ __('common.login') }}</h2>

    @if (session('status'))
        <div class="alert-success mb-5" role="status">{{ session('status') }}</div>
    @endif

    @if (isset($authConfigured) && ! $authConfigured)
        <div class="alert-warning mb-6">{{ __('auth.misconfigured') }}</div>
    @endif

    @if (! empty($authLocalEnabled))
    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf
        <div>
            <label for="email" class="form-label">{{ __('auth.placeholder_email') }}</label>
            <div class="relative">
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       placeholder="{{ __('auth.placeholder_email') }}" required autofocus
                       class="form-input pr-10 @error('email') form-input-error @enderror"
                       aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                       @if ($errors->has('email')) aria-describedby="email-error" @endif>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
            @error('email')
                <p id="email-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="form-label">{{ __('auth.placeholder_password') }}</label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                       placeholder="{{ __('auth.placeholder_password') }}"
                       class="form-input pr-10 @error('password') form-input-error @enderror"
                       aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                       @if ($errors->has('password')) aria-describedby="password-error" @endif>
                <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none cursor-pointer">
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.783-2.961m5.208 5.208A3 3 0 1112 15m-5.625-5.625A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.783 2.961"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p id="password-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        @if (! empty($authLocalEnabled))
            <div class="text-right">
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('common.forgot_password') }}</a>
            </div>
        @endif

        <button type="submit" class="btn-primary w-full py-2.5">{{ __('common.login') }}</button>
    </form>
    @endif

    @if (! empty($authEntraEnabled) || ! empty($authLdapEnabled))
        @if (! empty($authLocalEnabled))
            <p class="text-center text-sm text-slate-500 dark:text-slate-400 my-5">{{ __('auth.or_use') }}</p>
        @endif
        @if (! empty($authEntraEnabled))
            <a href="{{ route('auth.entra.redirect') }}"
               class="btn-secondary w-full py-2.5 mb-4 no-underline bg-gray-800 hover:bg-gray-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 21 21" aria-hidden="true"><path fill="currentColor" d="M0 0h10v10H0V0zm11 0h10v10H11V0zM0 11h10v10H0V11zm11 0h10v10H11V11z"/></svg>
                {{ __('auth.sign_in_with_microsoft') }}
            </a>
        @endif
        @if (! empty($authLdapEnabled))
            <div class="mt-2 pt-4 border-t border-slate-200 dark:border-slate-600">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">{{ __('auth.sign_in_ldap') }}</p>
                <form method="POST" action="{{ route('auth.ldap.login') }}" class="space-y-4" novalidate>
                    @csrf
                    <div>
                        <label for="ldap_email" class="form-label">{{ __('auth.ldap_email') }}</label>
                        <input type="email" name="ldap_email" id="ldap_email" value="{{ old('ldap_email') }}"
                               class="form-input @error('ldap_email') form-input-error @enderror"
                               required autocomplete="username"
                               aria-invalid="{{ $errors->has('ldap_email') ? 'true' : 'false' }}"
                               @if ($errors->has('ldap_email')) aria-describedby="ldap-email-error" @endif>
                        @error('ldap_email')
                            <p id="ldap-email-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ldap_password" class="form-label">{{ __('auth.ldap_password') }}</label>
                        <input type="password" name="ldap_password" id="ldap_password"
                               class="form-input @error('ldap_password') form-input-error @enderror"
                               required autocomplete="current-password"
                               aria-invalid="{{ $errors->has('ldap_password') ? 'true' : 'false' }}"
                               @if ($errors->has('ldap_password')) aria-describedby="ldap-password-error" @endif>
                        @error('ldap_password')
                            <p id="ldap-password-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn-secondary w-full py-2.5 bg-slate-700 hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-700 text-white">
                        {{ __('auth.sign_in_ldap') }}
                    </button>
                </form>
            </div>
        @endif
    @endif
</div>
@endsection
```

### forgot-password.blade.php — replace login-form-* classes

- [ ] **Step 2: Update forgot-password.blade.php**

Replace `.login-form-title` heading:
```html
<h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6 text-center">{{ __('auth.forgot_password_page_title') }}</h2>
```

Replace `.login-form-label` → `class="form-label"`
Replace `.login-form-input w-full px-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-{color}...` → `class="form-input @error('email') form-input-error @enderror"`
Replace `.login-form-btn` → `class="btn-primary w-full py-2.5"`
Replace `.login-form-link` → `class="text-sm text-blue-600 dark:text-blue-400 hover:underline"`
Replace status alert → `<div class="alert-success mb-5" role="status">{{ session('status') }}</div>`

### reset-password.blade.php — same pattern as forgot-password

- [ ] **Step 3: Update reset-password.blade.php with same class replacements as forgot-password**

Apply the same replacements: `.login-form-label` → `form-label`, `.login-form-input` → `form-input`, `.login-form-btn` → `btn-primary w-full py-2.5`.

- [ ] **Step 4: Commit**

```bash
git add backend/resources/views/auth/
git commit -m "feat(ui): refactor auth pages to use design system classes"
```

---

## Task 6: Dashboard and Users Module

**Files:**
- Modify: `backend/resources/views/dashboard.blade.php`
- Modify: `backend/resources/views/users/index.blade.php`
- Modify: `backend/resources/views/users/create.blade.php`
- Modify: `backend/resources/views/users/edit.blade.php`
- Modify: `backend/resources/views/users/show.blade.php`
- Modify: `backend/resources/views/users/import.blade.php`

### dashboard.blade.php

- [ ] **Step 1: Update welcome card**

Change:
```html
<div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 p-6">
```
To:
```html
<div class="card p-6">
```

### users/index.blade.php

- [ ] **Step 2: Update table wrapper**

Change:
```html
<div id="users-table" x-ref="usersTable" class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 overflow-visible">
```
To:
```html
<div id="users-table" x-ref="usersTable" class="table-wrapper">
```

- [ ] **Step 3: Update thead background**

Change `bg-gray-50 dark:bg-gray-800/80` → `bg-slate-50 dark:bg-slate-800/60`

- [ ] **Step 4: Update th classes**

Change `text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider` → `table-header`

- [ ] **Step 5: Update row hover**

Change `hover:bg-gray-200 dark:hover:bg-gray-700` → `hover:bg-slate-50 dark:hover:bg-slate-700/50`

- [ ] **Step 6: Update status badges**

Change:
```html
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
```
To:
```html
<span class="badge-green">
```

Change:
```html
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
```
To:
```html
<span class="badge-red">
```

- [ ] **Step 7: Update role badges**

Change:
```html
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
```
To:
```html
<span class="badge-blue mr-1">
```

- [ ] **Step 8: Update action buttons (Import + Add User)**

Change import button:
```html
<a href="{{ route('users.import') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
```
To:
```html
<a href="{{ route('users.import') }}" class="btn-secondary">
```

Change add button:
```html
<a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
```
To:
```html
<a href="{{ route('users.create') }}" class="btn-primary">
```

- [ ] **Step 9: Update flash messages in users/index.blade.php**

Change:
```html
<div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
    <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
</div>
```
To:
```html
<div class="alert-error mb-4">{{ session('error') }}</div>
```

Change success alert similarly: `<div class="alert-success mb-4">{{ session('success') }}</div>`

- [ ] **Step 10: Update action dropdown panel in users/index.blade.php**

Change:
```html
class="absolute right-0 bottom-full mb-2 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
```
To:
```html
class="absolute right-0 bottom-full mb-2 w-40 bg-white dark:bg-slate-800 rounded-[8px] shadow-[var(--shadow-lg)] border border-slate-200 dark:border-slate-700 py-1 z-50"
```

### users/create.blade.php and users/edit.blade.php

- [ ] **Step 11: Update section card wrappers in create/edit**

Change all occurrences of:
```html
<div class="bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
```
To:
```html
<div class="card p-6 mb-6">
```

- [ ] **Step 12: Update all input/label classes in create/edit**

Change every label with `block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1` → `form-label`

Change every input/select/textarea with the long `w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 ... bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100` → `form-input`

For error state, add `@error('field') form-input-error @enderror` inside `@class` blocks, or change `@error('field') border-red-400 @enderror` → `@error('field') form-input-error @enderror`.

- [ ] **Step 13: Update section headings in create/edit**

Change `text-base font-semibold text-gray-800 dark:text-gray-200` → `section-title`

- [ ] **Step 14: Update footer action buttons in create/edit**

Change cancel link:
```html
class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition"
```
To: `class="btn-secondary"`

Change submit button:
```html
class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition"
```
To: `class="btn-primary"`

- [ ] **Step 15: Update error alert in create/edit**

Change:
```html
<div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
    <ul class="text-sm text-red-700 dark:text-red-400 space-y-1">
```
To:
```html
<div class="alert-error mb-4">
    <ul class="space-y-1">
```

- [ ] **Step 16: Run tests**

```bash
cd backend && php artisan test
```

- [ ] **Step 17: Commit**

```bash
git add backend/resources/views/dashboard.blade.php backend/resources/views/users/
git commit -m "feat(ui): migrate dashboard and users module to design system"
```

---

## Task 7: Roles and Permissions Module

**Files:**
- Modify: `backend/resources/views/roles/index.blade.php`
- Modify: `backend/resources/views/roles/create.blade.php`
- Modify: `backend/resources/views/roles/edit.blade.php`
- Modify: `backend/resources/views/roles/show.blade.php`
- Modify: `backend/resources/views/permissions/index.blade.php`
- Modify: `backend/resources/views/permissions/create.blade.php`
- Modify: `backend/resources/views/permissions/edit.blade.php`

Apply the standard migration patterns from the **Class Migration Reference** table above:

- [ ] **Step 1: Migrate roles/index.blade.php**

- Table wrapper: `bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/50 overflow-visible` → `table-wrapper`
- `thead` background: `bg-gray-50 dark:bg-gray-800/80` → `bg-slate-50 dark:bg-slate-800/60`
- All `th` classes → `table-header`
- Row hover: `hover:bg-gray-200 dark:hover:bg-gray-700` → `hover:bg-slate-50 dark:hover:bg-slate-700/50`
- Add role button: → `btn-primary`
- Flash alerts: → `.alert-success`, `.alert-error`
- Action dropdown: `bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700` → `bg-white dark:bg-slate-800 rounded-[8px] shadow-[var(--shadow-lg)] border border-slate-200 dark:border-slate-700`

- [ ] **Step 2: Migrate roles/create.blade.php and roles/edit.blade.php**

- Section card → `.card p-6`
- Labels → `.form-label`
- Inputs/selects → `.form-input`
- Error classes → `.form-input-error`
- Submit → `.btn-primary`, Cancel → `.btn-secondary`
- Error alert → `.alert-error`

- [ ] **Step 3: Migrate roles/show.blade.php**

- Info panels → `.card`
- Buttons → `.btn-primary`, `.btn-secondary`, `.btn-danger`

- [ ] **Step 4: Migrate permissions/index, create, edit with same patterns**

- [ ] **Step 5: Commit**

```bash
git add backend/resources/views/roles/ backend/resources/views/permissions/
git commit -m "feat(ui): migrate roles and permissions module to design system"
```

---

## Task 8: Repair Requests Module

**Files:**
- Modify: `backend/resources/views/repair-requests/index.blade.php`
- Modify: `backend/resources/views/repair-requests/show.blade.php`
- Modify: `backend/resources/views/repair-requests/assign.blade.php`
- Modify: `backend/resources/views/repair-requests/evaluate.blade.php`
- Modify: `backend/resources/views/repair-requests/my-jobs.blade.php`
- Modify: `backend/resources/views/repair-requests/_company_header.blade.php`

- [ ] **Step 1: Migrate repair-requests/index.blade.php**

- Both grid cards (submit form card + submitted requests card): `bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5` → `card p-5`
- Admin hints alert: `bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg` → `alert-warning`
- Error/success flashes → `.alert-error`, `.alert-success`
- Request list items `bg-white dark:bg-gray-900/20` → `bg-white dark:bg-slate-900/20`

- [ ] **Step 2: Migrate repair-requests/show.blade.php and other repair-requests pages**

- Info cards → `.card`
- Buttons → `.btn-primary`, `.btn-secondary`, `.btn-danger`
- Alerts → `.alert-*`

- [ ] **Step 3: Commit**

```bash
git add backend/resources/views/repair-requests/
git commit -m "feat(ui): migrate repair-requests module to design system"
```

---

## Task 9: Approvals and Forms Module

**Files:**
- Modify: `backend/resources/views/approvals/my-approvals.blade.php`
- Modify: `backend/resources/views/forms/index.blade.php`
- Modify: `backend/resources/views/forms/create.blade.php`
- Modify: `backend/resources/views/forms/edit-draft.blade.php`
- Modify: `backend/resources/views/forms/my-submissions.blade.php`
- Modify: `backend/resources/views/forms/show-submission.blade.php`

Apply all standard migration patterns (`.card`, `.btn-*`, `.alert-*`, `.table-wrapper`, `.form-*`) per the Class Migration Reference.

- [ ] **Step 1: Migrate approvals/my-approvals.blade.php**
- [ ] **Step 2: Migrate forms/index.blade.php and forms/my-submissions.blade.php** (table wrappers → `.table-wrapper`)
- [ ] **Step 3: Migrate forms/create.blade.php and forms/edit-draft.blade.php** (form cards → `.card`, inputs → `.form-input`)
- [ ] **Step 4: Migrate forms/show-submission.blade.php** (info cards → `.card`)

- [ ] **Step 5: Commit**

```bash
git add backend/resources/views/approvals/ backend/resources/views/forms/
git commit -m "feat(ui): migrate approvals and forms module to design system"
```

---

## Task 10: Equipment and Spare Parts Module

**Files:**
- Modify: `backend/resources/views/equipment-registry/index.blade.php`
- Modify: `backend/resources/views/equipment-registry/create.blade.php`
- Modify: `backend/resources/views/equipment-registry/edit.blade.php`
- Modify: `backend/resources/views/equipment-locations/index.blade.php`
- Modify: `backend/resources/views/spare-parts/stock.blade.php`
- Modify: `backend/resources/views/spare-parts/requisition-index.blade.php`
- Modify: `backend/resources/views/spare-parts/requisition-create.blade.php`
- Modify: `backend/resources/views/spare-parts/requisition-show.blade.php`
- Modify: `backend/resources/views/spare-parts/withdrawal-history.blade.php`

Apply standard migration patterns per Class Migration Reference.

- [ ] **Step 1: Migrate all equipment-registry pages** (index → `.table-wrapper`, create/edit → `.card` + `.form-input` + `.btn-*`)
- [ ] **Step 2: Migrate equipment-locations/index.blade.php**
- [ ] **Step 3: Migrate all spare-parts pages**

- [ ] **Step 4: Commit**

```bash
git add backend/resources/views/equipment-registry/ backend/resources/views/equipment-locations/ backend/resources/views/spare-parts/
git commit -m "feat(ui): migrate equipment and spare-parts modules to design system"
```

---

## Task 11: Maintenance and Purchase Module

**Files:**
- Modify: `backend/resources/views/maintenance/index.blade.php`
- Modify: `backend/resources/views/maintenance/show.blade.php`
- Modify: `backend/resources/views/maintenance/create-plan.blade.php`
- Modify: `backend/resources/views/maintenance/auto-assign.blade.php`
- Modify: `backend/resources/views/purchase-requests/index.blade.php`
- Modify: `backend/resources/views/purchase-requests/create.blade.php`
- Modify: `backend/resources/views/purchase-requests/show.blade.php`
- Modify: `backend/resources/views/purchase-orders/index.blade.php`
- Modify: `backend/resources/views/purchase-orders/create.blade.php`
- Modify: `backend/resources/views/purchase-orders/show.blade.php`

Apply standard migration patterns per Class Migration Reference.

- [ ] **Step 1: Migrate maintenance pages** (cards → `.card`, tables → `.table-wrapper`, buttons → `.btn-*`, alerts → `.alert-*`)
- [ ] **Step 2: Migrate purchase-requests pages**
- [ ] **Step 3: Migrate purchase-orders pages**

- [ ] **Step 4: Commit**

```bash
git add backend/resources/views/maintenance/ backend/resources/views/purchase-requests/ backend/resources/views/purchase-orders/
git commit -m "feat(ui): migrate maintenance and purchase modules to design system"
```

---

## Task 12: Companies, Profile, Reports, and Notifications

**Files:**
- Modify: `backend/resources/views/companies/index.blade.php`
- Modify: `backend/resources/views/companies/create.blade.php`
- Modify: `backend/resources/views/companies/edit.blade.php`
- Modify: `backend/resources/views/companies/_form.blade.php`
- Modify: `backend/resources/views/profile/edit.blade.php`
- Modify: `backend/resources/views/profile/password.blade.php`
- Modify: `backend/resources/views/notifications/index.blade.php`
- Modify: `backend/resources/views/reports/index.blade.php`
- Modify: `backend/resources/views/reports/repair-history.blade.php`
- Modify: `backend/resources/views/reports/pm-am-history.blade.php`
- Modify: `backend/resources/views/reports/dashboards/show.blade.php`

### Profile pages — special pattern

- [ ] **Step 1: Migrate profile/edit.blade.php**

The existing success/info flash messages use an inline flex pattern with SVG icons. Replace with:
```html
@if(session('success'))
<div class="alert-success mb-4">{{ session('success') }}</div>
@endif
@if(session('info'))
<div class="alert-info mb-4">{{ session('info') }}</div>
@endif
```

All profile cards: `bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200...` → `.card`
All labels → `.form-label`
All inputs → `.form-input`
Submit → `.btn-primary`, Cancel → `.btn-secondary`

- [ ] **Step 2: Migrate companies and reports pages** (same patterns)

- [ ] **Step 3: Commit**

```bash
git add backend/resources/views/companies/ backend/resources/views/profile/ backend/resources/views/notifications/ backend/resources/views/reports/
git commit -m "feat(ui): migrate companies, profile, reports, and notifications to design system"
```

---

## Task 13: Settings — Departments, Positions, Workflow, Approval Routing

**Files:**
- Modify: `backend/resources/views/settings/departments/index.blade.php`
- Modify: `backend/resources/views/settings/departments/create.blade.php`
- Modify: `backend/resources/views/settings/departments/edit.blade.php`
- Modify: `backend/resources/views/settings/departments/workflow-bindings-matrix.blade.php`
- Modify: `backend/resources/views/settings/positions/index.blade.php`
- Modify: `backend/resources/views/settings/positions/create.blade.php`
- Modify: `backend/resources/views/settings/positions/edit.blade.php`
- Modify: `backend/resources/views/settings/workflow/index.blade.php`
- Modify: `backend/resources/views/settings/workflow/create.blade.php`
- Modify: `backend/resources/views/settings/workflow/edit.blade.php`
- Modify: `backend/resources/views/settings/approval-routing.blade.php`

Apply standard migration patterns per Class Migration Reference.

- [ ] **Step 1: Migrate departments pages** (index → `.table-wrapper`, create/edit → `.card` + forms, workflow-bindings-matrix → `.card`)
- [ ] **Step 2: Migrate positions pages**
- [ ] **Step 3: Migrate workflow pages**
- [ ] **Step 4: Migrate approval-routing.blade.php** (cards → `.card`)

- [ ] **Step 5: Commit**

```bash
git add backend/resources/views/settings/departments/ backend/resources/views/settings/positions/ backend/resources/views/settings/workflow/ backend/resources/views/settings/approval-routing.blade.php
git commit -m "feat(ui): migrate settings departments/positions/workflow to design system"
```

---

## Task 14: Settings — Document Forms, Document Types, Navigation

**Files:**
- Modify: `backend/resources/views/settings/document-forms/index.blade.php`
- Modify: `backend/resources/views/settings/document-forms/create.blade.php`
- Modify: `backend/resources/views/settings/document-forms/edit.blade.php`
- Modify: `backend/resources/views/settings/document-forms/_form.blade.php`
- Modify: `backend/resources/views/settings/document-forms/_form-action-buttons.blade.php`
- Modify: `backend/resources/views/settings/document-forms/_form-fixed-primary-actions.blade.php`
- Modify: `backend/resources/views/settings/document-forms/_form-inline-field-actions.blade.php`
- Modify: `backend/resources/views/settings/document-forms/policy.blade.php`
- Modify: `backend/resources/views/settings/document-types/index.blade.php`
- Modify: `backend/resources/views/settings/document-types/form.blade.php`
- Modify: `backend/resources/views/settings/navigation/index.blade.php`
- Modify: `backend/resources/views/settings/navigation/form.blade.php`

**Important:** Document form builder pages have `overflow-x-visible` exception in `app.blade.php`. Do not add `overflow-hidden` to any wrapper on these pages.

- [ ] **Step 1: Migrate document-forms/index.blade.php** (table → `.table-wrapper`, buttons → `.btn-*`, alerts → `.alert-*`)
- [ ] **Step 2: Migrate document-forms/_form.blade.php and partials** (buttons → `.btn-primary`, `.btn-secondary`, `.btn-danger`)
- [ ] **Step 3: Migrate document-types and navigation pages**

- [ ] **Step 4: Commit**

```bash
git add backend/resources/views/settings/document-forms/ backend/resources/views/settings/document-types/ backend/resources/views/settings/navigation/
git commit -m "feat(ui): migrate settings document-forms and navigation to design system"
```

---

## Task 15: Settings — Equipment, Running Numbers, Dashboards, Branding, Auth, Notifications

**Files:**
- Modify: `backend/resources/views/settings/equipment/index.blade.php`
- Modify: `backend/resources/views/settings/equipment/create.blade.php`
- Modify: `backend/resources/views/settings/equipment/edit.blade.php`
- Modify: `backend/resources/views/settings/equipment-locations/index.blade.php`
- Modify: `backend/resources/views/settings/equipment-locations/create.blade.php`
- Modify: `backend/resources/views/settings/equipment-locations/edit.blade.php`
- Modify: `backend/resources/views/settings/running-numbers/index.blade.php`
- Modify: `backend/resources/views/settings/running-numbers/_form.blade.php`
- Modify: `backend/resources/views/settings/running-numbers/create.blade.php`
- Modify: `backend/resources/views/settings/running-numbers/edit.blade.php`
- Modify: `backend/resources/views/settings/dashboards/index.blade.php`
- Modify: `backend/resources/views/settings/dashboards/_form.blade.php`
- Modify: `backend/resources/views/settings/dashboards/create.blade.php`
- Modify: `backend/resources/views/settings/dashboards/edit.blade.php`
- Modify: `backend/resources/views/settings/branding.blade.php`
- Modify: `backend/resources/views/settings/auth.blade.php`
- Modify: `backend/resources/views/settings/branch-scoping.blade.php`
- Modify: `backend/resources/views/settings/password-policy.blade.php`
- Modify: `backend/resources/views/settings/notifications/index.blade.php`
- Modify: `backend/resources/views/settings/activity-history/index.blade.php`

Apply standard migration patterns per Class Migration Reference.

- [ ] **Step 1: Migrate settings/equipment and settings/equipment-locations pages**
- [ ] **Step 2: Migrate settings/running-numbers pages**
- [ ] **Step 3: Migrate settings/dashboards pages**
- [ ] **Step 4: Migrate settings/branding.blade.php** (setting cards → `.card`, inputs → `.form-input`, labels → `.form-label`, submit → `.btn-primary`)
- [ ] **Step 5: Migrate settings/auth.blade.php and settings/branch-scoping.blade.php**
- [ ] **Step 6: Migrate settings/password-policy.blade.php**
- [ ] **Step 7: Migrate settings/notifications/index.blade.php and settings/activity-history/index.blade.php**

- [ ] **Step 8: Commit**

```bash
git add backend/resources/views/settings/
git commit -m "feat(ui): migrate remaining settings pages to design system"
```

---

## Task 16: Final Pass and Verification

**Files:**
- Check: all views migrated
- Check: `backend/resources/css/app.css` — no unused old classes

- [ ] **Step 1: Search for remaining old card classes**

```bash
cd backend && grep -rl "bg-gray-100 dark:bg-gray-800 rounded-xl" resources/views/ | grep -v "welcome.blade.php"
```
Expected: no output (all converted to `.card`).

- [ ] **Step 2: Search for remaining old button classes**

```bash
cd backend && grep -rl "bg-blue-600 hover:bg-blue-700 text-white.*rounded-lg" resources/views/ | grep -v "components\|sidebar"
```
Expected: no output.

- [ ] **Step 3: Search for remaining old alert classes**

```bash
cd backend && grep -rl "bg-green-50.*border-green-200" resources/views/
```
Expected: no output.

- [ ] **Step 4: Run full test suite**

```bash
cd backend && php artisan test
```
Expected: All tests pass.

- [ ] **Step 5: Final commit**

```bash
git add -A
git commit -m "feat(ui): complete full UI redesign — design system migration"
```

---

## Testing Checklist

After all tasks, verify visually:

- [ ] Login page: gradient left panel, clean form inputs, no inline styles
- [ ] Dashboard: white KPI cards with shadow, slate-50 page background
- [ ] Any list page: white table card with shadow, new badge colors, correct row hover
- [ ] Any form page: white card sections, unified input styling
- [ ] Sidebar: blue-800→blue-700 gradient, updated active state
- [ ] Dark mode: toggle works, all surfaces correct
- [ ] Dropdowns: not clipped by overflow-hidden anywhere
- [ ] Document form builder: overflow-visible exception still intact
