# API Specification — Laravel Sanctum + RBAC

## 1. Permission Naming Convention

### 1.1 รูปแบบ `{module}.{action}`

```
{module} = snake_case ชื่อ module
{action} = create | read | update | delete | export
```

### 1.2 Module → Permission Matrix

| Module (UI) | Permissions |
|---|---|
| `dashboard` | `dashboard.read` |
| `product` | `product.create`, `product.read`, `product.update`, `product.delete` |
| `sales` | `sales.create`, `sales.read`, `sales.update`, `sales.delete`, `sales.export` |
| `purchase` | `purchase.create`, `purchase.read`, `purchase.update`, `purchase.delete`, `purchase.export` |
| `expense` | `expense.create`, `expense.read`, `expense.update`, `expense.delete` |
| `report` | `report.read`, `report.export` |
| `loan` | `loan.create`, `loan.read`, `loan.update`, `loan.delete` |
| `company_profile` | `company_profile.read`, `company_profile.update` |
| `user_access` | `user_access.create`, `user_access.read`, `user_access.update`, `user_access.delete` |
| `integrations` | `integrations.read`, `integrations.update` |

### 1.3 Permission Level (Add New Role UI) → CRUD Mapping

จาก UI "Add new role" มี radio 3 ระดับ ต่อ scope:

| UI Level | Map to permissions |
|---|---|
| **No access** | ไม่ assign permission ใดๆ ในตัว module นั้น |
| **Read & Export** | `{module}.read`, `{module}.export` (ถ้ามี) |
| **Full access** | `{module}.read`, `{module}.export`, `{module}.create`, `{module}.update`, `{module}.delete` |

> **หมายเหตุ:** บาง module ไม่มี export (เช่น `dashboard`, `loan`) — ให้ skip permission นั้น  
> สำหรับ "Add User (Custom role)" ใช้ CRUD checkbox อิสระ — assign ตรงตาม checkbox ที่ tick

### 1.4 Seeder ตัวอย่าง

```php
// database/seeders/PermissionSeeder.php
$permissions = [
    'dashboard'      => ['read'],
    'product'        => ['create','read','update','delete'],
    'sales'          => ['create','read','update','delete','export'],
    'purchase'       => ['create','read','update','delete','export'],
    'expense'        => ['create','read','update','delete'],
    'report'         => ['read','export'],
    'loan'           => ['create','read','update','delete'],
    'company_profile'=> ['read','update'],
    'user_access'    => ['create','read','update','delete'],
    'integrations'   => ['read','update'],
];

foreach ($permissions as $module => $actions) {
    foreach ($actions as $action) {
        Permission::create([
            'name'       => "{$module}.{$action}",
            'guard_name' => 'web',
            'module'     => $module,
            'action'     => $action,
        ]);
    }
}
```

---

## 2. Global Conventions

```
Base URL   : https://api.example.com/api/v1
Auth header: Authorization: Bearer {token}
Content-Type: application/json
Accept      : application/json
```

### Standard Response Envelope

```json
// Success
{
  "success": true,
  "message": "Human-readable message",
  "data": { ... }
}

// Paginated
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 6,
    "per_page": 10,
    "total": 44
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}

// Error
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation message"]
  }
}
```

### HTTP Status Codes
| Code | Situation |
|---|---|
| 200 | OK |
| 201 | Created |
| 204 | No Content (delete) |
| 400 | Bad Request |
| 401 | Unauthenticated |
| 403 | Forbidden (no permission) |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

---

## 3. Auth Endpoints

### POST `/auth/login`
**Public** — ไม่ต้องใช้ token

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "secret",
  "device_name": "web-browser"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123...plaintext",
    "token_type": "Bearer",
    "expires_at": "2026-04-09T00:00:00Z",
    "user": {
      "id": 1,
      "name": "Florence Shaw",
      "email": "florence@untitledui.com",
      "avatar": "https://cdn.example.com/avatars/1.jpg",
      "is_active": true,
      "last_active_at": "2026-03-09T08:00:00Z",
      "roles": ["admin"],
      "permissions": ["sales.create", "sales.read", "..."]
    }
  }
}
```

**Response 401:**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

**Response 403 (account disabled):**
```json
{
  "success": false,
  "message": "Your account has been disabled. Please contact administrator."
}
```

---

### POST `/auth/logout`
**Auth required**

**Request:** *(no body)*

**Response 200:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```
> Server revokes current token ด้วย `$request->user()->currentAccessToken()->delete()`

