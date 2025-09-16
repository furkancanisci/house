<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuildingType;
use App\Models\WindowType;
use App\Models\FloorType;
use Illuminate\Http\JsonResponse;

class PropertyDetailController extends Controller
{
    /**
     * Get all building types.
     */
    public function getBuildingTypes(): JsonResponse
    {
        $buildingTypes = BuildingType::active()->ordered()->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'value' => $type->value,
                'name_en' => $type->name_en,
                'name_ar' => $type->name_ar,
                'description_en' => $type->description_en,
                'description_ar' => $type->description_ar,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $buildingTypes
        ]);
    }

    /**
     * Get all window types.
     */
    public function getWindowTypes(): JsonResponse
    {
        $windowTypes = WindowType::active()->ordered()->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'value' => $type->value,
                'name_en' => $type->name_en,
                'name_ar' => $type->name_ar,
                'description_en' => $type->description_en,
                'description_ar' => $type->description_ar,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $windowTypes
        ]);
    }

    /**
     * Get all floor types.
     */
    public function getFloorTypes(): JsonResponse
    {
        $floorTypes = FloorType::active()->ordered()->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'value' => $type->value,
                'name_en' => $type->name_en,
                'name_ar' => $type->name_ar,
                'description_en' => $type->description_en,
                'description_ar' => $type->description_ar,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $floorTypes
        ]);
    }

    /**
     * Get all property detail types in one request.
     */
    public function getAllTypes(): JsonResponse
    {
        $buildingTypes = BuildingType::active()->ordered()->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'value' => $type->value,
                'name_en' => $type->name_en,
                'name_ar' => $type->name_ar,
                'description_en' => $type->description_en,
                'description_ar' => $type->description_ar,
            ];
        });

        $windowTypes = WindowType::active()->ordered()->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'value' => $type->value,
                'name_en' => $type->name_en,
                'name_ar' => $type->name_ar,
                'description_en' => $type->description_en,
                'description_ar' => $type->description_ar,
            ];
        });

        $floorTypes = FloorType::active()->ordered()->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'value' => $type->value,
                'name_en' => $type->name_en,
                'name_ar' => $type->name_ar,
                'description_en' => $type->description_en,
                'description_ar' => $type->description_ar,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'building_types' => $buildingTypes,
                'window_types' => $windowTypes,
                'floor_types' => $floorTypes,
            ]
        ]);
    }
}