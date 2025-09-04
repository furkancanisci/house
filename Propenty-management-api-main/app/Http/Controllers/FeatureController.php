<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class FeatureController extends Controller
{
    /**
     * Display a listing of features.
     */
    public function index(Request $request): JsonResponse
    {
        $language = $request->get('lang', 'ar');
        $category = $request->get('category');
        $active = $request->get('active', true);
        $grouped = $request->get('grouped', false);

        $query = Feature::query();

        // Filter by active status
        if ($active) {
            $query->active();
        }

        // Filter by category
        if ($category) {
            $query->category($category);
        }

        // Order by sort_order and name
        $query->orderBy('sort_order')
              ->orderBy('name_' . $language);

        $features = $query->get();

        // Return grouped by category if requested
        if ($grouped) {
            $groupedFeatures = $features->groupBy('category')
                ->map(function ($categoryFeatures) use ($language) {
                    return $categoryFeatures->map(function ($feature) use ($language) {
                        return $feature->toLocalizedArray($language);
                    });
                });

            return response()->json([
                'success' => true,
                'data' => $groupedFeatures,
                'categories' => Feature::CATEGORIES,
            ]);
        }

        // Return flat list
        $featuresData = $features->map(function ($feature) use ($language) {
            return $feature->toLocalizedArray($language);
        });

        return response()->json([
            'success' => true,
            'data' => $featuresData,
            'total' => $featuresData->count(),
        ]);
    }

    /**
     * Store a newly created feature.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:features,slug',
            'icon' => 'nullable|string|max:255',
            'category' => ['required', 'string', Rule::in(array_keys(Feature::CATEGORIES))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $feature = Feature::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature created successfully',
            'data' => $feature->toLocalizedArray($request->get('lang', 'ar')),
        ], 201);
    }

    /**
     * Display the specified feature.
     */
    public function show(Request $request, Feature $feature): JsonResponse
    {
        $language = $request->get('lang', 'ar');

        return response()->json([
            'success' => true,
            'data' => $feature->toLocalizedArray($language),
        ]);
    }

    /**
     * Update the specified feature.
     */
    public function update(Request $request, Feature $feature): JsonResponse
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:features,slug,' . $feature->id,
            'icon' => 'nullable|string|max:255',
            'category' => ['sometimes', 'required', 'string', Rule::in(array_keys(Feature::CATEGORIES))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $feature->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature updated successfully',
            'data' => $feature->fresh()->toLocalizedArray($request->get('lang', 'ar')),
        ]);
    }

    /**
     * Remove the specified feature.
     */
    public function destroy(Feature $feature): JsonResponse
    {
        // Check if feature is used by any properties
        $propertiesCount = $feature->properties()->count();
        
        if ($propertiesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete feature. It is currently used by ' . $propertiesCount . ' properties.',
            ], 422);
        }

        $feature->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feature deleted successfully',
        ]);
    }

    /**
     * Get features by category.
     */
    public function getByCategory(Request $request, string $category): JsonResponse
    {
        $language = $request->get('lang', 'ar');
        
        if (!array_key_exists($category, Feature::CATEGORIES)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid category',
            ], 400);
        }

        $features = Feature::active()
            ->category($category)
            ->orderBy('sort_order')
            ->orderBy('name_' . $language)
            ->get()
            ->map(function ($feature) use ($language) {
                return $feature->toLocalizedArray($language);
            });

        return response()->json([
            'success' => true,
            'data' => $features,
            'category' => $category,
            'category_label' => Feature::CATEGORIES[$category],
        ]);
    }

    /**
     * Get available categories.
     */
    public function getCategories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Feature::CATEGORIES,
        ]);
    }

    /**
     * Toggle feature active status.
     */
    public function toggleStatus(Feature $feature): JsonResponse
    {
        $feature->update([
            'is_active' => !$feature->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feature status updated successfully',
            'data' => [
                'id' => $feature->id,
                'is_active' => $feature->is_active,
            ],
        ]);
    }

    /**
     * Bulk update sort order.
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'features' => 'required|array',
            'features.*.id' => 'required|exists:features,id',
            'features.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['features'] as $featureData) {
            Feature::where('id', $featureData['id'])
                ->update(['sort_order' => $featureData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully',
        ]);
    }
}