---

### POST `/auth/forgot-password`
**Public** — Rate limited: 5 req/min

**Request:**
```json
{
  "email": "user@example.com"
}
```

**Response 200:** *(เสมอ — ไม่ expose ว่า email มีอยู่หรือเปล่า)*
```json
{
  "success": true,
  "message": "If this email exists, a password reset link has been sent."
}
```

---

### POST `/auth/reset-password`
**Public**

**Request:**
```json
{
  "email": "user@example.com",
  "token": "reset-token-from-email",
  "password": "NewSecure@123",
  "password_confirmation": "NewSecure@123"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Password has been reset successfully. Please login."
}
```

**Response 422:**
```json
{
  "success": false,
  "message": "Invalid or expired reset token.",
  "errors": {
    "token": ["This password reset token is invalid."]
  }
}
```

---

### GET `/auth/me`
**Auth required**

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Florence Shaw",
    "email": "florence@untitledui.com",
    "avatar": "https://cdn.example.com/avatars/1.jpg",
    "is_active": true,
    "last_active_at": "2026-03-09T08:00:00Z",
    "created_at": "2022-07-04T00:00:00Z",
    "roles": [
      { "id": 1, "name": "admin", "display_name": "Administrator" }
    ],
    "permissions": ["sales.create", "sales.read", "..."]
  }
}
```

---

## 4. Users Endpoints

> ทุก endpoint ต้องการ `user_access.read` ขึ้นไป

### GET `/users`
**Permission:** `user_access.read`

**Query params:**
| Param | Type | Default | Description |
|---|---|---|---|
| `search` | string | - | ค้นหาจาก name หรือ email |
| `role` | string | - | filter by role name |
| `is_active` | boolean | - | filter active/inactive |
| `sort_by` | string | `last_active_at` | `name`, `email`, `created_at`, `last_active_at` |
| `sort_dir` | string | `desc` | `asc`, `desc` |
| `per_page` | int | 10 | max 100 |
| `page` | int | 1 | |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Florence Shaw",
      "email": "florence@untitledui.com",
      "avatar": "https://cdn.example.com/avatars/1.jpg",
      "is_active": true,
      "last_active_at": "2024-03-04T00:00:00Z",
      "created_at": "2022-07-04T00:00:00Z",
      "roles": [
        { "id": 1, "name": "admin", "display_name": "Administrator" }
      ],
      "access_badges": ["Admin", "Data Export", "Data Import"]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 44
  }
}
```

---

### POST `/users`
**Permission:** `user_access.create`

> **หมายเหตุ:** Admin ไม่ต้องกรอก password ให้ user — ระบบจะ **auto-generate password** แล้วส่ง welcome email พร้อม link สำหรับให้ user ตั้ง password ของตัวเองครั้งแรก (ใช้ `forgot-password` flow เดิม)

**Request:**
```json
{
  "name": "Andy Worchen",
  "email": "andyworchen@gmail.com",
  "role_type": "custom",
  "role_id": null,
  "permissions": [
    "dashboard.read",
    "product.create",
    "product.read",
    "product.update",
    "sales.create",
    "sales.read",
    "sales.update",
    "sales.delete"
  ]
}
```
> ถ้า `role_type = "default"` ให้ส่ง `role_id` และไม่ต้องส่ง `permissions`  
> ถ้า `role_type = "custom"` ให้ส่ง `permissions` array  
> **ไม่รับ `password` field** — ระบบ auto-generate และส่ง welcome email ให้ user set password เอง

