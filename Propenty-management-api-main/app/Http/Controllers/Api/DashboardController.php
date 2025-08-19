<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyCollection;
use App\Http\Resources\UserResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Apply auth middleware to all methods except for testing
        $this->middleware('auth:sanctum')->except(['properties', 'favorites']);
    }

    // In DashboardController.php
public function statsRaw()
{
    try {
        $userId = auth('sanctum')->id() ?? 1; // Fallback to 1 for testing
        
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
                ->withCount(['views' => function ($query) {
                    $query->where('viewed_at', '>=', now()->startOfMonth());
                }])
                ->get()
                ->sum('views_count'),
        ];

        // Recent activity - latest property views
        $recentActivity = $user->properties()
            ->with('views.user')
            ->get()
            ->flatMap(function ($property) {
                return $property->views->take(5)->map(function ($view) use ($property) {
                    return [
                        'type' => 'view',
                        'property_title' => $property->title,
                        'property_id' => $property->id,
                        'user_name' => $view->user ? $view->user->full_name : 'Anonymous',
                        'viewed_at' => $view->viewed_at,
                    ];
                });
            })
            ->sortByDesc('viewed_at')
            ->take(10)
            ->values();

        // Performance chart data (last 30 days)
        $chartData = $user->properties()
            ->with(['views' => function ($query) {
                $query->where('viewed_at', '>=', now()->subDays(30))
                    ->selectRaw('property_id, DATE(viewed_at) as date, COUNT(*) as views')
                    ->groupBy('property_id', 'date');
            }])
            ->get()
            ->flatMap(function ($property) {
                return $property->views->map(function ($view) use ($property) {
                    return [
                        'date' => $view->date,
                        'views' => $view->views,
                        'property_title' => $property->title,
                    ];
                });
            })
            ->groupBy('date')
            ->map(function ($group, $date) {
                return [
                    'date' => $date,
                    'total_views' => $group->sum('views'),
                ];
            })
            ->values();

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
        $userId = 1;
        
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
     * Get user's properties.
     */
    public function properties(Request $request): PropertyCollection
    {
        $user = $request->user();
        
        // Debug logging
        \Log::info('Dashboard properties request', [
            'user_id' => $user ? $user->id : 'null',
            'user_email' => $user ? $user->email : 'null',
            'has_token' => $request->bearerToken() ? 'yes' : 'no'
        ]);
        
        if (!$user) {
            // For testing purposes, use user ID 1 if no authenticated user
            $user = \App\Models\User::find(1);
            if (!$user) {
                return new PropertyCollection(Property::where('id', -1)->paginate(0));
            }
        }

        $query = $user->properties()->with(['media']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

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
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $properties = $query->paginate($request->get('per_page', 10));
        
        // Debug logging
        \Log::info('User properties query result', [
            'user_id' => $user->id,
            'properties_count' => $properties->count(),
            'total_properties' => $properties->total()
        ]);

        return new PropertyCollection($properties);
    }

    /**
     * Get user's favorite properties.
     */
    public function favorites(Request $request): PropertyCollection
    {
        $query = $request->user()
            ->favoriteProperties()
            ->with(['user', 'media'])
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
        $query->orderBy($sortBy, $sortOrder);

        $properties = $query->paginate($request->get('per_page', 10));

        return new PropertyCollection($properties);
    }

    /**
     * Get property analytics summary.
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $properties = $user->properties;

        // Overall analytics
        $analytics = [
            'total_properties' => $properties->count(),
            'total_views' => $properties->sum('views_count'),
            'total_favorites' => $user->favoriteProperties()->count(),
            'average_price' => $properties->avg('price'),
            'properties_by_type' => $properties->groupBy('property_type')
                ->map(function ($group, $type) {
                    return [
                        'type' => $type,
                        'count' => $group->count(),
                        'total_views' => $group->sum('views_count'),
                    ];
                })
                ->values(),
            'properties_by_status' => $properties->groupBy('status')
                ->map(function ($group, $status) {
                    return [
                        'status' => $status,
                        'count' => $group->count(),
                    ];
                })
                ->values(),
            'top_performing_properties' => $properties
                ->sortByDesc('views_count')
                ->take(5)
                ->map(function ($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'views_count' => $property->views_count,
                        'favorites_count' => $property->favoritedByUsers()->count(),
                    ];
                })
                ->values(),
        ];

        // Monthly views trend (last 12 months)
        $monthlyViews = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $views = $properties->sum(function ($property) use ($date) {
                return $property->views()
                    ->whereYear('viewed_at', $date->year)
                    ->whereMonth('viewed_at', $date->month)
                    ->count();
            });
            
            $monthlyViews[] = [
                'month' => $date->format('M Y'),
                'views' => $views,
            ];
        }

        $analytics['monthly_views'] = $monthlyViews;

        return response()->json([
            'analytics' => $analytics,
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'bio' => 'sometimes|nullable|string|max:1000',
            'avatar' => 'sometimes|nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();
        $user->update($request->only([
            'first_name',
            'last_name',
            'phone',
            'date_of_birth',
            'gender',
            'bio',
        ]));

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatar');
            $user->addMediaFromRequest('avatar')
                ->toMediaCollection('avatar');
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Get user notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        
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
