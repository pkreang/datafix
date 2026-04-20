<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EquipmentLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentLocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EquipmentLocation::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('building', 'like', "%{$search}%");
            });
        }

        $locations = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
            'message' => __('api.equipment_locations_retrieved'),
        ]);
    }

    public function show(EquipmentLocation $equipmentLocation): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $equipmentLocation,
            'message' => __('api.equipment_location_retrieved'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:equipment_locations,code',
            'building' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $location = EquipmentLocation::create($validated);

        return response()->json([
            'success' => true,
            'data' => $location,
            'message' => __('api.equipment_location_created'),
        ], 201);
    }

    public function update(Request $request, EquipmentLocation $equipmentLocation): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('equipment_locations', 'code')->ignore($equipmentLocation->id),
            ],
            'building' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $equipmentLocation->update($validated);

        return response()->json([
            'success' => true,
            'data' => $equipmentLocation,
            'message' => __('api.equipment_location_updated'),
        ]);
    }

    public function destroy(EquipmentLocation $equipmentLocation): JsonResponse
    {
        if ($equipmentLocation->equipment()->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('api.equipment_location_has_equipment'),
            ], 422);
        }

        $equipmentLocation->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => __('api.equipment_location_deleted'),
        ]);
    }
}
