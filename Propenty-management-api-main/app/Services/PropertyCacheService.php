<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PropertyCacheService
{
    const CACHE_TTL = 3600; // 1 hour
    const SEARCH_CACHE_TTL = 1800; // 30 minutes
    
    /**
     * Get cached property or fetch from database
     */
    public function getProperty(int $propertyId): ?Property
    {
        $cacheKey = "property:{$propertyId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($propertyId) {
            Log::info("Fetching property {$propertyId} from database");
            return Property::with(['media', 'user', 'priceType'])->find($propertyId);
        });
    }
    
    /**
     * Get cached search results
     */
    public function getSearchResults(array $filters): array
    {
        $cacheKey = 'property_search:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::SEARCH_CACHE_TTL, function () use ($filters) {
            Log::info('Fetching search results from database', ['filters' => $filters]);
            
            $query = Property::with(['media', 'user', 'priceType'])
                ->where('status', 'active');
            
            // Apply filters
            if (!empty($filters['city'])) {
                $query->where('city', 'like', '%' . $filters['city'] . '%');
            }
            
            if (!empty($filters['property_type'])) {
                $query->where('property_type', $filters['property_type']);
            }
            
            if (!empty($filters['min_price'])) {
                $query->where('price', '>=', $filters['min_price']);
            }
            
            if (!empty($filters['max_price'])) {
                $query->where('price', '<=', $filters['max_price']);
            }
            
            if (!empty($filters['bedrooms'])) {
                $query->where('bedrooms', $filters['bedrooms']);
            }
            
            if (!empty($filters['bathrooms'])) {
                $query->where('bathrooms', $filters['bathrooms']);
            }
            
            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 15;
            
            return $query->paginate($perPage, ['*'], 'page', $page)->toArray();
        });
    }
    
    /**
     * Get cached featured properties
     */
    public function getFeaturedProperties(int $limit = 10): array
    {
        $cacheKey = "featured_properties:{$limit}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            Log::info("Fetching {$limit} featured properties from database");
            
            return Property::with(['media', 'user', 'priceType'])
                ->where('status', 'active')
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }
    
    /**
     * Get cached recent properties
     */
    public function getRecentProperties(int $limit = 10): array
    {
        $cacheKey = "recent_properties:{$limit}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            Log::info("Fetching {$limit} recent properties from database");
            
            return Property::with(['media', 'user', 'priceType'])
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }
    
    /**
     * Clear property cache
     */
    public function clearPropertyCache(int $propertyId): void
    {
        $cacheKey = "property:{$propertyId}";
        Cache::forget($cacheKey);
        
        // Clear related caches
        $this->clearSearchCache();
        $this->clearFeaturedCache();
        $this->clearRecentCache();
        
        Log::info("Cleared cache for property {$propertyId}");
    }
    
    /**
     * Clear search cache
     */
    public function clearSearchCache(): void
    {
        // Clear all search result caches (this is a simplified approach)
        // In production, you might want to use cache tags for more efficient clearing
        Cache::flush();
        Log::info('Cleared search cache');
    }
    
    /**
     * Clear featured properties cache
     */
    public function clearFeaturedCache(): void
    {
        $pattern = 'featured_properties:*';
        $this->clearCacheByPattern($pattern);
        Log::info('Cleared featured properties cache');
    }
    
    /**
     * Clear recent properties cache
     */
    public function clearRecentCache(): void
    {
        $pattern = 'recent_properties:*';
        $this->clearCacheByPattern($pattern);
        Log::info('Cleared recent properties cache');
    }
    
    /**
     * Clear cache by pattern (Redis specific)
     */
    private function clearCacheByPattern(string $pattern): void
    {
        try {
            // Only use Redis pattern clearing if Redis is the cache driver
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // For non-Redis drivers, fall back to cache flush
                // This is less efficient but works with all cache drivers
                Cache::flush();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache by pattern', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            // Fallback to cache flush if Redis operations fail
            Cache::flush();
        }
    }
    
    /**
     * Warm up cache with popular searches
     */
    public function warmUpCache(): void
    {
        $popularSearches = [
            ['listing_type' => 'rent'],
            ['listing_type' => 'sale'],
            ['property_type' => 'apartment'],
            ['property_type' => 'house'],
            ['city' => 'Damascus'],
            ['city' => 'Aleppo']
        ];

        foreach ($popularSearches as $filters) {
            $this->getSearchResults($filters);
        }
    }

    /**
     * Generate a consistent cache key for search filters
     */
    public function generateSearchKey(array $filters): string
    {
        // Sort filters to ensure consistent key generation
        ksort($filters);
        
        // Remove empty values
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
        
        return 'search:' . md5(json_encode($filters));
    }

    /**
     * Cache a property
     */
    public function cacheProperty(Property $property): void
    {
        $cacheKey = "property:{$property->id}";
        Cache::put($cacheKey, $property, self::CACHE_TTL);
        Log::info("Cached property {$property->id}");
    }

    /**
     * Invalidate property cache (alias for clearPropertyCache)
     */
    public function invalidateProperty(int $propertyId): void
    {
        $this->clearPropertyCache($propertyId);
    }

    /**
     * Invalidate search results cache (alias for clearSearchCache)
     */
    public function invalidateSearchResults(): void
    {
        $this->clearSearchCache();
    }
}