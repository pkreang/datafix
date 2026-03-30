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
        return view('reports.index');
    }

    public function repairHistory(): View
    {
        return view('reports.repair-history');
    }

    public function pmAmHistory(): View
    {
        return view('reports.pm-am-history');
    }

    public function showDashboard(ReportDashboard $dashboard, Request $request): View|\Illuminate\Http\RedirectResponse
    {
        // Permission check: if visibility=permission, user must have required_permission
        if ($dashboard->visibility === 'permission' && $dashboard->required_permission) {
            $user = $request->user();
            if (!$user) {
                return redirect()->route('login');
            }

            $isSuperAdmin = $user->is_super_admin ?? false;
            if (!$isSuperAdmin && !$user->hasPermissionTo($dashboard->required_permission)) {
                abort(403);
            }
        }

        $dashboard->load('widgets');

        $apiToken = session('api_token');

        return view('reports.dashboards.show', compact('dashboard', 'apiToken'));
    }
}
