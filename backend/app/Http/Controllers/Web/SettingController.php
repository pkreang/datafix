<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    private array $policyKeys = [
        'password_min_length',
        'password_max_length',
        'password_require_uppercase',
        'password_require_lowercase',
        'password_require_number',
        'password_require_special',
        'password_expires_days',
        'password_force_change_first_login',
        'password_prevent_reuse',
        'lockout_max_attempts',
        'lockout_duration_minutes',
    ];

    public function passwordPolicy(): View
    {
        $settings = [];
        foreach ($this->policyKeys as $key) {
            $settings[$key] = Setting::get($key);
        }

        return view('settings.password-policy', compact('settings'));
    }

    public function savePasswordPolicy(Request $request)
    {
        $request->validate([
            'password_min_length'        => 'required|integer|min:1|max:128',
            'password_max_length'        => 'required|integer|min:1|max:255',
            'password_expires_days'      => 'required|integer|min:0|max:365',
            'password_prevent_reuse'     => 'required|integer|min:0|max:24',
            'lockout_max_attempts'       => 'required|integer|min:0|max:100',
            'lockout_duration_minutes'   => 'required|integer|min:0|max:1440',
        ]);

        $boolKeys = [
            'password_require_uppercase',
            'password_require_lowercase',
            'password_require_number',
            'password_require_special',
            'password_force_change_first_login',
        ];

        foreach ($this->policyKeys as $key) {
            if (in_array($key, $boolKeys)) {
                Setting::set($key, $request->boolean($key) ? '1' : '0');
            } else {
                Setting::set($key, $request->input($key, '0'));
            }
        }

        return redirect()->route('settings.password-policy')
            ->with('success', 'Password policy updated successfully');
    }
}
