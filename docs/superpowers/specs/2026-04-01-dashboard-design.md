# Dashboard Design Spec — 2026-04-01

## Context

The current Home Dashboard (`/dashboard`) shows only Users / Roles / Permissions — irrelevant to CMMS operations. The Report Dashboard system already has a powerful widget-based infrastructure but ships with no pre-built dashboards, so users must build everything from scratch.

**Goal:** Make both dashboards useful out of the box for two audiences (managers and technicians), while allowing admins to set role defaults and users with permission to customize their own view.

---

## Two-Tier Approach

### Tier 1 — Home Dashboard (`/dashboard`)

Quick-glance KPI cards shown immediately after login. Data loads per-card via API to keep initial page render fast.

#### Default KPI Cards by Role

**Manager / Admin:**
| Card | Metric | Data Source |
|------|--------|-------------|
| Repair Requests – Pending | Count with status=pending | `repair_requests` |
| Repair Requests – This Month | Count vs previous month | `repair_requests` |
| PM/AM Plans – Overdue | Count overdue | `pm_am_plans` |
| PM/AM Plans – This Week | Count upcoming 7 days | `pm_am_plans` |
| Spare Parts – Low Stock | Count where current_stock < min_stock | `spare_parts` |
| Equipment – Active | Total active equipment | `equipment` |

**Technician:**
| Card | Metric |
|------|--------|
| My Pending Requests | repair_requests assigned to current user, pending |
| Today's Plans | PM/AM plans scheduled today |
| Spare Parts – Low Stock | Same as manager |

#### Configurability
- **Admin** configures default cards per role via Settings UI
- **User** with `manage_own_dashboard` permission can toggle cards on/off
- All cards are automatically scoped to user's company/branch — no cross-org data leakage

#### Architecture
- Each card = Blade component `<x-kpi-card>` rendered server-side shell, data fetched client-side via Alpine.js + API
- `DashboardController@index` passes user's card config to the view
- User card preferences stored in a new `dashboard_config` JSON column on the `users` table (migration required)

---

### Tier 2 — Report Dashboards (`/reports`)

Three pre-built dashboards seeded via `DashboardSeeder`. Uses existing `ReportDashboard`, `ReportDashboardWidget`, `DataSourceRegistry`, and `DashboardWidgetDataController` — no new infrastructure needed.

#### Pre-built Dashboard 1: CMMS Overview (Manager)
| Widget | Type | Config |
|--------|------|--------|
| Repair Requests by Status | Pie chart | group_by: status |
| Repair Requests – Monthly Trend | Line chart | group_by: month, date_field: created_at |
| PM/AM Plans by Status | Bar chart | group_by: status |
| Equipment by Category | Bar chart | group_by: equipment_category_id |
| Top Departments by Repair Requests | Table | sort: count desc, limit: 5 |

#### Pre-built Dashboard 2: Maintenance Dashboard (Technician)
| Widget | Type | Config |
|--------|------|--------|
| Pending / Overdue PM/AM Plans | Table | filter: status in [pending, overdue] |
| Pending Repair Requests | Table | filter: status=pending, cols: reference_no, department, created_at |
| Low Stock Spare Parts | Table | filter: current_stock < min_stock |

#### Pre-built Dashboard 3: Inventory Dashboard (Spare Parts)
| Widget | Type | Config |
|--------|------|--------|
| Stock Level by Category | Bar chart | group_by: equipment_category_id, agg: sum(current_stock) |
| Transactions – Receive vs Issue | Line chart | group_by: month, split: transaction_type |
| Low Stock Items | Table | filter: current_stock < min_stock |
| Total Inventory Value | Metric | agg: sum(current_stock * unit_cost) |

#### Visibility
- CMMS Overview: visible to roles with `view_reports` permission
- Maintenance Dashboard: visible to roles with `view_repair_requests` or `view_maintenance`
- Inventory Dashboard: visible to roles with `view_spare_parts`
- Admins can clone and modify all three

---

## New Permission Required

| Permission | Description |
|------------|-------------|
| `manage_own_dashboard` | Allows user to toggle KPI cards on Home Dashboard |

Add to `RolePermissionSeeder` — granted to all roles by default (can be revoked by admin).

---

## Files to Create / Modify

| File | Action |
|------|--------|
| `app/Http/Controllers/Web/DashboardController.php` | Modify `index()` to load role-based KPI config |
| `resources/views/dashboard.blade.php` | Redesign with KPI card grid + Alpine.js fetch |
| `resources/views/components/kpi-card.blade.php` | New Blade component |
| `routes/api.php` | Add `GET /api/dashboard/kpi/{card}` endpoint |
| `app/Http/Controllers/Api/DashboardKpiController.php` | New controller for KPI data |
| `database/seeders/DashboardSeeder.php` | New seeder for 3 pre-built dashboards |
| `database/migrations/xxxx_add_dashboard_config_to_users.php` | New migration: add `dashboard_config` JSON column to `users` |
| `database/seeders/RolePermissionSeeder.php` | Add `manage_own_dashboard` |
| `lang/en/common.php` + `lang/th/common.php` | Add dashboard translation keys |

---

## Verification

1. `php artisan db:seed --class=DashboardSeeder` — verify 3 dashboards appear in `/reports`
2. Login as manager → Home Dashboard shows 6 KPI cards with real data
3. Login as technician → Home Dashboard shows 3 technician cards
4. User with `manage_own_dashboard` → can toggle cards; preference persists on reload
5. User without permission → toggle UI not shown
6. Each Report Dashboard renders all widgets with data from correct data sources
7. Date range filter on Report Dashboard affects all widgets simultaneously
