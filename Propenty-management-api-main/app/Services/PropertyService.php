<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PropertyService
{
    /**
     * Get property statistics for analytics
     */
    public function getPropertyAnalytics(Property $property, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        // Views analytics
        $totalViews = $property->views()->where('viewed_at', '>=', $startDate)->count();
        $uniqueViews = $property->views()
            ->where('viewed_at', '>=', $startDate)
            ->distinct(['ip_address', 'user_id'])
            ->count();
        
        // Daily views chart data
        $dailyViews = $property->views()
            ->where('viewed_at', '>=', $startDate)
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Device analytics
        $deviceStats = $property->views()
            ->where('viewed_at', '>=', $startDate)
            ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(device_info, "$.platform")) as platform, COUNT(*) as count')
            ->groupBy('platform')
            ->get();

        // Browser analytics
        $browserStats = $property->views()
            ->where('viewed_at', '>=', $startDate)
            ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(device_info, "$.browser")) as browser, COUNT(*) as count')
            ->groupBy('browser')
            ->get();

        // Referrer analytics
        $referrerStats = $property->views()
            ->where('viewed_at', '>=', $startDate)
            ->selectRaw('
                CASE 
                    WHEN referrer IS NULL THEN "Direct"
                    WHEN referrer LIKE "%google%" THEN "Google"
                    WHEN referrer LIKE "%facebook%" THEN "Facebook"
                    WHEN referrer LIKE "%zillow%" THEN "Zillow"
                    WHEN referrer LIKE "%realtor%" THEN "Realtor.com"
                    ELSE "Other"
                END as source, 
                COUNT(*) as count
            ')
            ->groupBy('source')
            ->get();

        return [
            'period' => [
                'days' => $days,
                'start_date' => $startDate->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'overview' => [
                'total_views' => $totalViews,
                'unique_views' => $uniqueViews,
                'favorites_count' => $property->favoritedByUsers()->count(),
                'conversion_rate' => $totalViews > 0 ? round(($property->favoritedByUsers()->count() / $totalViews) * 100, 2) : 0,
            ],
            'chart_data' => [
                'daily_views' => $dailyViews,
                'device_breakdown' => $deviceStats,
                'browser_breakdown' => $browserStats,
                'traffic_sources' => $referrerStats,
            ],
        ];
    }

    /**
     * Record a property view with detailed tracking
     */
    public function recordPropertyView(Property $property, Request $request): PropertyView
    {
        // Check if we should record this view (avoid spam)
        $userId = auth()->id();
        $ipAddress = $request->ip();
        
        // Don't record if the same user/IP viewed within the last hour
        $recentView = PropertyView::where('property_id', $property->id)
            ->where(function ($query) use ($userId, $ipAddress) {
                $query->where('user_id', $userId)
                      ->orWhere('ip_address', $ipAddress);
            })
            ->where('viewed_at', '>', now()->subHour())
            ->exists();

        if ($recentView) {
            return PropertyView::where('property_id', $property->id)
                ->where(function ($query) use ($userId, $ipAddress) {
                    $query->where('user_id', $userId)
                          ->orWhere('ip_address', $ipAddress);
                })
                ->latest('viewed_at')
                ->first();
        }

        // Create the view record
        $view = PropertyView::create([
            'property_id' => $property->id,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'device_info' => $this->extractDeviceInfo($request),
            'viewed_at' => now(),
        ]);

        // Increment property views counter
        $property->increment('views_count');

        return $view;
    }

    /**
     * Extract device information from request
     */
    private function extractDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent();
        
        return [
            'browser' => $this->getBrowserFromUserAgent($userAgent),
            'platform' => $this->getPlatformFromUserAgent($userAgent),
            'is_mobile' => $this->isMobileDevice($request),
            'screen_resolution' => $request->header('sec-ch-viewport-width') . 'x' . $request->header('sec-ch-viewport-height'),
        ];
    }

    /**
     * Extract browser from user agent
     */
    private function getBrowserFromUserAgent(string $userAgent): string
    {
        if (str_contains($userAgent, 'Edg/')) return 'Edge';
        if (str_contains($userAgent, 'Chrome/')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox/')) return 'Firefox';
        if (str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome')) return 'Safari';
        if (str_contains($userAgent, 'Opera/')) return 'Opera';
        
        return 'Unknown';
    }

    /**
     * Extract platform from user agent
     */
    private function getPlatformFromUserAgent(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows NT')) return 'Windows';
        if (str_contains($userAgent, 'Macintosh')) return 'macOS';
        if (str_contains($userAgent, 'iPhone')) return 'iOS';
        if (str_contains($userAgent, 'iPad')) return 'iPadOS';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        
        return 'Unknown';
    }

    /**
     * Detect if request is from mobile device
     */
    private function isMobileDevice(Request $request): bool
    {
        // Check the sec-ch-ua-mobile header first (modern browsers)
        if ($request->header('sec-ch-ua-mobile') === '?1') {
            return true;
        }

        // Fallback to user agent detection
        $userAgent = strtolower($request->userAgent());
        $mobileKeywords = ['mobile', 'android', 'iphone', 'ipad', 'tablet', 'phone'];
        
        foreach ($mobileKeywords as $keyword) {
            if (str_contains($userAgent, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get cached property recommendations
     */
    public function getRecommendedProperties(User $user, int $limit = 6): array
    {
        $cacheKey = "recommended_properties_user_{$user->id}";
        
        return Cache::remember($cacheKey, now()->addHour(), function () use ($user, $limit) {
            // Get user's favorite properties to understand preferences
            $favoriteProperties = $user->favoriteProperties;
            
            if ($favoriteProperties->isEmpty()) {
                // If no favorites, return featured properties
                return Property::featured()
                    ->active()
                    ->where('user_id', '!=', $user->id)
                    ->limit($limit)
                    ->get()
                    ->toArray();
            }

            // Analyze preferences
            $preferredTypes = $favoriteProperties->pluck('property_type')->unique();
            $preferredCities = $favoriteProperties->pluck('city')->unique();
            $avgPrice = $favoriteProperties->avg('price');
            $priceRange = [$avgPrice * 0.7, $avgPrice * 1.3]; // ±30% of average

            // Build recommendation query
            $query = Property::active()
                ->where('user_id', '!=', $user->id)
                ->whereNotIn('id', $favoriteProperties->pluck('id'));

            // Apply preference filters with scoring
            $query->selectRaw('
                properties.*, 
                (
                    CASE WHEN property_type IN ("' . $preferredTypes->implode('","') . '") THEN 2 ELSE 0 END +
                    CASE WHEN city IN ("' . $preferredCities->implode('","') . '") THEN 2 ELSE 0 END +
                    CASE WHEN price BETWEEN ? AND ? THEN 1 ELSE 0 END +
                    CASE WHEN is_featured = 1 THEN 1 ELSE 0 END
                ) as recommendation_score
            ', $priceRange)
            ->orderByDesc('recommendation_score')
            ->orderByDesc('views_count')
            ->limit($limit);

            return $query->get()->toArray();
        });
    }

    /**
     * Handle property image uploads
     */
    public function handlePropertyImages(Property $property, Request $request): array
    {
        $uploadedImages = [];

        // Handle main image
        if ($request->hasFile('main_image')) {
            $property->clearMediaCollection('main_image');
            $media = $property->addMediaFromRequest('main_image')
                ->toMediaCollection('main_image');
            $uploadedImages['main_image'] = $media->getUrl();
        }

        // Handle gallery images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $media = $property->addMedia($image)
                    ->toMediaCollection('images');
                $uploadedImages['images'][] = [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                ];
            }
        }

        return $uploadedImages;
    }

    /**
     * Generate property report data
     */
    public function generatePropertyReport(Property $property): array
    {
        $analytics = $this->getPropertyAnalytics($property, 90); // 90 days
        
        return [
            'property' => [
                'id' => $property->id,
                'title' => $property->title,
                'type' => $property->property_type,
                'listing_type' => $property->listing_type,
                'price' => $property->price,
                'status' => $property->status,
                'created_at' => $property->created_at,
                'published_at' => $property->published_at,
            ],
            'performance' => [
                'total_views' => $property->views_count,
                'recent_views' => $analytics['overview']['total_views'],
                'unique_views' => $analytics['overview']['unique_views'],
                'favorites_count' => $analytics['overview']['favorites_count'],
                'conversion_rate' => $analytics['overview']['conversion_rate'],
            ],
            'audience' => [
                'top_platforms' => $analytics['chart_data']['device_breakdown'],
                'top_browsers' => $analytics['chart_data']['browser_breakdown'],
                'traffic_sources' => $analytics['chart_data']['traffic_sources'],
            ],
            'trends' => [
                'daily_views' => $analytics['chart_data']['daily_views'],
            ],
            'recommendations' => [
                'optimize_description' => strlen($property->description) < 200,
                'add_more_images' => $property->getMedia('images')->count() < 5,
                'update_price' => $analytics['overview']['total_views'] > 100 && $analytics['overview']['conversion_rate'] < 2,
            ],
        ];
    }
}
