# Full UI Redesign — Data Flow (CMMS)

**Date:** 2026-04-09
**Approach:** Design System First (Approach A)
**Stack:** Laravel 12 + Blade + Alpine.js + Tailwind v4
**Scope:** All ~60+ Blade views, both light and dark mode

---

## Goals

- Keep blue (`#2563EB`) as primary brand color
- Apply Soft UI Evolution style: white card surfaces elevated above a slate-50 background via subtle multi-layer shadows
- Consistent component utility classes across all views (no per-page inline style soup)
- Full dark mode parity
- WCAG AA contrast throughout

---

## Section 1: Design Tokens

### CSS Custom Properties (add to `app.css`)

```css
@layer base {
  :root {
    --color-bg:          #F8FAFC;   /* slate-50 */
    --color-surface:     #FFFFFF;
    --color-surface-2:   #F1F5F9;   /* slate-100 */
    --color-border:      #E2E8F0;   /* slate-200 */
    --color-text:        #1E293B;   /* slate-800 */
    --color-muted:       #64748B;   /* slate-500 */
    --color-brand:       #2563EB;   /* blue-600 */
    --color-brand-hover: #1D4ED8;   /* blue-700 */

    --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);

    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
  }

  .dark {
    --color-bg:        #0F172A;   /* slate-950 */
    --color-surface:   #1E293B;   /* slate-800 */
    --color-surface-2: #334155;   /* slate-700 */
    --color-border:    #334155;   /* slate-700 */
    --color-text:      #F1F5F9;   /* slate-100 */
    --color-muted:     #94A3B8;   /* slate-400 */
    --color-brand:     #3B82F6;   /* blue-500 */
    --color-brand-hover: #2563EB;

    --shadow-xs: 0 1px 2px rgba(0,0,0,0.2);
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.25), 0 1px 2px rgba(0,0,0,0.15);
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.3), 0 2px 4px -1px rgba(0,0,0,0.2);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.35), 0 4px 6px -2px rgba(0,0,0,0.2);
  }
}
```

### Typography

- **Headings:** Plus Jakarta Sans (add to Google Fonts import)
- **Body / Thai:** Inter + Noto Sans Thai (keep existing)
- Font stack: `'Plus Jakarta Sans', 'Inter', 'Noto Sans Thai', ui-sans-serif, system-ui, sans-serif`

| Scale | Size | Weight | Class | Usage |
|-------|------|--------|-------|-------|
| Page title | 20px | 600 | `.page-title` | Header h1 |
| Section title | 16px | 600 | `.section-title` | Card headings |
| Body | 14px | 400 | default | Table rows, values |
| Caption | 12px | 400 | `.text-xs` | Sub-text, labels |

---

## Section 2: Component Utility Classes

Add all of the following to `resources/css/app.css`.

### Layout / Surface

```css
.card {
  @apply bg-white dark:bg-slate-800
         rounded-[12px]
         border border-slate-200 dark:border-slate-700
         shadow-[var(--shadow-sm)];
}

.card-header {
  @apply px-6 py-4 border-b border-slate-200 dark:border-slate-700;
}
```

### Buttons

```css
.btn-primary {
  @apply inline-flex items-center justify-center gap-2
         px-4 py-2 rounded-lg text-sm font-medium
         bg-blue-600 hover:bg-blue-700 text-white
         transition-colors duration-200
         cursor-pointer
         disabled:opacity-60 disabled:cursor-not-allowed
         shadow-[var(--shadow-xs)];
}

.btn-secondary {
  @apply inline-flex items-center justify-center gap-2
         px-4 py-2 rounded-lg text-sm font-medium
         bg-slate-100 hover:bg-slate-200
         dark:bg-slate-700 dark:hover:bg-slate-600
         text-slate-700 dark:text-slate-200
         transition-colors duration-200
         cursor-pointer;
}

.btn-danger {
  @apply inline-flex items-center justify-center gap-2
         px-4 py-2 rounded-lg text-sm font-medium
         bg-red-600 hover:bg-red-700 text-white
         transition-colors duration-200
         cursor-pointer;
}
```

### Forms

```css
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
```

### Badges

```css
.badge-base   { @apply text-xs font-medium px-2.5 py-0.5 rounded-full; }
.badge-green  { @apply badge-base bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400; }
.badge-red    { @apply badge-base bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400; }
.badge-blue   { @apply badge-base bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400; }
.badge-yellow { @apply badge-base bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400; }
.badge-gray   { @apply badge-base bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400; }
```

### Alerts / Flash Messages

```css
.alert-base    { @apply p-4 rounded-lg border-l-4 text-sm; }
.alert-success { @apply alert-base bg-green-50 border-green-500 text-green-800 dark:bg-green-900/20 dark:text-green-200; }
.alert-error   { @apply alert-base bg-red-50 border-red-500 text-red-800 dark:bg-red-900/20 dark:text-red-200; }
.alert-warning { @apply alert-base bg-amber-50 border-amber-500 text-amber-800 dark:bg-amber-900/20 dark:text-amber-200; }
.alert-info    { @apply alert-base bg-blue-50 border-blue-500 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200; }
```

### Tables

```css
/* NOTE: overflow-visible on wrapper so absolute action-menu dropdowns aren't clipped (CLAUDE.md gotcha #2).
   Add overflow-x-auto directly on <table> elements inside, not on the wrapper. */
.table-wrapper {
  @apply bg-white dark:bg-slate-800
         rounded-[12px]
         border border-slate-200 dark:border-slate-700
         shadow-[var(--shadow-sm)]
         overflow-visible;
}

.table-header {
  @apply text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider;
}

.table-primary { @apply text-sm font-medium text-slate-900 dark:text-slate-100; }
.table-sub     { @apply text-xs text-slate-500 dark:text-slate-400; }
```

