<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FloorType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FloorTypeController extends Controller
{
    /**
     * Display a listing of active floor types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FloorType::active()->ordered();

        // Optional search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $floorTypes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $floorTypes->map(function ($type) {
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
            'message' => 'Floor types retrieved successfully.'
        ]);
    }

    /**
     * Get floor types as options for dropdowns.
     */
    public function options(Request $request): JsonResponse
    {
        $options = FloorType::getOptions();

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'Floor type options retrieved successfully.'
        ]);
    }

    /**
     * Display the specified floor type.
     */
    public function show(FloorType $floorType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $floorType->id,
                'name_en' => $floorType->name_en,
                'name_ar' => $floorType->name_ar,
                'name' => $floorType->localized_name,
                'value' => $floorType->value,
                'description_en' => $floorType->description_en,
                'description_ar' => $floorType->description_ar,
                'description' => $floorType->localized_description,
                'is_active' => $floorType->is_active,
                'sort_order' => $floorType->sort_order,
                'properties_count' => $floorType->properties()->count(),
                'created_at' => $floorType->created_at,
                'updated_at' => $floorType->updated_at,
            ],
            'message' => 'Floor type retrieved successfully.'
        ]);
    }

    /**
     * Get floor types with property counts.
     */
    public function withCounts(): JsonResponse
    {
        $floorTypes = FloorType::active()
            ->ordered()
            ->withCount('properties')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $floorTypes->map(function ($type) {
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
            'message' => 'Floor types with counts retrieved successfully.'
        ]);
    }
}