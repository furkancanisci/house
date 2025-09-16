<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Services\PropertyCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PropertyCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PropertyCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new PropertyCacheService();
        Cache::flush(); // Clear cache before each test
    }

    public function test_can_cache_and_retrieve_property()
    {
        // Create a test property
        $property = Property::factory()->create([
            'title' => 'Test Property',
            'status' => 'active',
            'is_available' => true
        ]);

        // First call should fetch from database
        $cachedProperty = $this->cacheService->getProperty($property->id);
        $this->assertNotNull($cachedProperty);
        $this->assertEquals($property->id, $cachedProperty->id);
        $this->assertEquals('Test Property', $cachedProperty->title);

        // Second call should return cached version
        $cachedProperty2 = $this->cacheService->getProperty($property->id);
        $this->assertEquals($cachedProperty->id, $cachedProperty2->id);
    }

    public function test_can_cache_search_results()
    {
        // Create test properties
        Property::factory()->count(3)->create([
            'status' => 'active',
            'property_type' => 'apartment',
            'city' => 'Damascus'
        ]);

        $filters = [
            'property_type' => 'apartment',
            'city' => 'Damascus'
        ];

        // First call should fetch from database
        $results = $this->cacheService->getSearchResults($filters);
        $this->assertIsArray($results);
        $this->assertArrayHasKey('data', $results);
        $this->assertCount(3, $results['data']);

        // Second call should return cached version
        $cachedResults = $this->cacheService->getSearchResults($filters);
        $this->assertEquals($results, $cachedResults);
    }

    public function test_can_get_featured_properties()
    {
        // Create featured properties
        Property::factory()->count(2)->create([
            'status' => 'active',
            'is_featured' => true
        ]);

        // Create non-featured properties
        Property::factory()->count(3)->create([
            'status' => 'active',
            'is_featured' => false
        ]);

        $featuredProperties = $this->cacheService->getFeaturedProperties(5);
        $this->assertIsArray($featuredProperties);
        $this->assertCount(2, $featuredProperties);

        // Verify all returned properties are featured
        foreach ($featuredProperties as $property) {
            $this->assertTrue($property['is_featured']);
        }
    }

    public function test_can_get_recent_properties()
    {
        // Create properties with different creation dates
        $oldProperty = Property::factory()->create([
            'status' => 'active',
            'created_at' => now()->subDays(5)
        ]);

        $recentProperty = Property::factory()->create([
            'status' => 'active',
            'created_at' => now()->subHour()
        ]);

        $recentProperties = $this->cacheService->getRecentProperties(5);
        $this->assertIsArray($recentProperties);
        $this->assertCount(2, $recentProperties);

        // Verify properties are ordered by creation date (newest first)
        $this->assertEquals($recentProperty->id, $recentProperties[0]['id']);
        $this->assertEquals($oldProperty->id, $recentProperties[1]['id']);
    }

    public function test_can_clear_property_cache()
    {
        $property = Property::factory()->create(['status' => 'active']);

        // Cache the property
        $this->cacheService->getProperty($property->id);
        $this->assertTrue(Cache::has("property:{$property->id}"));

        // Clear the cache
        $this->cacheService->clearPropertyCache($property->id);
        $this->assertFalse(Cache::has("property:{$property->id}"));
    }

    public function test_search_results_respect_filters()
    {
        // Create properties with different attributes
        Property::factory()->create([
            'status' => 'active',
            'property_type' => 'apartment',
            'city' => 'Damascus',
            'price' => 1000
        ]);

        Property::factory()->create([
            'status' => 'active',
            'property_type' => 'house',
            'city' => 'Aleppo',
            'price' => 2000
        ]);

        // Test city filter
        $damascusResults = $this->cacheService->getSearchResults(['city' => 'Damascus']);
        $this->assertCount(1, $damascusResults['data']);
        $this->assertEquals('Damascus', $damascusResults['data'][0]['city']);

        // Test property type filter
        $houseResults = $this->cacheService->getSearchResults(['property_type' => 'house']);
        $this->assertCount(1, $houseResults['data']);
        $this->assertEquals('house', $houseResults['data'][0]['property_type']);

        // Test price range filter
        $priceResults = $this->cacheService->getSearchResults([
            'min_price' => 1500,
            'max_price' => 2500
        ]);
        $this->assertCount(1, $priceResults['data']);
        $this->assertEquals(2000, $priceResults['data'][0]['price']);
    }

    public function test_warm_up_cache_populates_common_data()
    {
        // Create test data
        Property::factory()->count(2)->create([
            'status' => 'active',
            'is_featured' => true
        ]);

        Property::factory()->count(3)->create([
            'status' => 'active',
            'property_type' => 'apartment'
        ]);

        // Warm up cache
        $this->cacheService->warmUpCache();

        // Verify cache keys exist
        $this->assertTrue(Cache::has('featured_properties:10'));
        $this->assertTrue(Cache::has('recent_properties:10'));
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}