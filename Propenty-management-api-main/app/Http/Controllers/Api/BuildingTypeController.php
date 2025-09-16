<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuildingType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BuildingTypeController extends Controller
{
    /**
     * Display a listing of active building types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BuildingType::active()->ordered();

        // Optional search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $buildingTypes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $buildingTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name_en' => $type->name_en,
                    'name_ar' => $type->name_ar,
                    'name' => $type->localized_name,
                    'value' => $type->value,
                    'description_en' => $type->description_en,
                    'description_ar' => $type->description_ar,
                    'description' => $type->localized_description,
                    'sort_order' => $type->sort_order,
                ];
            }),
            'message' => 'Building types retrieved successfully.'
        ]);
    }

    /**
     * Get building types as options for dropdowns.
     */
    public function options(Request $request): JsonResponse
    {
        $options = BuildingType::getOptions();

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'Building type options retrieved successfully.'
        ]);
    }

    /**
     * Display the specified building type.
     */
    public function show(BuildingType $buildingType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $buildingType->id,
                'name_en' => $buildingType->name_en,
                'name_ar' => $buildingType->name_ar,
                'name' => $buildingType->localized_name,
                'value' => $buildingType->value,
                'description_en' => $buildingType->description_en,
                'description_ar' => $buildingType->description_ar,
                'description' => $buildingType->localized_description,
                'is_active' => $buildingType->is_active,
                'sort_order' => $buildingType->sort_order,
                'properties_count' => $buildingType->properties()->count(),
                'created_at' => $buildingType->created_at,
                'updated_at' => $buildingType->updated_at,
            ],
            'message' => 'Building type retrieved successfully.'
        ]);
    }

    /**
     * Get building types with property counts.
     */
    public function withCounts(): JsonResponse
    {
        $buildingTypes = BuildingType::active()
            ->ordered()
            ->withCount('properties')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $buildingTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name_en' => $type->name_en,
                    'name_ar' => $type->name_ar,
                    'name' => $type->localized_name,
                    'value' => $type->value,
                    'properties_count' => $type->properties_count,
                    'sort_order' => $type->sort_order,
                ];
            }),
            'message' => 'Building types with counts retrieved successfully.'
        ]);
    }
}