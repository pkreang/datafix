<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingController extends Controller
{
    private const SETTINGS_KEYS = [
        'notifications.email_enabled',
        'notifications.approval_pending_email',
        'notifications.workflow_approved_email',
        'notifications.workflow_rejected_email',
        'notifications.line_enabled',
        'notifications.approval_pending_line',
        'notifications.workflow_approved_line',
        'notifications.workflow_rejected_line',
    ];

    public function index(): View
    {
        $settings = Setting::whereIn('key', self::SETTINGS_KEYS)
            ->pluck('value', 'key')
            ->toArray();

        return view('settings.notifications.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $toggles = $request->input('toggle', []);

        foreach (self::SETTINGS_KEYS as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => ($toggles[$key] ?? '0') === '1' ? '1' : '0']
            );
        }

        return redirect()
            ->route('settings.notifications.index')
            ->with('success', __('notifications.settings_saved'));
    }
}
