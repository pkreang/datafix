<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ReportDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $isSuperAdmin = $user?->is_super_admin ?? false;

        $dashboards = ReportDashboard::withCount('widgets')
            ->where('is_active', true)
            ->when(! $isSuperAdmin, function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('visibility', 'all')
                        ->orWhere(function ($q2) use ($user) {
                            $q2->where('visibility', 'permission')
                                ->whereIn('required_permission', $user?->getAllPermissions()->pluck('name') ?? []);
                        });
                });
            })
            ->orderBy('created_at')
            ->get();

        return view('reports.index', compact('dashboards'));
    }

    public function showDashboard(ReportDashboard $dashboard, Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (! $dashboard->is_active) {
            abort(404);
        }

        // Permission check: if visibility=permission, user must have required_permission
        if ($dashboard->visibility === 'permission' && $dashboard->required_permission) {
            $user = $request->user();
            if (! $user) {
                return redirect()->route('login');
            }

            $isSuperAdmin = $user->is_super_admin ?? false;
            if (! $isSuperAdmin && ! $user->hasPermissionTo($dashboard->required_permission)) {
                abort(403);
            }
        }

        $dashboard->load('widgets');

        $apiToken = session('api_token');

        $departments = \App\Models\Department::orderBy('name')->where('is_active', true)->get(['id', 'name']);

        return view('reports.dashboards.show', compact('dashboard', 'apiToken', 'departments'));
    }
}
