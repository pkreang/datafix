# ERD — Laravel Sanctum + Spatie RBAC

## 1. ภาพรวม Relationships

```
users ──< model_has_roles >── roles ──< role_has_permissions >── permissions
  │                                                                    │
  └──────────────< model_has_permissions >────────────────────────────┘
  │
  └──< personal_access_tokens
```

---

## 2. Tables & Fields

### `users`
| Column | Type | Constraint | หมายเหตุ |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| `name` | VARCHAR(255) | NOT NULL | Full name |
| `email` | VARCHAR(255) | NOT NULL, UNIQUE | |
| `email_verified_at` | TIMESTAMP | NULLABLE | |
| `password` | VARCHAR(255) | NOT NULL | bcrypt hashed |
| `avatar` | VARCHAR(500) | NULLABLE | URL หรือ path |
| `is_active` | BOOLEAN | NOT NULL, DEFAULT true | soft disable user |
| `is_super_admin` | BOOLEAN | NOT NULL, DEFAULT false | ป้องกัน delete/modify |
| `last_active_at` | TIMESTAMP | NULLABLE | อัปเดตทุก request |
| `remember_token` | VARCHAR(100) | NULLABLE | |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |
| `deleted_at` | TIMESTAMP | NULLABLE | SoftDeletes |

**Indexes:** `email` (unique), `is_active`, `deleted_at`

---

### `roles`  *(managed by Spatie)*
| Column | Type | Constraint | หมายเหตุ |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | |
| `name` | VARCHAR(255) | NOT NULL | ชื่อ role เช่น `super-admin`, `regional-admin` |
| `guard_name` | VARCHAR(255) | NOT NULL, DEFAULT `'web'` | |
| `display_name` | VARCHAR(255) | NULLABLE | ชื่อสำหรับแสดงใน UI |
| `description` | TEXT | NULLABLE | |
| `is_system` | BOOLEAN | NOT NULL, DEFAULT false | ถ้า true ห้าม delete |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

<!-- Phase 2: เพิ่ม field `region VARCHAR(100) NULLABLE` สำหรับ Regional Admin scoping -->

**Indexes:** `(name, guard_name)` (unique)

---

### `permissions`  *(managed by Spatie)*
| Column | Type | Constraint | หมายเหตุ |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | |
| `name` | VARCHAR(255) | NOT NULL | เช่น `sales.create` |
| `guard_name` | VARCHAR(255) | NOT NULL, DEFAULT `'web'` | |
| `module` | VARCHAR(100) | NOT NULL | เช่น `sales` |
| `action` | VARCHAR(50) | NOT NULL | `create/read/update/delete/export` |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

**Indexes:** `(name, guard_name)` (unique), `module`, `action`

---

### `model_has_roles`  *(managed by Spatie)*
| Column | Type | Constraint | หมายเหตุ |
|---|---|---|---|
| `role_id` | BIGINT UNSIGNED | FK → roles.id | |
| `model_type` | VARCHAR(255) | NOT NULL | `App\Models\User` |
| `model_id` | BIGINT UNSIGNED | NOT NULL | FK → users.id |

**PK:** `(role_id, model_id, model_type)`

---

### `model_has_permissions`  *(managed by Spatie)*
| Column | Type | Constraint | หมายเหตุ |
|---|---|---|---|
| `permission_id` | BIGINT UNSIGNED | FK → permissions.id | |
| `model_type` | VARCHAR(255) | NOT NULL | `App\Models\User` |
| `model_id` | BIGINT UNSIGNED | NOT NULL | FK → users.id |

**PK:** `(permission_id, model_id, model_type)`  
> ใช้สำหรับ **Custom role** — assign permission โดยตรงกับ user แทน role

---

### `role_has_permissions`  *(managed by Spatie)*
| Column | Type | Constraint | หมายเหตุ |
|---|---|---|---|
| `permission_id` | BIGINT UNSIGNED | FK → permissions.id | |
| `role_id` | BIGINT UNSIGNED | FK → roles.id | |

**PK:** `(permission_id, role_id)`

---

### `personal_access_tokens`  *(Laravel Sanctum)*
| Column | Type | Constraint | หมายเหตุ |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK | |
| `tokenable_type` | VARCHAR(255) | NOT NULL | `App\Models\User` |
| `tokenable_id` | BIGINT UNSIGNED | NOT NULL | FK → users.id |
| `name` | VARCHAR(255) | NOT NULL | เช่น `web-session` |
| `token` | VARCHAR(64) | NOT NULL, UNIQUE | SHA-256 hashed |
| `abilities` | TEXT | NULLABLE | JSON array ของ abilities |
| `last_used_at` | TIMESTAMP | NULLABLE | |
| `expires_at` | TIMESTAMP | NULLABLE | ตั้งค่า token expiry |
| `created_at` | TIMESTAMP | | |
| `updated_at` | TIMESTAMP | | |

**Indexes:** `(tokenable_type, tokenable_id)`, `token` (unique)

---

### `password_reset_tokens`  *(Laravel built-in)*
| Column | Type | Constraint | หมายเหตุ |
|---|---|---|---|
| `email` | VARCHAR(255) | PK | |
| `token` | VARCHAR(255) | NOT NULL | hashed |
| `created_at` | TIMESTAMP | NULLABLE | expires หลัง 60 นาที |

---

## 3. Relationship Diagram (Text)

```
┌──────────────────────────────────────────────────────────────────┐
│                            users                                  │
│  id, name, email, password, avatar, is_active, is_super_admin,   │
│  last_active_at, deleted_at                                       │
└───────────────┬──────────────────────────┬───────────────────────┘
                │ 1                        │ 1
                │ N                        │ N
    ┌───────────▼──────────┐   ┌──────────▼────────────┐
    │   model_has_roles    │   │ model_has_permissions  │
    │  role_id, model_id   │   │ perm_id, model_id      │
    └───────────┬──────────┘   └──────────┬─────────────┘
                │ N                        │ N
                │ 1                        │ 1
    ┌───────────▼──────────┐   ┌──────────▼─────────────┐
    │        roles         │   │      permissions        │
    │  id, name, guard,    │   │  id, name, module,      │
    │  display_name,       ├──►│  action, guard_name     │
    │  is_system           │   │                         │
    └──────────────────────┘   └─────────────────────────┘
          │ N                           ▲
          │ role_has_permissions        │
          └─────────────────────────────┘

    ┌──────────────────────────────────────┐
    │        personal_access_tokens        │
    │  id, tokenable_id, token (hashed),   │
    │  abilities, expires_at, last_used_at │
    └──────────────────────────────────────┘
             (polymorphic → users)
```

---

## 4. Default Roles (Seeder)

| Role name | is_system | หมายเหตุ |
|---|---|---|
| `super-admin` | true | Bypass ทุก permission check (Spatie Gate::before) |
| `admin` | true | Full access ทุก module |
| `regional-admin` | false | Access เฉพาะ region ที่ assign *(Phase 2: เพิ่ม region scoping)* |
| `viewer` | false | Read-only ทุก module |

---

## 5. Custom Role Flow

```
User ─── Default role ──► ใช้ permission จาก role_has_permissions
User ─── Custom role  ──► ใช้ permission จาก model_has_permissions (direct)
                           (ไม่ assign role ใดๆ หรือ assign role = 'custom')
```