**Response 201:**
```json
{
  "success": true,
  "message": "User created successfully. A welcome email has been sent to andyworchen@gmail.com.",
  "data": {
    "id": 45,
    "name": "Andy Worchen",
    "email": "andyworchen@gmail.com",
    "is_active": true,
    "created_at": "2026-03-09T10:00:00Z",
    "roles": [],
    "permissions": ["dashboard.read", "product.create", "..."]
  }
}
```

**Response 422:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "permissions": ["The permissions field is required when role_type is custom."]
  }
}
```

---

### GET `/users/{id}`
**Permission:** `user_access.read`

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Amélie Laurent",
    "email": "amelie@untitledui.com",
    "avatar": "https://cdn.example.com/avatars/2.jpg",
    "is_active": true,
    "last_active_at": "2024-03-04T00:00:00Z",
    "created_at": "2022-07-04T00:00:00Z",
    "role_type": "default",
    "roles": [
      { "id": 1, "name": "admin", "display_name": "Administrator" }
    ],
    "permissions": ["sales.create", "sales.read", "..."],
    "direct_permissions": []
  }
}
```

---

### PUT `/users/{id}`
**Permission:** `user_access.update`

**Request:**
```json
{
  "name": "Amélie Laurent Updated",
  "email": "amelie.new@untitledui.com",
  "is_active": true,
  "avatar": "data:image/jpeg;base64,..."
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "\"Amélie Laurent\" details updated",
  "data": { "...user object..." }
}
```

---

### DELETE `/users/{id}`
**Permission:** `user_access.delete`

**Response 204:** *(No body)*

**Response 403 (super admin protection):**
```json
{
  "success": false,
  "message": "Super admin account cannot be deleted."
}
```

---

### PATCH `/users/{id}/password`
**Permission:** `user_access.update` หรือเป็น user ตัวเอง

**Request:**
```json
{
  "password": "NewSecure@123",
  "password_confirmation": "NewSecure@123"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Password updated successfully."
}
```

---

### PATCH `/users/{id}/roles`
**Permission:** `user_access.update`  
> Endpoint สำหรับ "Add New Role" modal — รับแค่ `role_id` และ `permissions[]` ที่ต้องการ override

**Request:**
```json
{
  "role_id": 3,
  "permissions": [
    "sales.read",
    "sales.export",
    "purchase.read",
    "company_profile.read",
    "company_profile.update"
  ]
}
```
> `permissions` คือชุด permission ที่ต้องการ assign ให้ user โดยตรง (direct permissions) ควบคู่กับ role  
> ถ้าส่ง `permissions: []` ระบบจะ assign เฉพาะ role โดยไม่มี override  
> *(Phase 2: เพิ่ม field `region` สำหรับ Regional Admin scoping)*

**Response 200:**
```json
{
  "success": true,
  "message": "Role assigned successfully.",
  "data": {
    "user_id": 5,
    "role": { "id": 3, "name": "regional-admin", "display_name": "Regional Admin" },
    "permissions_granted": ["sales.read", "sales.export", "..."]
  }
}
```

---

### PATCH `/users/{id}/permissions`
**Permission:** `user_access.update`  
> Assign custom permissions โดยตรง (Custom role flow)

**Request:**
```json
{
  "permissions": [
    "dashboard.read",
    "sales.create",
    "sales.read",
    "sales.update",
    "sales.delete"
  ],
  "sync": true
}
```
> `sync: true` = replace all existing direct permissions  
> `sync: false` = เพิ่มเข้าไป (merge)

**Response 200:**
```json
{
  "success": true,
  "message": "Permissions updated successfully.",
  "data": {
    "user_id": 45,
    "permissions": ["dashboard.read", "sales.create", "..."]
  }
}
```

---

## 5. Roles Endpoints

### GET `/roles`
**Permission:** `user_access.read`

