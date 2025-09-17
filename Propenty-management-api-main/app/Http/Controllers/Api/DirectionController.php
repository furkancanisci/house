<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DirectionController extends Controller
{
    /**
     * Display a listing of active directions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Direction::active()->ordered();

        // Optional search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $directions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $directions->map(function ($direction) {
                return [
                    'id' => $direction->id,
                    'name_en' => $direction->name_en,
                    'name_ar' => $direction->name_ar,
                    'name' => $direction->localized_name,
                    'value' => $direction->value,
                    'description_en' => $direction->description_en,
                    'description_ar' => $direction->description_ar,
                    'description' => $direction->localized_description,
                    'sort_order' => $direction->sort_order,
                ];
            }),
            'message' => 'Directions retrieved successfully.'
        ]);
    }

    /**
     * Get directions as options for dropdowns.
     */
    public function options(Request $request): JsonResponse
    {
        $options = Direction::getOptions();

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'Direction options retrieved successfully.'
        ]);
    }

    /**
     * Display the specified direction.
     */
    public function show(Direction $direction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $direction->id,
                'name_en' => $direction->name_en,
                'name_ar' => $direction->name_ar,
                'name' => $direction->localized_name,
                'value' => $direction->value,
                'description_en' => $direction->description_en,
                'description_ar' => $direction->description_ar,
                'description' => $direction->localized_description,
                'is_active' => $direction->is_active,
                'sort_order' => $direction->sort_order,
                'properties_count' => $direction->properties()->count(),
                'created_at' => $direction->created_at,
                'updated_at' => $direction->updated_at,
            ],
            'message' => 'Direction retrieved successfully.'
        ]);
    }

    /**
     * Get directions with property counts.
     */
    public function withCounts(): JsonResponse
    {
        $directions = Direction::active()
            ->ordered()
            ->withCount('properties')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $directions->map(function ($direction) {
                return [
                    'id' => $direction->id,
                    'name_en' => $direction->name_en,
                    'name_ar' => $direction->name_ar,
                    'name' => $direction->localized_name,
                    'value' => $direction->value,
                    'properties_count' => $direction->properties_count,
                    'sort_order' => $direction->sort_order,
                ];
            }),
            'message' => 'Directions with counts retrieved successfully.'
        ]);
    }
}