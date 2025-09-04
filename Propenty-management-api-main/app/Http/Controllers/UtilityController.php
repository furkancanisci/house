<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UtilityController extends Controller
{
    /**
     * Display a listing of utilities.
     */
    public function index(Request $request): JsonResponse
    {
        $language = $request->get('lang', 'ar');
        $category = $request->get('category');
        $active = $request->get('active', true);
        $grouped = $request->get('grouped', false);

        $query = Utility::query();

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

        $utilities = $query->get();

        // Return grouped by category if requested
        if ($grouped) {
            $groupedUtilities = $utilities->groupBy('category')
                ->map(function ($categoryUtilities) use ($language) {
                    return $categoryUtilities->map(function ($utility) use ($language) {
                        return $utility->toLocalizedArray($language);
                    });
                });

            return response()->json([
                'success' => true,
                'data' => $groupedUtilities,
                'categories' => Utility::CATEGORIES,
            ]);
        }

        // Return flat list
        $utilitiesData = $utilities->map(function ($utility) use ($language) {
            return $utility->toLocalizedArray($language);
        });

        return response()->json([
            'success' => true,
            'data' => $utilitiesData,
            'total' => $utilitiesData->count(),
        ]);
    }

    /**
     * Store a newly created utility.
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
            'slug' => 'nullable|string|max:255|unique:utilities,slug',
            'icon' => 'nullable|string|max:255',
            'category' => ['required', 'string', Rule::in(array_keys(Utility::CATEGORIES))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $utility = Utility::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Utility created successfully',
            'data' => $utility->toLocalizedArray($request->get('lang', 'ar')),
        ], 201);
    }

    /**
     * Display the specified utility.
     */
    public function show(Request $request, Utility $utility): JsonResponse
    {
        $language = $request->get('lang', 'ar');

        return response()->json([
            'success' => true,
            'data' => $utility->toLocalizedArray($language),
        ]);
    }

    /**
     * Update the specified utility.
     */
    public function update(Request $request, Utility $utility): JsonResponse
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:utilities,slug,' . $utility->id,
            'icon' => 'nullable|string|max:255',
            'category' => ['sometimes', 'required', 'string', Rule::in(array_keys(Utility::CATEGORIES))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $utility->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Utility updated successfully',
            'data' => $utility->fresh()->toLocalizedArray($request->get('lang', 'ar')),
        ]);
    }

    /**
     * Remove the specified utility.
     */
    public function destroy(Utility $utility): JsonResponse
    {
        // Check if utility is used by any properties
        $propertiesCount = $utility->properties()->count();
        
        if ($propertiesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete utility. It is currently used by ' . $propertiesCount . ' properties.',
            ], 422);
        }

        $utility->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utility deleted successfully',
        ]);
    }

    /**
     * Get utilities by category.
     */
    public function getByCategory(Request $request, string $category): JsonResponse
    {
        $language = $request->get('lang', 'ar');
        
        if (!array_key_exists($category, Utility::CATEGORIES)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid category',
            ], 400);
        }

        $utilities = Utility::active()
            ->category($category)
            ->orderBy('sort_order')
            ->orderBy('name_' . $language)
            ->get()
            ->map(function ($utility) use ($language) {
                return $utility->toLocalizedArray($language);
            });

        return response()->json([
            'success' => true,
            'data' => $utilities,
            'category' => $category,
            'category_label' => Utility::CATEGORIES[$category],
        ]);
    }

    /**
     * Get available categories.
     */
    public function getCategories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Utility::CATEGORIES,
        ]);
    }

    /**
     * Toggle utility active status.
     */
    public function toggleStatus(Utility $utility): JsonResponse
    {
        $utility->update([
            'is_active' => !$utility->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utility status updated successfully',
            'data' => [
                'id' => $utility->id,
                'is_active' => $utility->is_active,
            ],
        ]);
    }

    /**
     * Bulk update sort order.
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'utilities' => 'required|array',
            'utilities.*.id' => 'required|exists:utilities,id',
            'utilities.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['utilities'] as $utilityData) {
            Utility::where('id', $utilityData['id'])
                ->update(['sort_order' => $utilityData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully',
        ]);
    }
}