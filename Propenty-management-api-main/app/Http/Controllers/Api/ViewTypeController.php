<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ViewType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ViewTypeController extends Controller
{
    /**
     * Display a listing of active view types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ViewType::active()->ordered();

        // Optional search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $viewTypes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $viewTypes->map(function ($type) {
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
            'message' => 'View types retrieved successfully.'
        ]);
    }

    /**
     * Get view types as options for dropdowns.
     */
    public function options(Request $request): JsonResponse
    {
        $options = ViewType::getOptions();

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'View type options retrieved successfully.'
        ]);
    }

    /**
     * Display the specified view type.
     */
    public function show(ViewType $viewType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $viewType->id,
                'name_en' => $viewType->name_en,
                'name_ar' => $viewType->name_ar,
                'name' => $viewType->localized_name,
                'value' => $viewType->value,
                'description_en' => $viewType->description_en,
                'description_ar' => $viewType->description_ar,
                'description' => $viewType->localized_description,
                'is_active' => $viewType->is_active,
                'sort_order' => $viewType->sort_order,
                'properties_count' => $viewType->properties()->count(),
                'created_at' => $viewType->created_at,
                'updated_at' => $viewType->updated_at,
            ],
            'message' => 'View type retrieved successfully.'
        ]);
    }

    /**
     * Get view types with property counts.
     */
    public function withCounts(): JsonResponse
    {
        $viewTypes = ViewType::active()
            ->ordered()
            ->withCount('properties')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $viewTypes->map(function ($type) {
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
            'message' => 'View types with counts retrieved successfully.'
        ]);
    }
}