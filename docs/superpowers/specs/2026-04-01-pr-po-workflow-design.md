# PR/PO Workflow Design Spec — 2026-04-01

## Context

The CMMS system already has a mature ApprovalFlowService, DocumentForm infrastructure, and three working document types (repair_request, pm_am_plan, spare_parts_requisition). No purchasing workflow exists yet. This spec adds a Purchase Request (PR) + Purchase Order (PO) module for demo purposes, following the exact patterns established by SparePartsController and its related infrastructure.

**Goal:** Demo a full E2E procurement workflow — employee submits PR with line items → dept manager approves → procurement creates PO from approved PR → plant manager approves PO → complete.

---

## Document Types

| Property | Purchase Request | Purchase Order |
|---|---|---|
| `document_type` | `purchase_request` | `purchase_order` |
| `label_en` | Purchase Request | Purchase Order |
| `label_th` | ใบขอซื้อ | ใบสั่งซื้อ |
| `icon` | `shopping-cart` | `document-check` |
| `routing_mode` | `hybrid` | `organization_wide` |
| `sort_order` | 4 | 5 |
| Creator | Any employee | Procurement only (permission: `purchase_order.create`) |

---

## Approval Workflows

### Purchase Request (amount-based via existing DEPT_MGR / PLANT_MGR positions)

| Tier | Amount | Steps |
|---|---|---|
| PR-Small | ≤ 50,000 THB | DEPT_MGR (1 step) |
| PR-Large | > 50,000 THB | DEPT_MGR → PLANT_MGR (2 steps) |

### Purchase Order (org-wide single workflow)

| Tier | Amount | Steps |
|---|---|---|
| PO-Standard | All amounts | PLANT_MGR (1 step) |

---

## Document Forms & Fields

### PR Form (`purchase_request_default`)

| field_key | Type | Required | editable_by |
|---|---|---|---|
| `title` | text | ✓ | requester |
| `vendor_name` | text | — | requester |
| `required_date` | date | ✓ | requester |
| `budget_code` | text | — | requester |
| `reason` | textarea | ✓ | requester |
| `amount` | number | ✓ | requester (auto-sum from line items, display only) |
| `approver_note` | textarea | — | step_1 |

### PO Form (`purchase_order_default`)

| field_key | Type | Required | editable_by |
|---|---|---|---|
| `title` | text | ✓ | requester |
| `vendor_name` | text | ✓ | requester |
| `vendor_address` | textarea | — | requester |
| `delivery_date` | date | ✓ | requester |
| `payment_terms` | select | ✓ | requester |
| `amount` | number | ✓ | requester (auto-sum from line items) |
| `approver_note` | textarea | — | step_1 |

`payment_terms` options: `cash` / `net_30` / `net_60`

### Line Items (both PR and PO)

| Column | Type | Note |
|---|---|---|
| `item_name` | string | ชื่อสินค้า/บริการ |
| `qty` | decimal | จำนวน |
| `unit` | string | หน่วย (ชิ้น/กล่อง/ชุด) |
| `unit_price` | decimal | ราคาต่อหน่วย |
| `total_price` | decimal | qty × unit_price (stored, not computed) |
| `notes` | text | หมายเหตุ (nullable) |

---

## PR → PO Conversion Flow

```
1. Employee: POST /purchase-requests
   → PurchaseRequestItem records created
   → ApprovalFlowService::start('purchase_request', dept_id, user_id, null, payload, 'purchase_request_default', amount)
   → ApprovalInstance (status=pending, reference_no=PR-2026-XXXXX)

2. DEPT_MGR approves (+ PLANT_MGR if amount > 50k)
   → Existing ApprovalController::act() — no changes needed
   → ApprovalInstance status=approved

3. Procurement: GET /purchase-orders/create?from_pr={instance_id}
   → Validates: PR status=approved, no existing PO for this PR
   → Pre-fills: title, vendor_name, line items (qty + unit_price editable)
   → Adds: vendor_address, delivery_date, payment_terms
   → POST /purchase-orders
   → PurchaseOrderItem records created
   → ApprovalFlowService::start('purchase_order', null, user_id, null, payload, 'purchase_order_default', amount)
   → PO instance stores: payload.parent_reference=PR_reference_no, payload.purchase_request_id=PR_instance_id

4. PLANT_MGR approves PO
   → ApprovalInstance status=approved → procurement cycle complete
```

