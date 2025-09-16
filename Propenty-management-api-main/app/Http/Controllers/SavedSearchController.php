<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SavedSearchController extends Controller
{
    /**
     * Get all saved searches for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $savedSearches = SavedSearch::forUser($userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($search) {
                    return [
                        'id' => $search->id,
                        'name' => $search->name,
                        'search_criteria' => $search->search_criteria,
                        'formatted_criteria' => $search->formatted_criteria,
                        'notification_enabled' => $search->notification_enabled,
                        'created_at' => $search->created_at,
                        'updated_at' => $search->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $savedSearches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get saved searches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new saved search.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'search_criteria' => 'required|array',
                'notification_enabled' => 'boolean',
            ]);

            // Check if user already has a saved search with this name
            $existingSearch = SavedSearch::where('user_id', $userId)
                ->where('name', $validated['name'])
                ->first();

            if ($existingSearch) {
                return response()->json([
                    'success' => false,
                    'message' => 'A saved search with this name already exists'
                ], 422);
            }

            $savedSearch = SavedSearch::create([
                'user_id' => $userId,
                'name' => $validated['name'],
                'search_criteria' => $validated['search_criteria'],
                'notification_enabled' => $validated['notification_enabled'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Saved search created successfully',
                'data' => [
                    'id' => $savedSearch->id,
                    'name' => $savedSearch->name,
                    'search_criteria' => $savedSearch->search_criteria,
                    'formatted_criteria' => $savedSearch->formatted_criteria,
                    'notification_enabled' => $savedSearch->notification_enabled,
                    'created_at' => $savedSearch->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create saved search',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific saved search.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $savedSearch = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $savedSearch->id,
                    'name' => $savedSearch->name,
                    'search_criteria' => $savedSearch->search_criteria,
                    'formatted_criteria' => $savedSearch->formatted_criteria,
                    'notification_enabled' => $savedSearch->notification_enabled,
                    'created_at' => $savedSearch->created_at,
                    'updated_at' => $savedSearch->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Saved search not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update a saved search.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $savedSearch = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'search_criteria' => 'sometimes|required|array',
                'notification_enabled' => 'sometimes|boolean',
            ]);

            // Check if user is trying to update name and it conflicts with existing
            if (isset($validated['name']) && $validated['name'] !== $savedSearch->name) {
                $existingSearch = SavedSearch::where('user_id', $userId)
                    ->where('name', $validated['name'])
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingSearch) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A saved search with this name already exists'
                    ], 422);
                }
            }

            $savedSearch->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Saved search updated successfully',
                'data' => [
                    'id' => $savedSearch->id,
                    'name' => $savedSearch->name,
                    'search_criteria' => $savedSearch->search_criteria,
                    'formatted_criteria' => $savedSearch->formatted_criteria,
                    'notification_enabled' => $savedSearch->notification_enabled,
                    'updated_at' => $savedSearch->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update saved search',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a saved search.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $savedSearch = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $savedSearch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Saved search deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete saved search',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute a saved search and return matching properties.
     */
    public function execute(Request $request, string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $savedSearch = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            // Get pagination parameters
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 15);

            // Build query based on saved search criteria
            $query = Property::query();
            $criteria = $savedSearch->search_criteria;

            // Apply search filters based on criteria
            if (!empty($criteria['search'])) {
                $query->where(function ($q) use ($criteria) {
                    $q->where('title', 'like', '%' . $criteria['search'] . '%')
                      ->orWhere('description', 'like', '%' . $criteria['search'] . '%');
                });
            }

            if (!empty($criteria['min_price'])) {
                $query->where('price', '>=', $criteria['min_price']);
            }

            if (!empty($criteria['max_price'])) {
                $query->where('price', '<=', $criteria['max_price']);
            }

            if (!empty($criteria['property_type_id'])) {
                $query->where('property_type_id', $criteria['property_type_id']);
            }

            if (!empty($criteria['governorate_id'])) {
                $query->where('governorate_id', $criteria['governorate_id']);
            }

            if (!empty($criteria['city_id'])) {
                $query->where('city_id', $criteria['city_id']);
            }

            if (!empty($criteria['neighborhood_id'])) {
                $query->where('neighborhood_id', $criteria['neighborhood_id']);
            }

            if (!empty($criteria['bedrooms'])) {
                $query->where('bedrooms', $criteria['bedrooms']);
            }

            if (!empty($criteria['bathrooms'])) {
                $query->where('bathrooms', $criteria['bathrooms']);
            }

            if (!empty($criteria['floor_number'])) {
                $query->where('floor_number', $criteria['floor_number']);
            }

            if (!empty($criteria['orientation'])) {
                $query->where('orientation', $criteria['orientation']);
            }

            if (!empty($criteria['view_type'])) {
                $query->where('view_type', $criteria['view_type']);
            }

            // Get results with pagination
            $properties = $query->with(['user', 'governorate', 'city', 'neighborhood', 'propertyType', 'statistics'])
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'saved_search' => [
                        'id' => $savedSearch->id,
                        'name' => $savedSearch->name,
                        'formatted_criteria' => $savedSearch->formatted_criteria,
                    ],
                    'properties' => $properties->items(),
                    'pagination' => [
                        'current_page' => $properties->currentPage(),
                        'last_page' => $properties->lastPage(),
                        'per_page' => $properties->perPage(),
                        'total' => $properties->total(),
                        'from' => $properties->firstItem(),
                        'to' => $properties->lastItem(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute saved search',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of properties matching a saved search.
     */
    public function getMatchingCount(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $savedSearch = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $matchingCount = $savedSearch->getMatchingPropertiesCount();

            return response()->json([
                'success' => true,
                'data' => [
                    'saved_search_id' => $savedSearch->id,
                    'saved_search_name' => $savedSearch->name,
                    'matching_properties_count' => $matchingCount,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get matching count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle notification setting for a saved search.
     */
    public function toggleNotification(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $savedSearch = SavedSearch::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $savedSearch->notification_enabled = !$savedSearch->notification_enabled;
            $savedSearch->save();

            return response()->json([
                'success' => true,
                'message' => 'Notification setting updated successfully',
                'data' => [
                    'id' => $savedSearch->id,
                    'notification_enabled' => $savedSearch->notification_enabled,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}