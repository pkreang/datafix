<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::withCount('permissions')->get()->map(function ($role) {
            $role->users_count = $role->users()->count();
            return $role;
        });

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);
        $role->users_count = $role->users()->count();

        $permissionsByModule = [];
        foreach ($role->permissions as $perm) {
            $parts = explode('.', $perm->name);
            $module = $parts[0] ?? 'other';
            $action = $parts[1] ?? $perm->name;
            $permissionsByModule[$module][] = [
                'id'     => $perm->id,
                'name'   => $perm->name,
                'action' => $action,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                    => $role->id,
                'name'                  => $role->name,
                'guard_name'            => $role->guard_name,
                'permissions_count'     => $role->permissions->count(),
                'users_count'           => $role->users_count,
                'permissions'           => $role->permissions,
                'permissions_by_module' => $permissionsByModule,
                'created_at'            => $role->created_at,
                'updated_at'            => $role->updated_at,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        $role->loadCount('permissions');
        $role->users_count = $role->users()->count();

        return response()->json([
            'success' => true,
            'message' => 'Role created.',
            'data'    => $role,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name'        => 'sometimes|string|unique:roles,name,' . $id,
            'permissions' => 'array',
        ]);

        if ($request->has('name')) {
            $role->update(['name' => $request->name]);
        }

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        $role->load('permissions');
        $role->users_count = $role->users()->count();

        return response()->json([
            'success' => true,
            'message' => 'Role updated.',
            'data'    => $role,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted.',
        ]);
    }
}
