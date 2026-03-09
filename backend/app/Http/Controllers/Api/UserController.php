<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with('roles', 'permissions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'roles'    => 'array',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created.',
            'data'    => $user->load('roles'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'roles' => 'array',
        ]);

        $user->update($request->only(['name', 'email', 'is_active']));

        if ($request->has('password') && $request->password) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated.',
            'data'    => $user->load('roles'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted.',
        ]);
    }
}