**Query params:** `search`, `per_page`, `page`

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "super-admin",
      "display_name": "Super Administrator",
      "description": "Full system access",
      "is_system": true,
      "region": null,
      "users_count": 2,
      "permissions_count": 38,
      "created_at": "2024-01-01T00:00:00Z"
    }
  ],
  "meta": { "total": 8, "per_page": 10, "current_page": 1, "last_page": 1 }
}
```

---

### POST `/roles`
**Permission:** `user_access.create`

**Request:**
```json
{
  "name": "finance-manager",
  "display_name": "Finance Manager",
  "description": "Access to sales, purchase and expense modules",
  "region": null,
  "permissions": [
    "sales.read",
    "sales.export",
    "purchase.read",
    "purchase.export",
    "expense.create",
    "expense.read",
    "expense.update",
    "expense.delete"
  ]
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Role created successfully.",
  "data": {
    "id": 9,
    "name": "finance-manager",
    "display_name": "Finance Manager",
    "is_system": false,
    "permissions": ["sales.read", "sales.export", "..."]
  }
}
```

---

### GET `/roles/{id}`
**Permission:** `user_access.read`

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "regional-admin",
    "display_name": "Regional Admin",
    "description": "...",
    "is_system": false,
    "region": "EMEA",
    "permissions": [
      { "id": 5, "name": "sales.read", "module": "sales", "action": "read" },
      { "id": 6, "name": "sales.export", "module": "sales", "action": "export" }
    ],
    "users_count": 7,
    "created_at": "2024-01-15T00:00:00Z",
    "updated_at": "2024-02-20T00:00:00Z"
  }
}
```

---

### PUT `/roles/{id}`
**Permission:** `user_access.update`

**Request:**
```json
{
  "display_name": "Regional Administrator",
  "description": "Updated description",
  "permissions": ["sales.read", "sales.export", "purchase.read"]
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Role updated successfully.",
  "data": { "...role object..." }
}
```

**Response 403 (system role):**
```json
{
  "success": false,
  "message": "System roles cannot be modified."
}
```

---

### DELETE `/roles/{id}`
**Permission:** `user_access.delete`

**Response 204:** *(No body)*

**Response 403:**
```json
{
  "success": false,
  "message": "System roles cannot be deleted."
}
```

**Response 409 (role in use):**
```json
{
  "success": false,
  "message": "Cannot delete role. 12 users are currently assigned to this role."
}
```

---

## 6. Permissions Endpoints

### GET `/permissions`
**Permission:** `user_access.read`

**Query params:** `module` (filter by module)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "dashboard": [
      { "id": 1, "name": "dashboard.read", "action": "read" }
    ],
    "sales": [
      { "id": 5, "name": "sales.create", "action": "create" },
      { "id": 6, "name": "sales.read", "action": "read" },
      { "id": 7, "name": "sales.update", "action": "update" },
      { "id": 8, "name": "sales.delete", "action": "delete" },
      { "id": 9, "name": "sales.export", "action": "export" }
    ]
  }
}
```
> จัด group ตาม module เพื่อ render checkbox matrix ใน UI ได้ทันที

---

## 7. Route Summary

```
POST   /api/v1/auth/login
POST   /api/v1/auth/logout              [auth]
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
GET    /api/v1/auth/me                  [auth]

GET    /api/v1/users                    [auth, user_access.read]
POST   /api/v1/users                    [auth, user_access.create]
GET    /api/v1/users/{id}               [auth, user_access.read]
PUT    /api/v1/users/{id}               [auth, user_access.update]
DELETE /api/v1/users/{id}               [auth, user_access.delete]
PATCH  /api/v1/users/{id}/password      [auth, user_access.update]
PATCH  /api/v1/users/{id}/roles         [auth, user_access.update]
PATCH  /api/v1/users/{id}/permissions   [auth, user_access.update]

GET    /api/v1/roles                    [auth, user_access.read]
POST   /api/v1/roles                    [auth, user_access.create]
GET    /api/v1/roles/{id}               [auth, user_access.read]
PUT    /api/v1/roles/{id}               [auth, user_access.update]
DELETE /api/v1/roles/{id}               [auth, user_access.delete]

