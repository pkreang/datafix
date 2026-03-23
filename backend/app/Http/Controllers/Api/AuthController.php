<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        if (! AuthModeService::isLocalEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.local_disabled'),
            ], 403);
        }

        $email = strtolower(trim((string) $request->input('email')));

        $user = User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.failed'),
            ], 401);
        }

        // บัญชีจาก Entra/LDAP มีรหัสสุ่มใน DB — ให้ไป login ทาง SSO / LDAP
        if (in_array($user->auth_provider, ['entra', 'ldap'], true)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.directory_password_not_used'),
            ], 401);
        }

        $passwordHash = $user->getRawOriginal('password');

        if ($passwordHash === null || $passwordHash === '' || ! Hash::check($request->password, $passwordHash)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.failed'),
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated.',
            ], 403);
        }

        if (AuthModeService::isLocalSuperAdminOnly() && ! $user->is_super_admin) {
            return response()->json([
                'success' => false,
                'message' => __('auth.local_super_admin_only'),
            ], 403);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => null,
                'user' => $this->formatUser($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatUser($request->user()),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'department' => $user->department,
            'position' => $user->position,
            'is_active' => $user->is_active,
            'last_active_at' => $user->last_active_at?->toIso8601String(),
            'roles' => $user->getRoleNames()->toArray(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'auth_provider' => $user->auth_provider,
        ];
    }
}