---

## Section 3: Layout Shell Changes

### `resources/views/layouts/app.blade.php`

**Body:**
```html
<body class="h-full font-sans antialiased bg-[#F8FAFC] dark:bg-slate-950 text-slate-800 dark:text-slate-200">
```

**Sidebar:**
- Background: `bg-gradient-to-b from-blue-800 to-blue-700`
- Nav active item: `bg-white/15 rounded-lg border-l-2 border-white`
- Nav hover: `hover:bg-white/10 rounded-lg transition-colors duration-200`
- Border accents: `border-white/10` (replaces `border-blue-500/40`)

**Header:**
- Background: `bg-white dark:bg-slate-900`
- Shadow: `shadow-[0_1px_3px_rgba(0,0,0,0.06)]` (replaces `shadow-sm`)
- Lang switcher active: `bg-blue-600 text-white`
- Dark mode toggle: `p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800`

**Main:**
- Remove `bg-white dark:bg-gray-900` from `.flex-1` wrapper — background is set on `<body>`

### `resources/views/layouts/auth-guest.blade.php`

- Left panel: `bg-gradient-to-b from-blue-800 to-blue-600` (matches sidebar)
- Card radius: `rounded-[16px]`
- Card shadow: `shadow-[var(--shadow-lg)]`

---

## Section 4: Page-by-Page Changes

### Auth Pages (`auth/login.blade.php`, `auth/forgot-password.blade.php`, `auth/reset-password.blade.php`)

- Remove inline `<style>` blocks from `login.blade.php` — convert all `.login-form-*` classes to use `.form-label`, `.form-input`, `.btn-primary`
- Convert `login.blade.php` to extend `layouts/auth-guest.blade.php` (eliminates duplication)
- Left panel gradient matches sidebar

### Dashboard (`dashboard.blade.php`)

- KPI cards: `.card` (white + shadow)
- Welcome card: `.card`

### All Index / List Pages (~25 pages)

Pages: users, roles, permissions, repair-requests, approvals, forms, equipment-registry, equipment-locations, spare-parts (stock, requisition, withdrawal), maintenance, purchase-requests, purchase-orders, companies, departments, positions, workflow, document-forms, document-types, running-numbers, dashboards (settings), notifications, activity-history, reports

Changes per page:
1. Table wrapper: replace `bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700` → `.table-wrapper`
2. Flash alerts: replace inline classes → `.alert-success`, `.alert-error`, `.alert-warning`
3. Primary action buttons: → `.btn-primary`
4. Secondary action buttons: → `.btn-secondary`
5. Status badges: → `.badge-green`, `.badge-red`, `.badge-blue`, `.badge-yellow`, `.badge-gray`
6. Search input: → `.form-input max-w-sm`

### All Show / Detail Pages (~15 pages)

1. Info panels: → `.card`
2. Section dividers: `border-slate-200 dark:border-slate-700`
3. Action buttons: `.btn-primary` / `.btn-secondary` / `.btn-danger`

### All Create / Edit Form Pages (~20 pages)

1. Form wrapper: → `.card`
2. Labels: → `.form-label`
3. Inputs / selects / textareas: → `.form-input`
4. Error inputs: add `.form-input-error`
5. Submit buttons: → `.btn-primary` + loading state (Alpine `submitting` flag)
6. Cancel buttons: → `.btn-secondary`

### Settings Pages (~12 pages)

1. Setting cards: → `.card`
2. Section headings: `.section-title`
3. Save buttons: → `.btn-primary`

---

## Section 5: Implementation Order

Execute in this sequence to maximize visible progress per step:

1. **CSS tokens + utility classes** (`app.css`) — no view changes yet, zero risk
2. **Layout shell** (`layouts/app.blade.php`, `layouts/auth-guest.blade.php`) — instant global improvement
3. **Shared components** (`components/kpi-card.blade.php`, `components/sidebar-menu.blade.php`, `components/notification-bell.blade.php`)
4. **Login page** — convert to extend `auth-guest`, remove inline styles
5. **Dashboard** — quick win, high visibility
6. **Index pages** — systematic pass, 25+ pages
7. **Show pages** — 15+ pages
8. **Create/edit form pages** — 20+ pages
9. **Settings pages** — 12+ pages

---

## Constraints

- Do NOT change any PHP controller logic, routes, or database queries
- Do NOT add new npm packages — use only Tailwind v4 utilities + existing Alpine.js
- Thai language support must be preserved — keep Noto Sans Thai in font stack
- All existing Alpine.js `x-data` / `x-model` / `x-show` behavior must remain intact
- Dark mode must work via the existing `localStorage` + `dark` class on `<html>` mechanism
- `overflow-visible` exception on document-form builder pages must be preserved

---

## Success Criteria

- [ ] All pages use `.card` instead of `bg-gray-100` panels
- [ ] All buttons use `.btn-primary` / `.btn-secondary` / `.btn-danger`
- [ ] All form inputs use `.form-input` + `.form-label`
- [ ] All flash messages use `.alert-*` classes
- [ ] All status badges use `.badge-*` classes
- [ ] Sidebar shows blue-800→blue-700 gradient
- [ ] Page background is `slate-50` (light) / `slate-950` (dark)
- [ ] WCAG AA contrast passes for all text
- [ ] No `bg-gray-100` or `bg-gray-800` used for card surfaces (migrated to `.card`)
