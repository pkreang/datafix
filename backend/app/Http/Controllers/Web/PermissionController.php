<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
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

        $total = $permissions->count();

        return view('permissions.index', compact('grouped', 'total'));
    }
}
