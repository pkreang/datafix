<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const ADMIN_CARDS = [
        'school_pending_approvals',
        'school_submissions_this_month',
        'active_users',
    ];

    private const DEFAULT_CARDS = [
        'school_my_pending_requests',
        'school_draft_forms',
        'school_my_submissions_this_month',
    ];

    public function index(): View
    {
        $user = Auth::user();
        $roleNames = $user->roles->pluck('name');
        $isManager = $roleNames->intersect(['super-admin', 'admin'])->isNotEmpty();
        $defaultCards = $isManager ? self::ADMIN_CARDS : self::DEFAULT_CARDS;

        $savedConfig = $user->dashboard_config;
        $enabledCards = $savedConfig['cards'] ?? $defaultCards;

        $canCustomize = $user->can('manage_own_dashboard');

        return view('dashboard', compact('enabledCards', 'defaultCards', 'canCustomize'));
    }
}
