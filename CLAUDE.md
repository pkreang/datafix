# CLAUDE.md

**DATA FIX / DataPLC** — CMMS (Laravel 12). Web UI: Blade + Alpine.js + Tailwind v4. JSON API: Sanctum (`routes/api.php`). All commands run from `backend/`.

## Commands

```bash
composer setup        # install + migrate + seed
composer dev          # serve + queue + vite (concurrent)
composer test         # PHPUnit

php artisan migrate:fresh --seed
php artisan db:seed --class=NavigationMenuSeeder   # after editing menu order
php artisan test --filter TestClassName
```

## Architecture

### Auth — Session + Sanctum Token Bridge

The app does **not** use Laravel's default web guard:

1. `POST /login` calls internal `POST /api/v1/auth/login`, stores bearer token in session as `api_token`.
2. Middleware **`AuthenticateWeb`** (`auth.web`): no `api_token` → redirect login; loads `User` from `session('user')['id']` and calls **`Auth::setUser($user)`** every request so `@can()`, Spatie, `$request->user()` all work.
3. Session stores: `user`, `user_permissions`, `user.is_super_admin` (UI display only).

Sign-in modes (instance-wide via Settings): **Local**, **Microsoft Entra (OIDC)**, **LDAP** — all JIT-provision users. Auth services: `app/Services/Auth/`.

### RBAC

Spatie Permission v7.2 (`guard_name: web`). Two role modes:
- **Default role**: permissions via `role_has_permissions`.
- **Custom role**: direct permissions via `model_has_permissions`, bypassing role level.

**Super-admin:** `users.is_super_admin` DB column → `Gate::before` bypasses all checks. Session `is_super_admin` flag is UI-only — don't conflate the two.

### Navigation

Sidebar is DB-driven (`navigation_menus` table). `NavigationService::getMenus()` builds a cached tree (3600s TTL). Labels: `label_en` / `label_th` columns with fallback to `lang/*/common.php`. To change order/structure: edit `NavigationMenuSeeder` then reseed.

### Business Domain

- **Companies → Branches → Users** (`users.company_id` / `users.branch_id`). Document form headers pull company/branch address from these FKs.
- **Positions** (`positions` table, `users.position_id`). Workflow stages support `approver_type: position` — any user with matching `position_id` can approve.
- **Approval Workflows**: `approval_workflows` → `approval_workflow_stages` → `department_workflow_bindings`. Flow logic in `ApprovalFlowService`.
- **Settings**: key-value in `settings` table (`Setting` model). Covers password policy, branding, auth, notifications, etc.

### Localization

Two locales: `en`, `th`. Locale in session via `SetLocale` middleware. Translation files exist in **two places** — check both:
- `lang/{locale}/` — primary
- `resources/lang/{locale}/` — additional/overrides

JS translations: `lang/en.json`, `lang/th.json`.

## Gotchas

1. **Auth guard:** `@can` / Spatie checks require `AuthenticateWeb` to have called `Auth::setUser()`. Without it, permission checks silently fail.
2. **Dropdown clipping:** `<main>` uses `overflow-auto`. Table cards with absolute-positioned action menus must use `overflow-visible` on their wrapper — never `overflow-hidden`.
3. **Sidebar spacing:** spacer div matches sidebar width; `<main>` has no extra `padding-left` for the sidebar.
4. **Translation files in two places:** always check both `lang/` and `resources/lang/`.
5. **ExampleTest:** `GET /` returns 302 (redirects to login) — this test fails by design.

## Docs

| File | Contents |
|---|---|
| `Summary.md` | Detailed project summary (Thai) |
| `doc/api-spec.md` | API endpoints + permission matrix |
| `doc/erd.md` | Entity relationship diagram |
| `backend/README.md` | Seed data, demo users, auth/SSO notes |
