<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $payload = [
            'email'        => $request->email,
            'password'     => $request->password,
            'device_name'  => 'web-browser',
        ];
        $apiRequest = Request::create(
            '/api/v1/auth/login',
            'POST',
            $payload,
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $apiRequest->headers->set('Accept', 'application/json');
        $apiRequest->cookies->replace($request->cookies->all());

        $response = app()->handle($apiRequest);
        $data = json_decode($response->getContent(), true);

        if (! ($data['success'] ?? false)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $data['message'] ?? 'Invalid credentials.']);
        }

        $userData = $data['data']['user'] ?? $data['data'] ?? [];
        $roles = $userData['roles'] ?? [];
        $isSuperAdmin = in_array('super-admin', $roles) || in_array('admin', $roles);

        session([
            'api_token'        => $data['data']['token'] ?? null,
            'user'             => [
                'id'             => $userData['id'] ?? null,
                'first_name'     => $userData['first_name'] ?? '',
                'last_name'      => $userData['last_name'] ?? '',
                'name'           => trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')) ?: ($userData['name'] ?? ''),
                'email'          => $userData['email'] ?? '',
                'avatar'         => $userData['avatar'] ?? null,
                'roles'          => $roles,
                'is_super_admin' => $isSuperAdmin,
            ],
            'user_permissions' => $userData['permissions'] ?? [],
        ]);

        $intended = session()->pull('intended');
        $baseUrl = $request->getSchemeAndHttpHost();

        if ($intended) {
            $path = parse_url($intended, PHP_URL_PATH) ?: $intended;
            $query = parse_url($intended, PHP_URL_QUERY);
            return redirect($baseUrl . $path . ($query ? '?' . $query : ''));
        }

        return redirect($baseUrl . '/dashboard');
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
}
