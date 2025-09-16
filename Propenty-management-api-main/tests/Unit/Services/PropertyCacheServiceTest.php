<?php

namespace Tests\Unit\Services;

use App\Services\PropertyCacheService;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class PropertyCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PropertyCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new PropertyCacheService();
    }

    public function test_can_cache_property()
    {
        // Arrange
        $property = Property::factory()->create();
        Cache::shouldReceive('put')
            ->once()
            ->with("property:{$property->id}", $property, 3600)
            ->andReturn(true);

        Log::shouldReceive('info')
            ->once()
            ->with('Property cached', ['property_id' => $property->id]);

        // Act
        $this->cacheService->cacheProperty($property);

        // Assert - expectations are verified by Mockery
        $this->assertTrue(true);
    }

    public function test_can_get_cached_property()
    {
        // Arrange
        $property = Property::factory()->create();
        Cache::shouldReceive('get')
            ->once()
            ->with("property:{$property->id}")
            ->andReturn($property);

        // Act
        $result = $this->cacheService->getCachedProperty($property->id);

        // Assert
        $this->assertEquals($property, $result);
    }

    public function test_returns_null_when_property_not_cached()
    {
        // Arrange
        Cache::shouldReceive('remember')
            ->once()
            ->with('property:999', 3600, Mockery::type('Closure'))
            ->andReturn(null);

        // Act
        $result = $this->cacheService->getProperty(999);

        // Assert
        $this->assertNull($result);
    }

    public function test_can_invalidate_property_cache()
    {
        // Arrange
        $propertyId = 1;
        Cache::shouldReceive('forget')
            ->once()
            ->with("property:{$propertyId}")
            ->andReturn(true);
            
        Cache::shouldReceive('forget')
            ->once()
            ->with("stats:{$propertyId}")
            ->andReturn(true);

        Log::shouldReceive('info')
            ->once()
            ->with("Cache invalidated for property: {$propertyId}");

        // Act
        $this->cacheService->invalidateProperty($propertyId);

        // Assert - expectations are verified by Mockery
        $this->assertTrue(true);
    }

    public function test_can_cache_search_results()
    {
        // Arrange
        $searchKey = 'search:test_key';
        $results = collect(['property1', 'property2']);
        
        Cache::shouldReceive('put')
            ->once()
            ->with($searchKey, $results, 1800)
            ->andReturn(true);

        Log::shouldReceive('info')
            ->once()
            ->with('Search results cached', ['search_key' => $searchKey]);

        // Act
        $this->cacheService->cacheSearchResults($searchKey, $results);

        // Assert - expectations are verified by Mockery
        $this->assertTrue(true);
    }

    public function test_can_get_cached_search_results()
    {
        // Arrange
        $searchKey = 'test_search';
        $expectedResults = ['property1', 'property2'];
        
        Cache::shouldReceive('get')
            ->once()
            ->with('search:' . md5($searchKey))
            ->andReturn($expectedResults);

        // Act
        $result = $this->cacheService->getCachedSearchResults($searchKey);

        // Assert
        $this->assertEquals($expectedResults, $result);
    }

    public function test_can_invalidate_search_cache()
    {
        // Arrange
        $mockRedis = Mockery::mock();
        $mockRedis->shouldReceive('keys')
            ->once()
            ->with('search:*')
            ->andReturn(['search:key1', 'search:key2']);
        $mockRedis->shouldReceive('del')
            ->once()
            ->with(['search:key1', 'search:key2']);

        $mockStore = Mockery::mock('\Illuminate\Cache\RedisStore');
        $mockStore->shouldReceive('getRedis')
            ->once()
            ->andReturn($mockRedis);

        Cache::shouldReceive('getStore')
            ->twice()
            ->andReturn($mockStore);

        Log::shouldReceive('info')
            ->once()
            ->with('Search cache invalidated: 2 keys deleted');

        // Act
        $this->cacheService->invalidateSearchCache();
    }

    public function test_generates_correct_search_key()
    {
        // Arrange
        $filters = [
            'city' => 'Istanbul',
            'property_type' => 'apartment',
            'min_price' => 100000
        ];
        $expectedKey = '{"city":"Istanbul","min_price":100000,"property_type":"apartment"}';

        // Act
        $result = $this->cacheService->generateSearchKey($filters);

        // Assert
        $this->assertEquals($expectedKey, $result);
    }

    public function test_generates_consistent_search_key_for_same_filters()
    {
        // Arrange
        $filters1 = ['city' => 'Istanbul', 'min_price' => 100000];
        $filters2 = ['min_price' => 100000, 'city' => 'Istanbul']; // Different order

        // Act
        $key1 = $this->cacheService->generateSearchKey($filters1);
        $key2 = $this->cacheService->generateSearchKey($filters2);

        // Assert - Both should be the same because filters are sorted
        $this->assertEquals($key1, $key2);
        $this->assertEquals('{"city":"Istanbul","min_price":100000}', $key1);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}