GET    /api/v1/permissions              [auth, user_access.read]
```

---

## 8. Component List ต่อหน้า

### 8.1 หน้า Login

```
LoginPage
├── SplitLayout
│   ├── IllustrationPanel         (left — static image)
│   └── LoginFormPanel            (right)
│       ├── AppLogo
│       ├── LoginForm
│       │   ├── EmailInput        state: value, error
│       │   ├── PasswordInput     state: value, show/hide toggle, error
│       │   ├── ForgotPasswordLink → /auth/forgot-password
│       │   └── LoginButton       state: loading, disabled
│       └── FormErrorBanner       state: visible, message (401/403)
```

| Component | Input | Output / State |
|---|---|---|
| `LoginForm` | - | `onSuccess(token, user)` |
| `EmailInput` | `value`, `onChange`, `error` | Controlled input |
| `PasswordInput` | `value`, `onChange`, `error` | Toggle visibility |
| `LoginButton` | `isLoading` | Triggers submit |
| `FormErrorBanner` | `message` | Displays API error |

**States:** idle → loading → success (redirect) / error (show banner)

---

### 8.2 หน้า User Management

```
UserManagementPage
├── PageHeader
│   ├── Breadcrumb                (Sisyphus Ventures > User Management)
│   └── UserAvatar (current user)
├── PageTitle + Subtitle
├── Toolbar
│   ├── UserCountBadge            ("All users 44")
│   ├── SearchInput               state: query, debounce 300ms
│   ├── FilterButton + FilterDropdown
│   │   ├── RoleFilter
│   │   └── StatusFilter
│   └── AddUserButton             → navigate to /users/new
├── UserTable
│   ├── TableHeader               (sortable: Last active, Date added)
│   ├── UserRow × N
│   │   ├── CheckboxCell
│   │   ├── AvatarCell
│   │   ├── NameEmailCell
│   │   ├── AccessBadgeList       (role badge chips)
│   │   ├── LastActiveCell
│   │   ├── DateAddedCell
│   │   └── RowActionMenu         (edit, disable, delete)
│   ├── EmptyState                state: no-results / no-users
│   └── TableSkeleton             state: loading
├── Pagination
└── Toast (success/error notifications)
```

| Component | Input | Output |
|---|---|---|
| `SearchInput` | - | `onSearch(query)` |
| `FilterDropdown` | `roles[]`, `activeFilters` | `onFilterChange(filters)` |
| `UserTable` | `users[]`, `loading`, `sortConfig` | `onSort(field)`, `onRowAction(type, userId)` |
| `UserRow` | `user` | `onEdit`, `onDelete`, `onDisable` |
| `AccessBadgeList` | `roles[]` | Display only |
| `Pagination` | `meta` | `onPageChange(page)` |
| `Toast` | `message`, `type`, `action?` | `onUndo()`, `onViewProfile()` |

**States:** loading (skeleton) → populated → empty → error

---

### 8.3 หน้า Add User

```
AddUserPage
├── Breadcrumb                    (Settings / User & access)
├── PageTitle                     ("Add user")
├── Section: GeneralInfo
│   ├── FullNameInput             required, maxLength 255
│   └── EmailInput                required, format validation
├── Section: RoleAndAccess
│   ├── RoleTypeToggle            (Default role | Custom role)
│   ├── [if Default role]
│   │   └── RoleSelector          dropdown, loads from GET /roles
│   └── [if Custom role]
│       └── PermissionMatrix
│           ├── ModuleColumn
│           ├── CreateColumn
│           ├── ReadColumn
│           ├── UpdateColumn
│           └── DeleteColumn
│           └── PermissionRow × N (checkbox per cell)
├── FooterActions
│   ├── BackButton
│   ├── CancelButton
│   └── SaveButton                state: loading, disabled
└── ValidationSummary
```

| Component | Input | Output |
|---|---|---|
| `RoleTypeToggle` | `value` | `onChange('default'|'custom')` |
| `RoleSelector` | `roles[]`, `value` | `onChange(roleId)` |
| `PermissionMatrix` | `modules[]`, `permissions{}` | `onChange(permissionsMap)` |
| `PermissionRow` | `module`, `checkedActions{}` | `onToggle(module, action)` |
| `SaveButton` | `isLoading` | triggers submit |

**States:** idle → saving → success (redirect + toast) → error (inline validation)

---

### 8.4 หน้า Add New Role (Modal)

```
AddNewRoleModal
├── ModalHeader                   ("Add new role" + X close)
├── MemberSection
│   ├── MemberCard                (avatar, name, email)
│   └── ChangeLink                → open MemberPicker
├── RoleRow
│   └── RoleDropdown              loads from GET /roles
│   <!-- Phase 2: เพิ่ม RegionDropdown สำหรับ Regional Admin scoping -->
├── PermissionMatrixSection       ("Set permission")
│   └── PermissionScopeTable
│       ├── ScopeGroupHeader      (Settings, Account profile, ...)
│       └── ScopeRow × N
│           ├── ScopeName
│           ├── RadioNoAccess
│           ├── RadioReadExport
│           └── RadioFullAccess
└── AddButton                     state: loading, disabled
```

| Component | Input | Output |
|---|---|---|
| `MemberCard` | `user` | Display + `onChangeMember()` |
| `MemberPicker` | `users[]` | `onSelect(user)` |
| `RoleDropdown` | `roles[]`, `value` | `onChange(roleId)` |
| `PermissionScopeTable` | `scopes[]`, `values{}` | `onChange(scope, level)` |
| `ScopeRow` | `scope`, `currentLevel` | `onLevelChange(level)` |
| `AddButton` | `isLoading` | triggers `PATCH /users/{id}/roles` |

**States:** idle → loading (submit) → success (close modal + toast) → error (inline)

---

## 9. Implementation Checklist & Security Concerns

### 9.1 Super Admin Protection

```php
// App/Policies/UserPolicy.php
public function delete(User $auth, User $target): bool
{
    if ($target->is_super_admin) {
        return false; // ห้าม delete super admin เสมอ
    }
    // ห้าม user ลบตัวเอง
    if ($auth->id === $target->id) {
        return false;
    }
    return $auth->can('user_access.delete');
}

