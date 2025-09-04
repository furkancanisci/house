<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyCollection;
use App\Http\Resources\UserResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Apply auth middleware to all methods
        $this->middleware('auth:sanctum');
    }

    // In DashboardController.php
public function statsRaw(Request $request)
{
    try {
        $user = $request->user();
        
        // Return empty stats if no authenticated user
        if (!$user) {
            return response()->json([
                'totalProperties' => 0,
                'forRent' => 0,
                'forSale' => 0,
                'favoriteProperties' => 0,
                'myProperties' => 0
            ]);
        }
        
        $userId = $user->id;
        
        $stats = \DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM properties WHERE user_id = ?) as totalProperties,
                (SELECT COUNT(*) FROM properties WHERE user_id = ? AND listing_type = 'rent') as forRent,
                (SELECT COUNT(*) FROM properties WHERE user_id = ? AND listing_type = 'sale') as forSale,
                (SELECT COUNT(*) FROM property_favorites WHERE user_id = ?) as favoriteProperties
        ", [$userId, $userId, $userId, $userId]);

        if (!$stats) {
            throw new \Exception('Failed to fetch statistics');
        }

        // Return a plain array, not an object
        return [
            'totalProperties' => (int)$stats->totalProperties,
            'forRent' => (int)$stats->forRent,
            'forSale' => (int)$stats->forSale,
            'favoriteProperties' => (int)$stats->favoriteProperties,
            'myProperties' => (int)$stats->totalProperties
        ];

    } catch (\Exception $e) {
        \Log::error('Error in stats endpoint: ' . $e->getMessage());
        return response()->json([
            'error' => 'An error occurred while fetching statistics',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get dashboard overview data.
     */
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Return empty overview if no authenticated user
        if (!$user) {
            return response()->json([
                'user' => null,
                'stats' => [
                    'total_properties' => 0,
                    'active_properties' => 0,
                    'draft_properties' => 0,
                    'pending_properties' => 0,
                    'sold_rented_properties' => 0,
                    'total_favorites' => 0,
                    'total_views' => 0,
                    'this_month_views' => 0,
                ],
                'recent_activity' => [],
                'chart_data' => [],
            ]);
        }
        
        // Optimize stats queries to prevent memory issues
        $stats = [
            'total_properties' => $user->properties()->count(),
            'active_properties' => $user->properties()->where('status', 'active')->count(),
            'draft_properties' => $user->properties()->where('status', 'draft')->count(),
            'pending_properties' => $user->properties()->where('status', 'pending')->count(),
            'sold_rented_properties' => $user->properties()
                ->whereIn('status', ['sold', 'rented'])
                ->count(),
            'total_favorites' => $user->favoriteProperties()->count(),
            'total_views' => $user->properties()->sum('views_count'),
            'this_month_views' => $user->properties()
                ->join('property_views', 'properties.id', '=', 'property_views.property_id')
                ->where('property_views.viewed_at', '>=', now()->startOfMonth())
                ->count(),
        ];

        // Recent activity - limit to 5 most recent views to prevent memory issues
        $recentActivity = \DB::select("
            SELECT 
                'view' as type,
                p.title as property_title,
                p.id as property_id,
                COALESCE(u.first_name || ' ' || u.last_name, 'Anonymous') as user_name,
                pv.viewed_at
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            LEFT JOIN users u ON pv.user_id = u.id
            WHERE p.user_id = ?
            ORDER BY pv.viewed_at DESC
            LIMIT 5
        ", [$user->id]);

        // Performance chart data (last 7 days only to prevent memory issues)
        $chartData = \DB::select("
            SELECT 
                DATE(pv.viewed_at) as date,
                COUNT(*) as total_views
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            WHERE p.user_id = ?
            AND pv.viewed_at >= CURRENT_DATE - INTERVAL '7 days'
            GROUP BY DATE(pv.viewed_at)
            ORDER BY date ASC
        ", [$user->id]);

        return response()->json([
            'user' => new UserResource($user),
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'chart_data' => $chartData,
        ]);
    }

    /**
     * Get dashboard statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
{
    try {
        $user = $request->user();
        
        // Return empty stats if no authenticated user
        if (!$user) {
            return response()->json([
                'totalProperties' => 0,
                'forRent' => 0,
                'forSale' => 0,
                'favoriteProperties' => 0,
                'myProperties' => 0
            ]);
        }
        
        $userId = $user->id;
        
        // Use DB::selectOne to get a single row
        $stats = \DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM properties WHERE user_id = ?) as totalProperties,
                (SELECT COUNT(*) FROM properties WHERE user_id = ? AND listing_type = 'rent') as forRent,
                (SELECT COUNT(*) FROM properties WHERE user_id = ? AND listing_type = 'sale') as forSale,
                (SELECT COUNT(*) FROM property_favorites WHERE user_id = ?) as favoriteProperties
        ", [$userId, $userId, $userId, $userId]);

        return response()->json([
            'totalProperties' => (int)$stats->totalProperties,
            'forRent' => (int)$stats->forRent,
            'forSale' => (int)$stats->forSale,
            'favoriteProperties' => (int)$stats->favoriteProperties,
            'myProperties' => (int)$stats->totalProperties
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in stats endpoint: ' . $e->getMessage());
        return response()->json([
            'error' => 'An error occurred while fetching statistics',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Get user's favorite properties.
     */
    public function favorites(Request $request): PropertyCollection
    {
        $user = $request->user();
        
        // Return empty collection if no authenticated user
        if (!$user) {
            return new PropertyCollection(Property::where('id', -1)->paginate(0));
        }
        
        $query = $user->favoriteProperties()
            ->with(['user:id,first_name,last_name,email', 'media:id,model_id,model_type,collection_name,file_name,file_size,mime_type,manipulations,custom_properties,generated_conversions']) // Select only necessary columns
            ->where('status', 'active');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'property_favorites.created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort parameters
        $allowedSortColumns = ['property_favorites.created_at', 'created_at', 'updated_at', 'price', 'title'];
        $allowedSortOrders = ['asc', 'desc'];
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'property_favorites.created_at';
        }
        
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }
        
        $query->orderBy($sortBy, $sortOrder);

        // Limit the number of results to prevent memory issues
        $perPage = min($request->get('per_page', 10), 50); // Max 50 items per page

        $properties = $query->paginate($perPage);

        return new PropertyCollection($properties);
    }

    /**
     * Get property analytics summary.
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Return empty analytics if no authenticated user
        if (!$user) {
            return response()->json([
                'analytics' => [
                    'total_properties' => 0,
                    'total_views' => 0,
                    'total_favorites' => 0,
                    'average_price' => 0,
                    'properties_by_type' => [],
                    'properties_by_status' => [],
                    'top_performing_properties' => [],
                    'monthly_views' => []
                ]
            ]);
        }
        
        // Optimize analytics queries to prevent memory issues
        $totalProperties = $user->properties()->count();
        $totalViews = $user->properties()->sum('views_count');
        $totalFavorites = $user->favoriteProperties()->count();
        $averagePrice = $user->properties()->avg('price');
        
        // Properties by type
        $propertiesByType = \DB::select("
            SELECT 
                COALESCE(pt.name, 'Unknown') as type,
                COUNT(*) as count,
                SUM(p.views_count) as total_views
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            WHERE p.user_id = ?
            GROUP BY pt.name
        ", [$user->id]);
        
        // Properties by status
        $propertiesByStatus = \DB::select("
            SELECT 
                status,
                COUNT(*) as count
            FROM properties
            WHERE user_id = ?
            GROUP BY status
        ", [$user->id]);
        
        // Top performing properties (top 5 by views)
        $topPerformingProperties = \DB::select("
            SELECT 
                id,
                title,
                views_count,
                (SELECT COUNT(*) FROM property_favorites pf WHERE pf.property_id = properties.id) as favorites_count
            FROM properties
            WHERE user_id = ?
            ORDER BY views_count DESC
            LIMIT 5
        ", [$user->id]);
        
        // Monthly views trend (last 7 days only to prevent memory issues)
        $monthlyViews = \DB::select("
            SELECT 
                TO_CHAR(viewed_at, 'Mon YYYY') as month,
                COUNT(*) as views
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            WHERE p.user_id = ?
            AND pv.viewed_at >= CURRENT_DATE - INTERVAL '7 days'
            GROUP BY TO_CHAR(viewed_at, 'Mon YYYY')
            ORDER BY month ASC
        ", [$user->id]);

        $analytics = [
            'total_properties' => $totalProperties,
            'total_views' => $totalViews,
            'total_favorites' => $totalFavorites,
            'average_price' => $averagePrice,
            'properties_by_type' => $propertiesByType,
            'properties_by_status' => $propertiesByStatus,
            'top_performing_properties' => $topPerformingProperties,
            'monthly_views' => $monthlyViews,
        ];

        return response()->json([
            'analytics' => $analytics,
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            \Log::info('=== PROFILE UPDATE START ===');
            
            $user = $request->user();
            
            // Debug: Check if we have a user
            if (!$user) {
                \Log::info('No user found in updateProfile request');
                return response()->json([
                    'error' => 'Authentication required'
                ], 401);
            }
            
            \Log::info('User found: ' . $user->id);
            \Log::info('Request data: ', $request->all());
            
            // Validate the request data
            $validatedData = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            ]);
            
            \Log::info('Validated data: ', $validatedData);
            
            // Update the user using raw SQL query
            if (!empty($validatedData)) {
                $updateFields = [];
                $updateValues = [];
                
                foreach ($validatedData as $key => $value) {
                    $updateFields[] = "$key = ?";
                    $updateValues[] = $value;
                }
                
                // Add updated_at timestamp
                $updateFields[] = "updated_at = ?";
                $updateValues[] = now();
                
                $updateQuery = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $updateValues[] = $user->id;
                
                \Log::info('Update query: ' . $updateQuery);
                \Log::info('Update values: ', $updateValues);
                
                DB::statement($updateQuery, $updateValues);
                
                // Refresh the user model to get updated data
                $user = $user->fresh();
                
                \Log::info('User updated successfully');
            }

            \Log::info('Returning success response');
            \Log::info('=== PROFILE UPDATE END ===');
            
            return response()->json([
                'message' => 'Profile updated successfully.',
                'user' => new UserResource($user),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error updating profile: ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'exception' => $e,
            ]);
            
            return response()->json([
                'error' => 'Validation error',
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating profile: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'An error occurred while updating profile',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        } catch (\Error $e) {
            \Log::error('Error updating profile (Error): ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'A fatal error occurred while updating profile',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get user notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Return empty notifications if no authenticated user
        if (!$user) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }
        
        // Mock notifications - you can implement actual notification system
        $notifications = [
            [
                'id' => 1,
                'type' => 'property_view',
                'title' => 'New Property View',
                'message' => 'Your property "Modern Downtown Apartment" has been viewed by a potential buyer.',
                'created_at' => now()->subHours(2),
                'read_at' => null,
            ],
            [
                'id' => 2,
                'type' => 'property_favorite',
                'title' => 'Property Favorited',
                'message' => 'Someone added your property "Luxury Villa" to their favorites.',
                'created_at' => now()->subHours(5),
                'read_at' => null,
            ],
            [
                'id' => 3,
                'type' => 'price_alert',
                'title' => 'Price Alert',
                'message' => 'A property matching your criteria has been listed for $350,000.',
                'created_at' => now()->subDay(),
                'read_at' => now()->subHours(1),
            ],
        ];

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => collect($notifications)->where('read_at', null)->count(),
        ]);
    }
}