**Guards on PO create:**
- PR must have `status = 'approved'`
- No existing PO with `payload->purchase_request_id = PR instance.id`
- User must have `purchase_order.create` permission

---

## Navigation

```
Purchasing (parent, sort_order=5, icon=shopping-cart)
├── Purchase Requests  → /purchase-requests       (view_purchase_requests permission)
├── Create PR          → /purchase-requests/create (no permission, any user)
├── Purchase Orders    → /purchase-orders          (view_purchase_orders permission)
└── Create PO          → /purchase-orders/create   (purchase_order.create permission)
```

---

## Permissions (new, add to PermissionSeeder + RolePermissionSeeder)

| Permission | Description | Grant to |
|---|---|---|
| `view_purchase_requests` | View PR list/detail | admin, viewer, approver |
| `view_purchase_orders` | View PO list/detail | admin, viewer, approver |
| `purchase_order.create` | Create PO from approved PR | admin |

Note: Creating PR requires no special permission (like repair_request — any authenticated user).

---

## File Map

| Action | File |
|---|---|
| Create | `database/migrations/xxxx_create_purchase_request_items_table.php` |
| Create | `database/migrations/xxxx_create_purchase_order_items_table.php` |
| Create | `app/Models/PurchaseRequestItem.php` |
| Create | `app/Models/PurchaseOrderItem.php` |
| Create | `app/Http/Controllers/Web/PurchaseRequestController.php` |
| Create | `app/Http/Controllers/Web/PurchaseOrderController.php` |
| Create | `resources/views/purchase-requests/index.blade.php` |
| Create | `resources/views/purchase-requests/create.blade.php` |
| Create | `resources/views/purchase-requests/show.blade.php` |
| Create | `resources/views/purchase-orders/index.blade.php` |
| Create | `resources/views/purchase-orders/create.blade.php` |
| Create | `resources/views/purchase-orders/show.blade.php` |
| Create | `database/seeders/PurchaseWorkflowSeeder.php` |
| Modify | `database/seeders/DatabaseSeeder.php` |
| Modify | `database/seeders/NavigationMenuSeeder.php` |
| Modify | `database/seeders/PermissionSeeder.php` |
| Modify | `database/seeders/RolePermissionSeeder.php` |
| Modify | `routes/web.php` |
| Modify | `lang/en/common.php` + `lang/th/common.php` |
| Modify | `resources/lang/en/common.php` + `resources/lang/th/common.php` |

**Reused without modification:**
- `app/Services/ApprovalFlowService.php`
- `app/Http/Controllers/Web/ApprovalController.php`
- All existing Blade components and layout

---

## Verification

1. `php artisan migrate && php artisan db:seed --class=PurchaseWorkflowSeeder`
2. Login as regular user → สร้าง PR พร้อม line items → submit
3. Login as DEPT_MGR user → approve PR
4. ถ้า amount > 50k → login as PLANT_MGR → approve PR step 2
5. Login as admin (has `purchase_order.create`) → `/purchase-orders/create?from_pr={id}` → pre-fill ปรากฏ → submit PO
6. Login as PLANT_MGR → approve PO → status=approved
7. ตรวจสอบ PR show page: ปุ่ม "สร้าง PO" ปรากฏเฉพาะเมื่อ PR approved + มีสิทธิ์
8. ตรวจสอบ PO show page: link กลับ PR ต้นทาง
9. `php artisan test` — all tests pass
