<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WindowType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WindowTypeController extends Controller
{
    /**
     * Display a listing of active window types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = WindowType::active()->ordered();

        // Optional search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $windowTypes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $windowTypes->map(function ($type) {
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
            'message' => 'Window types retrieved successfully.'
        ]);
    }

    /**
     * Get window types as options for dropdowns.
     */
    public function options(Request $request): JsonResponse
    {
        $options = WindowType::getOptions();

        return response()->json([
            'success' => true,
            'data' => $options,
            'message' => 'Window type options retrieved successfully.'
        ]);
    }

    /**
     * Display the specified window type.
     */
    public function show(WindowType $windowType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $windowType->id,
                'name_en' => $windowType->name_en,
                'name_ar' => $windowType->name_ar,
                'name' => $windowType->localized_name,
                'value' => $windowType->value,
                'description_en' => $windowType->description_en,
                'description_ar' => $windowType->description_ar,
                'description' => $windowType->localized_description,
                'is_active' => $windowType->is_active,
                'sort_order' => $windowType->sort_order,
                'properties_count' => $windowType->properties()->count(),
                'created_at' => $windowType->created_at,
                'updated_at' => $windowType->updated_at,
            ],
            'message' => 'Window type retrieved successfully.'
        ]);
    }

    /**
     * Get window types with property counts.
     */
    public function withCounts(): JsonResponse
    {
        $windowTypes = WindowType::active()
            ->ordered()
            ->withCount('properties')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $windowTypes->map(function ($type) {
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
            'message' => 'Window types with counts retrieved successfully.'
        ]);
    }
}