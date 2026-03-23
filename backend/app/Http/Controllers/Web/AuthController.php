<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\Auth\AuthModeService;
use App\Services\Auth\EntraOAuthService;
use App\Services\Auth\LdapAuthService;
use App\Services\Auth\PasswordCapabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (session('api_token')) {
            return redirect()->route('dashboard');
        }

        $systemLogo = Setting::get('system_logo');
        $loginBackground = Setting::get('login_background');
        $loginBackgroundColor = Setting::get('login_background_color', '#2563eb');
        $loginIllustration = Setting::get('login_illustration');

        $authLocalEnabled = AuthModeService::isLocalEnabled();
        $authEntraEnabled = AuthModeService::isEntraEnabled() && AuthModeService::entraConfigured();
        $authLdapEnabled = AuthModeService::isLdapEnabled() && AuthModeService::ldapConfigured() && extension_loaded('ldap');
        $authConfigured = AuthModeService::anyMethodEnabled()
            && ($authLocalEnabled || $authEntraEnabled || $authLdapEnabled);

        return view('auth.login', compact(
            'systemLogo',
            'loginBackground',
            'loginBackgroundColor',
            'loginIllustration',
            'authLocalEnabled',
            'authEntraEnabled',
            'authLdapEnabled',
            'authConfigured'
        ));
    }

    public function login(Request $request): RedirectResponse
    {
        if (! AuthModeService::isLocalEnabled()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.local_disabled')]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // เรียก logic login ของ API โดยตรง — ไม่ผ่าน Kernel ซ้ำ (หลีกเลี่ยงปัญหา Host/URI ของ Request ย่อย)
        $apiRequest = Request::create('/api/v1/auth/login', 'POST', [
            'email' => $request->email,
            'password' => $request->password,
            'device_name' => 'web-browser',
        ]);

        $jsonResponse = app(ApiAuthController::class)->login($apiRequest);
        $data = $jsonResponse->getData(true);

        if (! is_array($data) || ! ($data['success'] ?? false)) {
            $message = is_array($data) ? ($data['message'] ?? __('auth.failed')) : __('auth.failed');
            if ($jsonResponse->getStatusCode() === 403 && is_array($data) && isset($data['message'])) {
                $message = $data['message'];
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $message]);
        }

        $userData = $data['data']['user'] ?? $data['data'] ?? [];

        return $this->establishSessionFromUserArray($request, $data['data']['token'] ?? null, $userData);
    }

    public function redirectToEntra(): RedirectResponse
    {
        if (! AuthModeService::isEntraEnabled() || ! AuthModeService::entraConfigured()) {
            return redirect()->route('login')->withErrors(['email' => __('auth.entra_unavailable')]);
        }

        return redirect()->away(app(EntraOAuthService::class)->authorizationUrl());
    }

    public function entraCallback(Request $request): RedirectResponse
    {
        $result = app(EntraOAuthService::class)->handleCallback($request);
        if (! ($result['success'] ?? false)) {
            return redirect()->route('login')->withErrors(['email' => $result['message'] ?? __('auth.failed')]);
        }

        /** @var User $user */
        $user = $result['user'];
        $token = $user->createToken('web-browser')->plainTextToken;

        return $this->establishSessionFromUserModel($request, $token, $user->fresh());
    }

    public function loginLdap(Request $request): RedirectResponse
    {
        if (! AuthModeService::isLdapEnabled() || ! AuthModeService::ldapConfigured()) {
            return back()
                ->withInput($request->only('ldap_email'))
                ->withErrors(['ldap_email' => __('auth.ldap_unavailable')]);
        }

        if (! extension_loaded('ldap')) {
            return back()
                ->withInput($request->only('ldap_email'))
                ->withErrors(['ldap_email' => __('auth.ldap_extension_missing')]);
        }

        $request->validate([
            'ldap_email' => 'required|email',
            'ldap_password' => 'required',
        ]);

        $user = app(LdapAuthService::class)->attempt(
            $request->input('ldap_email'),
            $request->input('ldap_password')
        );

        if (! $user) {
            return back()
                ->withInput($request->only('ldap_email'))
                ->withErrors(['ldap_email' => __('auth.failed')]);
        }

        $token = $user->createToken('web-browser')->plainTextToken;

        return $this->establishSessionFromUserModel($request, $token, $user);
    }

    public function logout(Request $request): RedirectResponse
    {
        $token = session('api_token');
        if ($token) {
            \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->delete();
        }

        session()->forget(['api_token', 'user', 'user_permissions']);

        return redirect()->route('login');
    }

    private function establishSessionFromUserModel(Request $request, ?string $token, User $user): RedirectResponse
    {
        return $this->establishSessionFromUserArray($request, $token, [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'auth_provider' => $user->auth_provider,
            'roles' => $user->getRoleNames()->toArray(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $userData
     */
    private function establishSessionFromUserArray(Request $request, ?string $token, array $userData): RedirectResponse
    {
        $roles = $userData['roles'] ?? [];
        $isSuperAdmin = in_array('super-admin', $roles, true) || in_array('admin', $roles, true);
        $authProvider = $userData['auth_provider'] ?? null;

        session([
            'api_token' => $token,
            'user' => [
                'id' => $userData['id'] ?? null,
                'first_name' => $userData['first_name'] ?? '',
                'last_name' => $userData['last_name'] ?? '',
                'name' => trim(($userData['first_name'] ?? '').' '.($userData['last_name'] ?? '')) ?: ($userData['name'] ?? ''),
                'email' => $userData['email'] ?? '',
                'avatar' => $userData['avatar'] ?? null,
                'auth_provider' => $authProvider,
                'can_change_password' => PasswordCapabilityService::canChangePasswordFromAuthProvider($authProvider),
                'roles' => $roles,
                'is_super_admin' => $isSuperAdmin,
            ],
            'user_permissions' => $userData['permissions'] ?? [],
        ]);

        $intended = session()->pull('intended');
        $baseUrl = $request->getSchemeAndHttpHost();

        if ($intended) {
            $path = parse_url($intended, PHP_URL_PATH) ?: $intended;
            $query = parse_url($intended, PHP_URL_QUERY);

            return redirect($baseUrl.$path.($query ? '?'.$query : ''));
        }

        return redirect($baseUrl.'/dashboard');
    }
}
