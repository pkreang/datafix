<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Setting;
use App\Models\User;
use App\Rules\PasswordPolicy;
use App\Services\Auth\PasswordCapabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class ProfileController extends Controller
{
    protected function currentUser(): ?User
    {
        $token = session('api_token');
        if ($token) {
            $model = PersonalAccessToken::findToken($token);

            return $model?->tokenable;
        }
        $userId = session('user')['id'] ?? null;

        return $userId ? User::find($userId) : null;
    }

    public function edit(): View|RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->route('login');
        }

        $positions = Position::query()
            ->where(function ($q) use ($user) {
                $q->where('is_active', true);
                if ($user->position_id) {
                    $q->orWhere('id', $user->position_id);
                }
            })
            ->orderBy('name')
            ->get();

        return view('profile.edit', [
            'user' => $user,
            'positions' => $positions,
            'canChangePasswordInApp' => PasswordCapabilityService::canChangePasswordInApp($user),
            'authPasswordHelpUrl' => trim((string) Setting::get('auth_password_help_url', '')),
        ]);
    }

    public function showPasswordForm(): View|RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! PasswordCapabilityService::canChangePasswordInApp($user)) {
            return redirect()
                ->route('profile.edit')
                ->with('info', __('auth.password_change_unavailable_hint'));
        }

        $passwordPolicy = $this->getPasswordPolicyRules();

        return view('profile.password', compact('passwordPolicy'));
    }

    private function getPasswordPolicyRules(): array
    {
        $min = Setting::getInt('password_min_length', 8);
        $max = Setting::getInt('password_max_length', 255);
        $rules = [];

        $rules[] = __('password_policy.rule_min_chars', ['min' => $min]);
        $rules[] = __('password_policy.rule_max_chars', ['max' => $max]);
        if (Setting::getBool('password_require_uppercase')) {
            $rules[] = __('password_policy.rule_uppercase');
        }
        if (Setting::getBool('password_require_lowercase')) {
            $rules[] = __('password_policy.rule_lowercase');
        }
        if (Setting::getBool('password_require_number')) {
            $rules[] = __('password_policy.rule_number');
        }
        if (Setting::getBool('password_require_special')) {
            $rules[] = __('password_policy.rule_special');
        }
        $expiry = Setting::getInt('password_expires_days', 0);
        if ($expiry > 0) {
            $rules[] = __('password_policy.rule_expiry', ['days' => $expiry]);
        }
        if (Setting::getBool('password_force_change_first_login')) {
            $rules[] = __('password_policy.rule_first_login');
        }
        $reuse = Setting::getInt('password_prevent_reuse', 0);
        if ($reuse > 0) {
            $rules[] = __('password_policy.rule_reuse', ['n' => $reuse]);
        }

        return $rules;
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'position_id' => 'nullable|exists:positions,id',
            'line_notify_token' => 'nullable|string|max:255',
        ]);

        $pos = Position::labelsForUser($request->input('position_id'));

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'department' => $request->department,
            'position_id' => $pos['id'],
            'position' => $pos['name'],
            'line_notify_token' => $request->line_notify_token ?: null,
        ]);

        $sessionUser = session('user', []);
        $sessionUser['first_name'] = $user->first_name;
        $sessionUser['last_name'] = $user->last_name;
        $sessionUser['name'] = $user->full_name;
        session(['user' => $sessionUser]);

        return back()->with('success', __('common.profile_updated'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->currentUser();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! PasswordCapabilityService::canChangePasswordInApp($user)) {
            return redirect()
                ->route('profile.edit')
                ->with('info', __('auth.password_change_unavailable_hint'));
        }

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', new PasswordPolicy],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => __('common.current_password_incorrect')]);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', __('common.password_changed'));
    }
}
