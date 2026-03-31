<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EquipmentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EquipmentCategory::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Equipment categories retrieved successfully.',
        ]);
    }

    public function show(EquipmentCategory $equipmentCategory): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $equipmentCategory,
            'message' => 'Equipment category retrieved successfully.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:equipment_categories,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $category = EquipmentCategory::create($validated);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Equipment category created successfully.',
        ], 201);
    }

    public function update(Request $request, EquipmentCategory $equipmentCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('equipment_categories', 'code')->ignore($equipmentCategory->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $equipmentCategory->update($validated);

        return response()->json([
            'success' => true,
            'data' => $equipmentCategory,
            'message' => 'Equipment category updated successfully.',
        ]);
    }

    public function destroy(EquipmentCategory $equipmentCategory): JsonResponse
    {
        if ($equipmentCategory->equipment()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that has associated equipment.',
            ], 422);
        }

        $equipmentCategory->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Equipment category deleted successfully.',
        ]);
    }
}
