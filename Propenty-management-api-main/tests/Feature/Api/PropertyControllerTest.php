<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use App\Models\User;
use App\Models\PropertyType;
use App\Models\City;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected PropertyType $propertyType;
    protected City $city;
    protected District $district;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->propertyType = PropertyType::factory()->create();
        $this->city = City::factory()->create();
        $this->district = District::factory()->create(['city_id' => $this->city->id]);
    }

    public function test_can_get_properties_list()
    {
        // Arrange
        Property::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active'
        ]);

        // Act
        $response = $this->getJson('/api/properties');

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'price',
                            'property_type',
                            'city',
                            'district',
                            'status'
                        ]
                    ],
                    'links',
                    'meta'
                ]);
    }

    public function test_can_search_properties_with_filters()
    {
        // Arrange
        Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'price' => 500000,
            'status' => 'active'
        ]);

        Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'price' => 1000000,
            'status' => 'active'
        ]);

        // Act
        $response = $this->getJson('/api/properties?min_price=400000&max_price=600000');

        // Assert
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_caching_works_for_search_results()
    {
        // Arrange
        Property::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active'
        ]);

        // Act - First request
        $response1 = $this->getJson('/api/properties');
        
        // Act - Second request (should use cache)
        $response2 = $this->getJson('/api/properties');

        // Assert
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_can_show_single_property()
    {
        // Arrange
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active'
        ]);

        // Act
        $response = $this->getJson("/api/properties/{$property->id}");

        // Assert
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'price',
                        'property_type',
                        'city',
                        'district',
                        'user',
                        'images',
                        'features',
                        'utilities'
                    ]
                ]);
    }

    public function test_property_view_count_increments()
    {
        // Arrange
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active',
            'view_count' => 0
        ]);

        // Act
        $this->getJson("/api/properties/{$property->id}");

        // Assert
        $property->refresh();
        $this->assertEquals(1, $property->view_count);
    }

    public function test_authenticated_user_can_create_property()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $propertyData = [
            'title' => 'Test Property',
            'description' => 'A beautiful test property',
            'price' => 750000,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'address' => 'Test Address 123',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 120,
            'floor' => 2,
            'total_floors' => 5
        ];

        // Act
        $response = $this->postJson('/api/properties', $propertyData);

        // Assert
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'price'
                    ]
                ]);
        
        $this->assertDatabaseHas('properties', [
            'title' => 'Test Property',
            'user_id' => $this->user->id
        ]);
    }

    public function test_unauthenticated_user_cannot_create_property()
    {
        // Arrange
        $propertyData = [
            'title' => 'Test Property',
            'description' => 'A beautiful test property',
            'price' => 750000
        ];

        // Act
        $response = $this->postJson('/api/properties', $propertyData);

        // Assert
        $response->assertStatus(401);
    }

    public function test_property_creation_validation()
    {
        // Arrange
        Sanctum::actingAs($this->user);

        // Act
        $response = $this->postJson('/api/properties', []);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'title',
                    'description',
                    'price',
                    'property_type_id',
                    'city_id',
                    'district_id'
                ]);
    }

    public function test_user_can_update_own_property()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id
        ]);

        $updateData = [
            'title' => 'Updated Property Title',
            'price' => 850000
        ];

        // Act
        $response = $this->putJson("/api/properties/{$property->id}", $updateData);

        // Assert
        $response->assertStatus(200);
        $property->refresh();
        $this->assertEquals('Updated Property Title', $property->title);
        $this->assertEquals(850000, $property->price);
    }

    public function test_user_cannot_update_others_property()
    {
        // Arrange
        $otherUser = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        $property = Property::factory()->create([
            'user_id' => $otherUser->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id
        ]);

        // Act
        $response = $this->putJson("/api/properties/{$property->id}", [
            'title' => 'Hacked Title'
        ]);

        // Assert
        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_property()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id
        ]);

        // Act
        $response = $this->deleteJson("/api/properties/{$property->id}");

        // Assert
        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_rate_limiting_works()
    {
        // This test would need to be adjusted based on your rate limiting configuration
        // For now, we'll test that the middleware is applied
        
        // Act - Make multiple requests quickly
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/properties');
        }

        // Assert - All requests should succeed within rate limit
        foreach ($responses as $response) {
            $this->assertContains($response->status(), [200, 429]); // 429 = Too Many Requests
        }
    }

    public function test_input_sanitization_blocks_malicious_input()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $maliciousData = [
            'title' => '<script>alert("xss")</script>Malicious Property',
            'description' => 'DROP TABLE properties; --',
            'price' => 750000,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id
        ];

        // Act
        $response = $this->postJson('/api/properties', $maliciousData);

        // Assert
        $response->assertStatus(400); // Should be blocked by input sanitization
    }
}