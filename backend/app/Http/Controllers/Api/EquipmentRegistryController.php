<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentRegistryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Equipment::with(['category', 'location', 'company']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('equipment_category_id', $categoryId);
        }

        if ($locationId = $request->input('location_id')) {
            $query->where('equipment_location_id', $locationId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }

        $perPage = $request->input('per_page', 15);
        $equipment = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipment list retrieved successfully.',
        ]);
    }

    public function show(Equipment $equipment): JsonResponse
    {
        $equipment->load(['category', 'location', 'company', 'branch']);

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipment retrieved successfully.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:equipment,code',
            'serial_number' => 'nullable|string|max:255',
            'equipment_category_id' => 'required|exists:equipment_categories,id',
            'equipment_location_id' => 'required|exists:equipment_locations,id',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive,under_maintenance,decommissioned',
            'installed_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'specifications' => 'nullable|json',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        if (isset($validated['specifications']) && is_string($validated['specifications'])) {
            $validated['specifications'] = json_decode($validated['specifications'], true);
        }

        $equipment = Equipment::create($validated);
        $equipment->load(['category', 'location', 'company', 'branch']);

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipment created successfully.',
        ], 201);
    }

    public function update(Request $request, Equipment $equipment): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('equipment', 'code')->ignore($equipment->id),
            ],
            'serial_number' => 'nullable|string|max:255',
            'equipment_category_id' => 'required|exists:equipment_categories,id',
            'equipment_location_id' => 'required|exists:equipment_locations,id',
            'company_id' => 'nullable|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive,under_maintenance,decommissioned',
            'installed_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'specifications' => 'nullable|json',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        if (isset($validated['specifications']) && is_string($validated['specifications'])) {
            $validated['specifications'] = json_decode($validated['specifications'], true);
        }

        $equipment->update($validated);
        $equipment->load(['category', 'location', 'company', 'branch']);

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipment updated successfully.',
        ]);
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        $equipment->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Equipment deleted successfully.',
        ]);
    }
}
