<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();
        $totalUsers = User::count();

        return view('users.index', compact('users', 'totalUsers'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        $permissionMatrix = $this->buildPermissionMatrix();

        return view('users.create', compact('roles', 'permissionMatrix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'department' => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'remark'     => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => '1234',
            'department' => $request->department,
            'position'   => $request->position,
            'remark'     => $request->remark,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        $roleType = $request->input('role_type', 'default');

        if ($roleType === 'default' && $request->filled('role_id')) {
            $role = Role::find($request->role_id);
            if ($role) {
                $user->assignRole($role);
            }
        } elseif ($roleType === 'custom' && $request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $user->syncPermissions($permissions);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function show(int $id): View
    {
        $user = User::with('roles', 'permissions')->findOrFail($id);

        return view('users.show', compact('user'));
    }

    public function edit(int $id): View
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        if ($request->has('toggle_active')) {
            $user->update(['is_active' => !$user->is_active]);
            $status = $user->is_active ? 'enabled' : 'disabled';
            return redirect()->route('users.index')->with('success', "User {$status} successfully");
        }

        return redirect()->route('users.index');
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->is_super_admin) {
            return redirect()->route('users.index')->with('error', 'Cannot delete a super admin user');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }

    private function buildPermissionMatrix(): array
    {
        $allPerms = Permission::orderBy('name')->get();
        $actions = ['create', 'read', 'update', 'delete', 'export'];

        $moduleOrder = [
            'dashboard', 'product', 'sales', 'purchase', 'expense',
            'report', 'loan', 'company_profile', 'user_access', 'integrations',
        ];

        $moduleLabels = [
            'dashboard'       => 'Dashboard',
            'product'         => 'Product',
            'sales'           => 'Sales',
            'purchase'        => 'Purchase',
            'expense'         => 'Expense',
            'report'          => 'Report',
            'loan'            => 'Loan',
            'company_profile' => 'Company profile',
            'user_access'     => 'User & access',
            'integrations'    => 'Integrations',
            'role_access'     => 'Role & permission',
            'permission_access' => 'Permission',
        ];

        $grouped = [];
        foreach ($allPerms as $perm) {
            $module = $perm->module ?? explode('.', $perm->name)[0];
            $action = $perm->action ?? (explode('.', $perm->name)[1] ?? '');
            $grouped[$module][$action] = $perm->id;
        }

        $matrix = [];
        $allModules = array_unique(array_merge($moduleOrder, array_keys($grouped)));

        foreach ($allModules as $module) {
            if (!isset($grouped[$module])) continue;

            $row = [
                'module' => $module,
                'label'  => $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module)),
                'actions' => [],
            ];

            foreach ($actions as $action) {
                $row['actions'][$action] = $grouped[$module][$action] ?? null;
            }

            $matrix[] = $row;
        }

        return $matrix;
    }
}
