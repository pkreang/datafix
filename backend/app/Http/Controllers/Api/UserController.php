<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        $query = User::with(['roles', 'company', 'branch']);

        // Search by first_name, last_name, or email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by company_id
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->get('company_id'));
        }

        // Filter by branch_id
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        // Filter by is_active
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with(['roles', 'permissions', 'company', 'branch'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department' => 'nullable|string|max:255',
            'position_id' => 'nullable|exists:positions,id',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'roles' => 'array',
        ]);

        $pos = ['id' => null, 'name' => null];
        if ($request->filled('position_id')) {
            $pos = Position::labelsForUser($request->input('position_id'));
        } elseif ($request->filled('position')) {
            $pos = ['id' => null, 'name' => $request->position];
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'company_id' => $request->company_id,
            'branch_id' => $request->branch_id,
            'department' => $request->department,
            'position_id' => $pos['id'],
            'position' => $pos['name'],
            'phone' => $request->phone,
        ]);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created.',
            'data' => $user->load(['roles', 'company', 'branch']),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'company_id' => 'sometimes|nullable|exists:companies,id',
            'branch_id' => 'sometimes|nullable|exists:branches,id',
            'department' => 'sometimes|nullable|string|max:255',
            'position_id' => 'sometimes|nullable|exists:positions,id',
            'position' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'roles' => 'array',
        ]);

        $data = $request->only([
            'first_name', 'last_name', 'email',
            'company_id', 'branch_id',
            'department', 'phone',
            'is_active',
        ]);

        if ($request->has('position_id')) {
            $p = Position::labelsForUser($request->input('position_id'));
            $data['position_id'] = $p['id'];
            $data['position'] = $p['name'];
        } elseif ($request->has('position')) {
            $data['position'] = $request->position;
            if ($request->input('position') === null || $request->input('position') === '') {
                $data['position_id'] = null;
            }
        }

        $user->update($data);

        if ($request->has('password') && $request->password) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated.',
            'data' => $user->load(['roles', 'company', 'branch']),
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
