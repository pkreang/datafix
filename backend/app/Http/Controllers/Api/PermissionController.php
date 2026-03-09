<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('name')->get();

        $grouped = [];
        foreach ($permissions as $perm) {
            $parts = explode('.', $perm->name);
            $module = $parts[0] ?? 'other';
            $action = $parts[1] ?? $perm->name;
            $grouped[$module][] = [
                'id'     => $perm->id,
                'name'   => $perm->name,
                'action' => $action,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $permissions,
            'grouped' => $grouped,
            'total'   => $permissions->count(),
        ]);
    }
}
