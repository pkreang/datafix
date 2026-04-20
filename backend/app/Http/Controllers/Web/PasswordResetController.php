<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Rules\PasswordNotReused;
use App\Rules\PasswordPolicy;
use App\Services\Auth\AuthModeService;
use App\Services\Auth\PasswordCapabilityService;
use App\Services\Auth\PasswordLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /** @return array<string, mixed> */
    private function guestBranding(): array
    {
        return [
            'systemLogo' => Setting::get('system_logo'),
            'loginBackground' => Setting::get('login_background'),
            'loginBackgroundColor' => Setting::get('login_background_color', '#2563eb'),
            'loginIllustration' => Setting::get('login_illustration'),
        ];
    }

    public function showForgotForm(Request $request): View|RedirectResponse
    {
        if (session('api_token')) {
            return redirect()->route('dashboard');
        }

        if (! AuthModeService::isLocalEnabled()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => __('auth.forgot_password_unavailable')]);
        }

        return view('auth.forgot-password', array_merge($this->guestBranding(), [
            'pageTitle' => __('auth.forgot_password_page_title'),
        ]));
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        if (! AuthModeService::isLocalEnabled()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => __('auth.forgot_password_unavailable')]);
        }

        $request->validate([
            'email' => 'required|email',
        ], [], [
            'email' => __('auth.placeholder_email'),
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $user = User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();

        if ($user && $user->is_active && PasswordCapabilityService::canChangePasswordInApp($user)) {
            $status = Password::broker()->sendResetLink(['email' => $user->email]);

            if ($status === Password::RESET_THROTTLED) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => __('passwords.throttled')]);
            }
        }

        return back()
            ->withInput($request->only('email'))
            ->with('status', __('passwords.sent'));
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (session('api_token')) {
            return redirect()->route('dashboard');
        }

        if (! AuthModeService::isLocalEnabled()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => __('auth.forgot_password_unavailable')]);
        }

        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $email = $request->query('email');
        $token = $request->query('token');

        return view('auth.reset-password', array_merge($this->guestBranding(), [
            'pageTitle' => __('auth.reset_password_page_title'),
            'token' => $token,
            'email' => $email,
            'passwordPolicyLines' => $this->passwordPolicyLines(),
        ]));
    }

    public function reset(Request $request): RedirectResponse
    {
        if (! AuthModeService::isLocalEnabled()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => __('auth.forgot_password_unavailable')]);
        }

        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $normalized = strtolower(trim((string) $request->input('email')));
        $user = User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalized])
            ->first();

        if (! $user || ! $user->is_active || ! PasswordCapabilityService::canChangePasswordInApp($user)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.user')]);
        }

        $request->validate([
            'password' => ['required', 'confirmed', new PasswordPolicy, new PasswordNotReused($user)],
        ], [], [
            'password' => __('auth.reset_password_new'),
        ]);

        $payload = $request->only('email', 'password', 'password_confirmation', 'token');
        $payload['email'] = $user->email;

        $status = Password::broker()->reset(
            $payload,
            function (User $resetUser, string $password): void {
                PasswordLifecycleService::applySelfServicePasswordChange($resetUser, $password);
            }
        );

        return match ($status) {
            Password::PASSWORD_RESET => redirect()
                ->route('login')
                ->with('status', __('passwords.reset')),
            Password::INVALID_TOKEN => redirect()
                ->route('password.request')
                ->withErrors(['email' => __('passwords.token')]),
            Password::INVALID_USER => redirect()
                ->route('password.request')
                ->withErrors(['email' => __('passwords.user')]),
            default => back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => is_string($status) ? __($status) : __('passwords.token')]),
        };
    }

    /** @return list<string> */
    private function passwordPolicyLines(): array
    {
        $min = Setting::getInt('password_min_length', 8);
        $max = Setting::getInt('password_max_length', 255);
        $lines = [];
        $lines[] = __('password_policy.rule_min_chars', ['min' => $min]);
        $lines[] = __('password_policy.rule_max_chars', ['max' => $max]);
        if (Setting::getBool('password_require_uppercase')) {
            $lines[] = __('password_policy.rule_uppercase');
        }
        if (Setting::getBool('password_require_lowercase')) {
            $lines[] = __('password_policy.rule_lowercase');
        }
        if (Setting::getBool('password_require_number')) {
            $lines[] = __('password_policy.rule_number');
        }
        if (Setting::getBool('password_require_special')) {
            $lines[] = __('password_policy.rule_special');
        }
        $reuse = Setting::getInt('password_prevent_reuse', 0);
        if ($reuse > 0) {
            $lines[] = __('password_policy.rule_reuse', ['n' => $reuse]);
        }

        return $lines;
    }
}