public function update(User $auth, User $target): bool
{
    // ห้าม downgrade super admin
    if ($target->is_super_admin && !$auth->is_super_admin) {
        return false;
    }
    return $auth->can('user_access.update');
}
```

```php
// Gate::before ใน AuthServiceProvider — super admin bypass ทุกอย่าง
Gate::before(function (User $user, string $ability) {
    if ($user->is_super_admin) {
        return true;
    }
});
```

---

### 9.2 Permission Escalation Prevention

```php
// ป้องกัน user assign permission ที่ตัวเองไม่มี
public function assignPermissions(Request $request, User $target): JsonResponse
{
    $requested = $request->input('permissions', []);
    $actorPermissions = $request->user()->getAllPermissions()->pluck('name');

    $escalated = array_diff($requested, $actorPermissions->toArray());

    if (!empty($escalated) && !$request->user()->is_super_admin) {
        return response()->json([
            'success' => false,
            'message' => 'You cannot assign permissions you do not have.',
            'errors'  => ['permissions' => $escalated],
        ], 403);
    }
    // ...proceed
}
```

---

### 9.3 Token Security

| เรื่อง | การ implement |
|---|---|
| Token expiry | ตั้ง `expires_at` ใน `createToken()` เช่น `now()->addDays(7)` |
| Token revocation | `logout` ต้อง delete current token เสมอ |
| Revoke all tokens | เมื่อ reset password ให้ revoke token ทั้งหมดของ user |
| Token hashing | Sanctum hash token ก่อนเก็บ — ปลอดภัยแม้ DB รั่ว |
| HTTPS only | บังคับ HTTPS ใน production, `SESSION_SECURE_COOKIE=true` |
| Token ใน header เท่านั้น | ห้าม accept token จาก query string |

```php
// config/sanctum.php
'expiration' => 60 * 24 * 7, // 7 days (minutes)

