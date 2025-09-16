<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyStatistic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PropertyStatisticsController extends Controller
{
    /**
     * Track a property view.
     */
    public function trackView(Request $request, string $propertyId): JsonResponse
    {
        try {
            $property = Property::findOrFail($propertyId);
            $statistics = PropertyStatistic::getOrCreateForProperty($propertyId);
            $statistics->incrementViews();

            return response()->json([
                'success' => true,
                'message' => 'View tracked successfully',
                'data' => [
                    'views_count' => $statistics->views_count,
                    'last_viewed_at' => $statistics->last_viewed_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track view',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track a property inquiry.
     */
    public function trackInquiry(Request $request, string $propertyId): JsonResponse
    {
        try {
            $property = Property::findOrFail($propertyId);
            $statistics = PropertyStatistic::getOrCreateForProperty($propertyId);
            $statistics->incrementInquiries();

            return response()->json([
                'success' => true,
                'message' => 'Inquiry tracked successfully',
                'data' => [
                    'inquiries_count' => $statistics->inquiries_count,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track inquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track adding property to favorites.
     */
    public function trackFavoriteAdd(Request $request, string $propertyId): JsonResponse
    {
        try {
            $property = Property::findOrFail($propertyId);
            $statistics = PropertyStatistic::getOrCreateForProperty($propertyId);
            $statistics->incrementFavorites();

            return response()->json([
                'success' => true,
                'message' => 'Favorite tracked successfully',
                'data' => [
                    'favorites_count' => $statistics->favorites_count,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track removing property from favorites.
     */
    public function trackFavoriteRemove(Request $request, string $propertyId): JsonResponse
    {
        try {
            $property = Property::findOrFail($propertyId);
            $statistics = PropertyStatistic::getOrCreateForProperty($propertyId);
            $statistics->decrementFavorites();

            return response()->json([
                'success' => true,
                'message' => 'Favorite removal tracked successfully',
                'data' => [
                    'favorites_count' => $statistics->favorites_count,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track favorite removal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for a specific property.
     */
    public function getPropertyStatistics(string $propertyId): JsonResponse
    {
        try {
            $property = Property::with('statistics')->findOrFail($propertyId);
            $statistics = $property->statistics ?? new PropertyStatistic([
                'property_id' => $propertyId,
                'views_count' => 0,
                'inquiries_count' => 0,
                'favorites_count' => 0,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'property_id' => $propertyId,
                    'views_count' => $statistics->views_count,
                    'inquiries_count' => $statistics->inquiries_count,
                    'favorites_count' => $statistics->favorites_count,
                    'engagement_score' => $statistics->engagement_score,
                    'last_viewed_at' => $statistics->last_viewed_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get property statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics for property owner.
     */
    public function getDashboardStatistics(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get user's properties with statistics
            $properties = Property::where('user_id', $userId)
                ->with('statistics')
                ->get();

            $totalViews = 0;
            $totalInquiries = 0;
            $totalFavorites = 0;
            $propertiesWithStats = [];

            foreach ($properties as $property) {
                $stats = $property->statistics;
                if ($stats) {
                    $totalViews += $stats->views_count;
                    $totalInquiries += $stats->inquiries_count;
                    $totalFavorites += $stats->favorites_count;
                }

                $propertiesWithStats[] = [
                    'id' => $property->id,
                    'title' => $property->title,
                    'views_count' => $stats ? $stats->views_count : 0,
                    'inquiries_count' => $stats ? $stats->inquiries_count : 0,
                    'favorites_count' => $stats ? $stats->favorites_count : 0,
                    'engagement_score' => $stats ? $stats->engagement_score : 0,
                    'last_viewed_at' => $stats ? $stats->last_viewed_at : null,
                ];
            }

            // Sort by engagement score
            usort($propertiesWithStats, function ($a, $b) {
                return $b['engagement_score'] <=> $a['engagement_score'];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_properties' => $properties->count(),
                        'total_views' => $totalViews,
                        'total_inquiries' => $totalInquiries,
                        'total_favorites' => $totalFavorites,
                        'average_views_per_property' => $properties->count() > 0 ? round($totalViews / $properties->count(), 2) : 0,
                    ],
                    'properties' => $propertiesWithStats,
                    'top_performing' => array_slice($propertiesWithStats, 0, 5),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular properties based on statistics.
     */
    public function getPopularProperties(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $type = $request->get('type', 'views'); // views, inquiries, favorites, engagement

            $query = PropertyStatistic::with('property');

            switch ($type) {
                case 'inquiries':
                    $query->mostInquired($limit);
                    break;
                case 'favorites':
                    $query->mostFavorited($limit);
                    break;
                case 'engagement':
                    $query->orderBy(DB::raw('views_count + (inquiries_count * 5) + (favorites_count * 3)'), 'desc')
                          ->limit($limit);
                    break;
                default:
                    $query->mostViewed($limit);
            }

            $statistics = $query->get();

            $popularProperties = $statistics->map(function ($stat) {
                return [
                    'property' => [
                        'id' => $stat->property->id,
                        'title' => $stat->property->title,
                        'price' => $stat->property->price,
                        'location' => $stat->property->full_address,
                        'main_image_url' => $stat->property->main_image_url,
                    ],
                    'statistics' => [
                        'views_count' => $stat->views_count,
                        'inquiries_count' => $stat->inquiries_count,
                        'favorites_count' => $stat->favorites_count,
                        'engagement_score' => $stat->engagement_score,
                        'last_viewed_at' => $stat->last_viewed_at,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $popularProperties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get popular properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}