<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PermissionDisplay;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::query()->orderBy('module')->orderBy('name')->get();

        $data = $permissions->map(function (Permission $perm) {
            $moduleKey = $this->moduleKeyForPermission($perm);

            return array_merge($perm->only([
                'id', 'name', 'guard_name', 'module', 'action', 'created_at', 'updated_at',
            ]), [
                'module_key' => $moduleKey,
                'display_name' => PermissionDisplay::label($perm->name),
                'module_display' => PermissionDisplay::module($moduleKey),
            ]);
        });

        $grouped = [];
        foreach ($permissions as $perm) {
            $moduleKey = $this->moduleKeyForPermission($perm);
            $parts = explode('.', $perm->name, 2);
            $action = $parts[1] ?? $perm->name;
            $grouped[$moduleKey][] = [
                'id' => $perm->id,
                'name' => $perm->name,
                'action' => $action,
                'display_name' => PermissionDisplay::label($perm->name),
                'module_display' => PermissionDisplay::module($moduleKey),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'grouped' => $grouped,
            'total' => $permissions->count(),
        ]);
    }

    private function moduleKeyForPermission(Permission $permission): string
    {
        if (filled($permission->module)) {
            return (string) $permission->module;
        }

        $parts = explode('.', $permission->name, 2);

        return $parts[0] ?? 'other';
    }
}