// เมื่อ reset password
$user->tokens()->delete(); // revoke all
```

---

### 9.4 Rate Limiting

```php
// routes/api.php
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});
```

---

### 9.5 Account Status Check

```php
// App/Http/Middleware/EnsureUserIsActive.php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    if ($user && !$user->is_active) {
        $user->currentAccessToken()->delete(); // revoke token
        return response()->json([
            'success' => false,
            'message' => 'Your account has been disabled.',
        ], 403);
    }
    return $next($request);
}
```

---

### 9.6 สิ่งที่ต้อง Implement เพิ่ม

| หัวข้อ | Priority | หมายเหตุ |
|---|---|---|
| **Email verification flow** | High | ส่ง welcome email + set temp password เมื่อ admin สร้าง user |
| **Audit log** | High | บันทึก who-did-what-when สำหรับทุก user/role change |
| **Password complexity** | High | min 8 chars, uppercase, number, symbol |
| **last_active_at middleware** | Medium | อัปเดตทุก authenticated request |
| **Avatar upload** | Medium | validate MIME type, resize, store ใน S3 |
| **Soft delete vs hard delete** | Medium | ใช้ SoftDeletes — ป้องกัน data loss |
| **Permission cache** | Medium | Cache permission ต่อ user, clear เมื่อ assign เปลี่ยน |
| **Region-based data scoping** | Phase 2 | Regional admin ต้อง filter data ตาม region — เพิ่ม `region` field ใน roles และ PATCH /users/{id}/roles |
| **i18n error messages** | Low | ถ้า app รองรับหลายภาษา |
| **API versioning** | Low | ใช้ `/api/v1/` prefix ตั้งแต่ต้น |

---

### 9.7 Permission Cache ที่แนะนำ

```php
// Cache permission ต่อ user
$permissions = Cache::remember(
    "user.{$userId}.permissions",
    now()->addMinutes(60),
    fn() => $user->getAllPermissions()->pluck('name')
);

// Clear cache เมื่อ assign role หรือ permission เปลี่ยน
Cache::forget("user.{$userId}.permissions");
```

---

### 9.8 Companies & branches — structured address

Endpoints (Sanctum, ตาม `routes/api.php`): `GET/POST /companies`, `PUT/DELETE /companies/{company}`, `GET/POST /companies/{company}/branches`, `PUT/DELETE /companies/{company}/branches/{branch}`.

นอกจากฟิลด์เดิม (`name`, `code`, `email`, `phone`, `is_active`, …) รองรับที่อยู่แบบเดิมและแบบแยกส่วน:

| Field | Type | Max | หมายเหตุ |
|-------|------|-----|----------|
| `address` | string | — | ที่อยู่บรรทัดเดียว (legacy); ถ้า client เก่าไม่ส่งฟิลด์แยก ใช้ฟิลด์นี้ได้ตามเดิม |
| `address_no` | string | 50 | เลขที่ / หน่วย |
| `address_building` | string | 255 | อาคาร / หมู่บ้าน |
| `address_street` | string | 255 | ถนน |
| `address_subdistrict` | string | 120 | ตำบล / แขวง |
| `address_district` | string | 120 | อำเภอ / เขต |
| `address_province` | string | 120 | จังหวัด |
| `address_postal_code` | string | 10 | รหัสไปรษณีย์ |

**กฎ:** ถ้ามีการส่งฟิลด์แยกส่วนอย่างน้อยหนึ่งฟิลด์ที่ไม่ว่าง ระบบจะประกอบและอัปเดต `address` ให้สอดคล้องตอนบันทึก (สำหรับ client ที่อ่านแค่ `address`) หากไม่ส่งฟิลด์แยกแต่ส่ง `address` ให้เก็บ `address` ตามที่ส่ง

### 9.9 Checklist ก่อน Production

- [ ] `APP_DEBUG=false` ใน production
- [ ] `SANCTUM_STATEFUL_DOMAINS` ตั้งค่าให้ถูก
- [ ] CORS ปิด wildcard `*` — ระบุ domain จริง
- [ ] Migration มี index ครบ (`email`, `token`, `deleted_at`)
- [ ] Super admin ≥ 1 account ที่ `is_super_admin = true` ใน seeder
- [ ] System roles (`super-admin`, `admin`) ถูก seed และ `is_system = true`
- [ ] ทดสอบ permission escalation scenario
- [ ] ทดสอบ delete super admin → ต้องได้ 403
- [ ] Rate limit ทดสอบ brute force login
- [ ] Reset password revoke token ทุกอัน
