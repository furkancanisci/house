<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuildingType;
use App\Models\WindowType;
use App\Models\FloorType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdvancedDetailsController extends Controller
{
    /**
     * Get all building types for property forms.
     */
    public function getBuildingTypes(Request $request): JsonResponse
    {
        try {
            $buildingTypes = BuildingType::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get()
                ->map(function ($type) use ($request) {
                    $lang = $request->get('lang', 'en');
                    return [
                        'id' => $type->id,
                        'name' => $lang === 'ar' ? $type->name_ar : $type->name_en,
                        'name_en' => $type->name_en,
                        'name_ar' => $type->name_ar,
                        'description' => $lang === 'ar' ? $type->description_ar : $type->description_en,
                        'sort_order' => $type->sort_order,
                        'is_active' => $type->is_active,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $buildingTypes,
                'message' => 'Building types retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve building types',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all window types for property forms.
     */
    public function getWindowTypes(Request $request): JsonResponse
    {
        try {
            $windowTypes = WindowType::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get()
                ->map(function ($type) use ($request) {
                    $lang = $request->get('lang', 'en');
                    return [
                        'id' => $type->id,
                        'name' => $lang === 'ar' ? $type->name_ar : $type->name_en,
                        'name_en' => $type->name_en,
                        'name_ar' => $type->name_ar,
                        'description' => $lang === 'ar' ? $type->description_ar : $type->description_en,
                        'sort_order' => $type->sort_order,
                        'is_active' => $type->is_active,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $windowTypes,
                'message' => 'Window types retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve window types',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all floor types for property forms.
     */
    public function getFloorTypes(Request $request): JsonResponse
    {
        try {
            $floorTypes = FloorType::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get()
                ->map(function ($type) use ($request) {
                    $lang = $request->get('lang', 'en');
                    return [
                        'id' => $type->id,
                        'name' => $lang === 'ar' ? $type->name_ar : $type->name_en,
                        'name_en' => $type->name_en,
                        'name_ar' => $type->name_ar,
                        'description' => $lang === 'ar' ? $type->description_ar : $type->description_en,
                        'sort_order' => $type->sort_order,
                        'is_active' => $type->is_active,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $floorTypes,
                'message' => 'Floor types retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve floor types',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all advanced details in one request.
     */
    public function getAllAdvancedDetails(Request $request): JsonResponse
    {
        try {
            $lang = $request->get('lang', 'en');
            
            $buildingTypes = BuildingType::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get()
                ->map(function ($type) use ($lang) {
                    return [
                        'id' => $type->id,
                        'name' => $lang === 'ar' ? $type->name_ar : $type->name_en,
                        'name_en' => $type->name_en,
                        'name_ar' => $type->name_ar,
                        'description' => $lang === 'ar' ? $type->description_ar : $type->description_en,
                        'sort_order' => $type->sort_order,
                    ];
                });

            $windowTypes = WindowType::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get()
                ->map(function ($type) use ($lang) {
                    return [
                        'id' => $type->id,
                        'name' => $lang === 'ar' ? $type->name_ar : $type->name_en,
                        'name_en' => $type->name_en,
                        'name_ar' => $type->name_ar,
                        'description' => $lang === 'ar' ? $type->description_ar : $type->description_en,
                        'sort_order' => $type->sort_order,
                    ];
                });

            $floorTypes = FloorType::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name_en')
                ->get()
                ->map(function ($type) use ($lang) {
                    return [
                        'id' => $type->id,
                        'name' => $lang === 'ar' ? $type->name_ar : $type->name_en,
                        'name_en' => $type->name_en,
                        'name_ar' => $type->name_ar,
                        'description' => $lang === 'ar' ? $type->description_ar : $type->description_en,
                        'sort_order' => $type->sort_order,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'building_types' => $buildingTypes,
                    'window_types' => $windowTypes,
                    'floor_types' => $floorTypes,
                ],
                'message' => 'Advanced details retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve advanced details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get advanced detail statistics.
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = [
                'building_types' => [
                    'total' => BuildingType::count(),
                    'active' => BuildingType::where('is_active', true)->count(),
                    'inactive' => BuildingType::where('is_active', false)->count(),
                ],
                'window_types' => [
                    'total' => WindowType::count(),
                    'active' => WindowType::where('is_active', true)->count(),
                    'inactive' => WindowType::where('is_active', false)->count(),
                ],
                'floor_types' => [
                    'total' => FloorType::count(),
                    'active' => FloorType::where('is_active', true)->count(),
                    'inactive' => FloorType::where('is_active', false)->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}