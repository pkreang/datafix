<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Department::query()->orderBy('name');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
            'message' => 'Departments retrieved successfully.',
        ]);
    }

    public function show(Department $department): JsonResponse
    {
        $department->load(['workflowBindings.workflow']);

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department retrieved successfully.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:departments,code',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $department = Department::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department created successfully.',
        ], 201);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => "required|string|max:100|unique:departments,code,{$department->id}",
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $department->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department updated successfully.',
        ]);
    }

    public function destroy(Department $department): JsonResponse
    {
        if ($department->workflowBindings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with active workflow bindings.',
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Department deleted successfully.',
        ]);
    }
}
