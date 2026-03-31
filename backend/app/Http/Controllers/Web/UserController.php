<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();
        $totalUsers = User::count();

        return view('users.index', compact('users', 'totalUsers'));
    }

    public function importForm(): View
    {
        return view('users.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'file.required' => __('users.import_file_required'),
            'file.mimes' => __('users.import_file_mimes'),
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if (count($row) < 3) {
                continue;
            }
            $data = array_combine($header, array_pad($row, count($header), null));
            $email = trim($data['email'] ?? $data['อีเมล'] ?? '');
            if (empty($email)) {
                $skipped++;

                continue;
            }
            if (User::where('email', $email)->exists()) {
                $skipped++;
                $errors[] = __('users.import_skip_duplicate', ['email' => $email]);

                continue;
            }
            $firstName = trim($data['first_name'] ?? $data['ชื่อ'] ?? $data['name'] ?? '');
            $lastName = trim($data['last_name'] ?? $data['นามสกุล'] ?? '');
            if (empty($firstName) && empty($lastName)) {
                $firstName = explode('@', $email)[0];
            }
            try {
                User::create([
                    'first_name' => $firstName ?: '-',
                    'last_name' => $lastName ?: '-',
                    'email' => $email,
                    'password' => Str::random(12),
                    'department_id' => ($dept = Department::where('name', trim($data['department'] ?? $data['แผนก'] ?? ''))->first()) ? $dept->id : null,
                    'department' => trim($data['department'] ?? $data['แผนก'] ?? '') ?: null,
                    'position' => trim($data['position'] ?? $data['ตำแหน่ง'] ?? '') ?: null,
                    'phone' => trim($data['phone'] ?? $data['เบอร์โทร'] ?? '') ?: null,
                    'remark' => trim($data['remark'] ?? $data['หมายเหตุ'] ?? '') ?: null,
                    'is_active' => true,
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = 'Row '.($index + 2).': '.$e->getMessage();
            }
        }

        $message = __('users.import_result', ['created' => $created, 'skipped' => $skipped]);
        if (! empty($errors)) {
            $message .= ' '.__('users.import_errors', ['count' => count($errors)]);

            return redirect()->route('users.import')->with('success', $message)->with('import_errors', array_slice($errors, 0, 10));
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        $permissionMatrix = $this->buildPermissionMatrix();
        $positions = Position::query()->where('is_active', true)->orderBy('name')->get();
        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();

        return view('users.create', compact('roles', 'permissionMatrix', 'positions', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'phone' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:1000',
            'role_id' => 'required_if:role_type,default',
            'permissions' => 'required_if:role_type,custom|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'email.unique' => __('users.validation_email_unique'),
            'role_id.required_if' => __('users.validation_role_required'),
            'permissions.required_if' => __('users.validation_permissions_required'),
        ]);

        $position = Position::labelsForUser($request->input('position_id'));

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Str::random(12),
            'department_id' => $request->department_id,
            'department' => $request->department_id ? Department::find($request->department_id)?->name : null,
            'position_id' => $position['id'],
            'position' => $position['name'],
            'phone' => $request->phone,
            'remark' => $request->remark,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $roleType = $request->input('role_type', 'default');

        if ($roleType === 'default' && $request->filled('role_id')) {
            $role = Role::find($request->role_id);
            if ($role) {
                $user->assignRole($role);
            }
        } elseif ($roleType === 'custom') {
            $permissionIds = (array) $request->input('permissions', []);
            $permissions = ! empty($permissionIds)
                ? Permission::whereIn('id', $permissionIds)->pluck('name')
                : collect();
            $user->syncPermissions($permissions);
        }

        return redirect()->route('users.index')->with('success', __('users.user_created'));
    }

    public function show(int $id): View
    {
        $user = User::with('roles', 'permissions')->findOrFail($id);

        return view('users.show', compact('user'));
    }

    public function edit(int $id): View
    {
        $user = User::with('roles', 'permissions')->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        $permissionMatrix = $this->buildPermissionMatrix();
        $positions = Position::query()
            ->where(function ($q) use ($user) {
                $q->where('is_active', true);
                if ($user->position_id) {
                    $q->orWhere('id', $user->position_id);
                }
            })
            ->orderBy('name')
            ->get();
        $departments = Department::query()
            ->where(function ($q) use ($user) {
                $q->where('is_active', true);
                if ($user->department_id) {
                    $q->orWhere('id', $user->department_id);
                }
            })
            ->orderBy('name')
            ->get();

        return view('users.edit', compact('user', 'roles', 'permissionMatrix', 'positions', 'departments'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        if ($request->has('toggle_active')) {
            $user->update(['is_active' => ! $user->is_active]);
            $status = $user->is_active ? 'enabled' : 'disabled';

            return redirect()->route('users.index')->with('success', __("users.user_{$status}"));
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'phone' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:1000',
        ]);

        $position = Position::labelsForUser($request->input('position_id'));

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'department_id' => $request->department_id,
            'department' => $request->department_id ? Department::find($request->department_id)?->name : null,
            'position_id' => $position['id'],
            'position' => $position['name'],
            'phone' => $request->phone,
            'remark' => $request->remark,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $roleType = $request->input('role_type', 'default');
        if ($roleType === 'default' && $request->filled('role_id')) {
            $user->syncRoles([]);
            $role = Role::find($request->role_id);
            if ($role) {
                $user->assignRole($role);
            }
            $user->syncPermissions([]);
        } elseif ($roleType === 'custom') {
            $user->syncRoles([]);
            $permissionIds = (array) $request->input('permissions', []);
            $permissions = ! empty($permissionIds)
                ? Permission::whereIn('id', $permissionIds)->pluck('name')
                : collect();
            $user->syncPermissions($permissions);
        }

        return redirect()->route('users.index')->with('success', __('common.updated'));
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->is_super_admin) {
            return redirect()->route('users.index')->with('error', __('users.cannot_delete_super_admin'));
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', __('users.user_deleted'));
    }

    private function buildPermissionMatrix(): array
    {
        $allPerms = Permission::orderBy('name')->get();
        $actions = ['create', 'read', 'update', 'delete', 'export'];

        $grouped = [];
        foreach ($allPerms as $perm) {
            $module = $perm->module ?? explode('.', $perm->name)[0];
            $action = $perm->action ?? (explode('.', $perm->name)[1] ?? '');
            $grouped[$module][$action] = $perm->id;
        }

        $matrix = [];
        foreach ($grouped as $module => $moduleActions) {
            $translationKey = "users.module_{$module}";
            $translated = __($translationKey);
            $label = $translated !== $translationKey ? $translated : ucfirst(str_replace('_', ' ', $module));

            $row = [
                'module' => $module,
                'label' => $label,
                'actions' => [],
            ];

            foreach ($actions as $action) {
                $row['actions'][$action] = $moduleActions[$action] ?? null;
            }

            $matrix[] = $row;
        }

        return $matrix;
    }
}
