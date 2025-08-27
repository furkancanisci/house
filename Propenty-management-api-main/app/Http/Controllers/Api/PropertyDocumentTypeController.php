<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyDocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyDocumentTypeController extends Controller
{
    /**
     * Display a listing of active property document types.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $language = $request->get('lang', 'ar');
            
            $documentTypes = PropertyDocumentType::active()
                ->ordered()
                ->get()
                ->map(function ($type) use ($language) {
                    return [
                        'id' => $type->id,
                        'name' => $type->getLocalizedName($language),
                        'description' => $type->getLocalizedDescription($language),
                        'sort_order' => $type->sort_order,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $documentTypes,
                'message' => 'Property document types retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property document types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified property document type.
     */
    public function show(Request $request, PropertyDocumentType $propertyDocumentType): JsonResponse
    {
        try {
            $language = $request->get('lang', 'ar');

            $data = [
                'id' => $propertyDocumentType->id,
                'name' => $propertyDocumentType->getLocalizedName($language),
                'description' => $propertyDocumentType->getLocalizedDescription($language),
                'is_active' => $propertyDocumentType->is_active,
                'sort_order' => $propertyDocumentType->sort_order,
                'created_at' => $propertyDocumentType->created_at,
                'updated_at' => $propertyDocumentType->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Property document type retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property document type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all property document types in all languages for admin purposes.
     */
    public function getAllLanguages(): JsonResponse
    {
        try {
            $documentTypes = PropertyDocumentType::active()
                ->ordered()
                ->get()
                ->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name_ar' => $type->name_ar,
                        'name_en' => $type->name_en,
                        'name_ku' => $type->name_ku,
                        'description_ar' => $type->description_ar,
                        'description_en' => $type->description_en,
                        'description_ku' => $type->description_ku,
                        'is_active' => $type->is_active,
                        'sort_order' => $type->sort_order,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $documentTypes,
                'message' => 'Property document types (all languages) retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property document types',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}