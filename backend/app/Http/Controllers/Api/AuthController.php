<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required',
            'device_name' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated.',
            ], 403);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_at' => null,
                'user'       => [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'avatar'         => $user->avatar,
                    'is_active'      => $user->is_active,
                    'last_active_at' => $user->last_active_at?->toIso8601String(),
                    'roles'          => $user->getRoleNames()->toArray(),
                    'permissions'    => $user->getAllPermissions()->pluck('name')->toArray(),
                ],
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
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'avatar'         => $user->avatar,
                'is_active'      => $user->is_active,
                'last_active_at' => $user->last_active_at?->toIso8601String(),
                'roles'          => $user->getRoleNames()->toArray(),
                'permissions'    => $user->getAllPermissions()->pluck('name')->toArray(),
            ],
        ]);
    }
}
