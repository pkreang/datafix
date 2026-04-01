<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Cards shown by default for admin / super-admin roles
    private const ADMIN_CARDS = [
        'repair_pending',
        'repair_this_month',
        'pm_pending',
        'pm_this_week',
        'spare_low_stock',
        'equipment_active',
    ];

    // Cards shown by default for viewer / approver / other roles
    private const DEFAULT_CARDS = [
        'my_pending_repairs',
        'pm_pending',
        'spare_low_stock',
    ];

    public function index(): View
    {
        $user        = Auth::user();
        $roleNames   = $user->roles->pluck('name');
        $isManager   = $roleNames->intersect(['super-admin', 'admin'])->isNotEmpty();
        $defaultCards = $isManager ? self::ADMIN_CARDS : self::DEFAULT_CARDS;

        $savedConfig  = $user->dashboard_config;
        $enabledCards = $savedConfig['cards'] ?? $defaultCards;

        $canCustomize = $user->can('manage_own_dashboard');

        return view('dashboard', compact('enabledCards', 'defaultCards', 'canCustomize'));
    }
}
