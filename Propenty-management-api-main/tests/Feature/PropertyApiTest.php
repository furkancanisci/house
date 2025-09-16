<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Feature;
use App\Models\Utility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->property = Property::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_get_properties_list()
    {
        Property::factory()->count(5)->create(['status' => 'active']);
        
        $response = $this->getJson('/api/v1/properties');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'property_type',
                            'listing_type',
                            'price',
                            'street_address',
                            'city',
                            'state',
                            'bedrooms',
                            'bathrooms',
                            'square_footage',
                            'is_featured',
                            'is_available',
                            'status',
                            'views_count',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }

    public function test_can_filter_properties_by_listing_type()
    {
        Property::factory()->count(3)->create(['listing_type' => 'rent', 'status' => 'active']);
        Property::factory()->count(2)->create(['listing_type' => 'sale', 'status' => 'active']);
        
        $response = $this->getJson('/api/v1/properties?listing_type=rent');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(3, $data);
        foreach ($data as $property) {
            $this->assertEquals('rent', $property['listing_type']);
        }
    }

    public function test_can_filter_properties_by_price_range()
    {
        Property::factory()->create(['price' => 500, 'status' => 'active']);
        Property::factory()->create(['price' => 1500, 'status' => 'active']);
        Property::factory()->create(['price' => 2500, 'status' => 'active']);
        
        $response = $this->getJson('/api/v1/properties?min_price=1000&max_price=2000');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals(1500, $data[0]['price']);
    }

    public function test_can_search_properties_by_keyword()
    {
        Property::factory()->create([
            'title' => 'Beautiful Apartment in Damascus',
            'status' => 'active'
        ]);
        Property::factory()->create([
            'title' => 'Modern House in Aleppo',
            'status' => 'active'
        ]);
        
        $response = $this->getJson('/api/v1/properties?search=Beautiful');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Beautiful', $data[0]['title']);
    }

    public function test_can_get_single_property()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        $response = $this->getJson("/api/v1/properties/{$property->id}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'property_type',
                        'listing_type',
                        'price',
                        'street_address',
                        'city',
                        'state',
                        'bedrooms',
                        'bathrooms',
                        'square_footage',
                        'is_featured',
                        'is_available',
                        'status',
                        'views_count',
                        'user',
                        'features',
                        'utilities',
                        'media',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'id' => $property->id,
                        'title' => $property->title
                    ]
                ]);
    }

    public function test_cannot_get_inactive_property()
    {
        $property = Property::factory()->create(['status' => 'inactive']);
        
        $response = $this->getJson("/api/v1/properties/{$property->id}");
        
        $response->assertStatus(404);
    }

    public function test_viewing_property_increments_views_count()
    {
        $property = Property::factory()->create(['status' => 'active', 'views_count' => 5]);
        
        $this->getJson("/api/v1/properties/{$property->id}");
        
        $this->assertEquals(6, $property->fresh()->views_count);
    }

    public function test_authenticated_user_can_create_property()
    {
        Sanctum::actingAs($this->user);
        
        $propertyData = [
            'title' => 'New Test Property',
            'description' => 'A beautiful test property',
            'property_type' => 'apartment',
            'listing_type' => 'rent',
            'price' => 1200,
            'street_address' => '123 Test Street',
            'city' => 'Damascus',
            'state' => 'Damascus',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'square_footage' => 800
        ];
        
        $response = $this->postJson('/api/v1/properties', $propertyData);
        
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'slug'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'title' => 'New Test Property',
                        'property_type' => 'apartment'
                    ]
                ]);
        
        $this->assertDatabaseHas('properties', [
            'title' => 'New Test Property',
            'user_id' => $this->user->id
        ]);
    }

    public function test_unauthenticated_user_cannot_create_property()
    {
        $propertyData = [
            'title' => 'New Test Property',
            'description' => 'A beautiful test property',
            'property_type' => 'apartment',
            'listing_type' => 'rent',
            'price' => 1200
        ];
        
        $response = $this->postJson('/api/v1/properties', $propertyData);
        
        $response->assertStatus(401);
    }

    public function test_property_creation_validation()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/v1/properties', []);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'title',
                    'description',
                    'property_type',
                    'listing_type',
                    'price'
                ]);
    }

    public function test_user_can_update_own_property()
    {
        Sanctum::actingAs($this->user);
        
        $updateData = [
            'title' => 'Updated Property Title',
            'price' => 1500
        ];
        
        $response = $this->putJson("/api/v1/properties/{$this->property->id}", $updateData);
        
        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'title' => 'Updated Property Title',
                        'price' => 1500
                    ]
                ]);
        
        $this->assertDatabaseHas('properties', [
            'id' => $this->property->id,
            'title' => 'Updated Property Title',
            'price' => 1500
        ]);
    }

    public function test_user_cannot_update_others_property()
    {
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);
        
        $updateData = ['title' => 'Hacked Title'];
        
        $response = $this->putJson("/api/v1/properties/{$this->property->id}", $updateData);
        
        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_property()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->deleteJson("/api/v1/properties/{$this->property->id}");
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('properties', [
            'id' => $this->property->id
        ]);
    }

    public function test_user_cannot_delete_others_property()
    {
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);
        
        $response = $this->deleteJson("/api/v1/properties/{$this->property->id}");
        
        $response->assertStatus(403);
        
        $this->assertDatabaseHas('properties', [
            'id' => $this->property->id
        ]);
    }

    public function test_can_attach_features_to_property()
    {
        Sanctum::actingAs($this->user);
        
        $features = Feature::factory()->count(3)->create();
        
        $propertyData = [
            'title' => 'Property with Features',
            'description' => 'A property with features',
            'property_type' => 'apartment',
            'listing_type' => 'rent',
            'price' => 1000,
            'street_address' => '123 Test St',
            'city' => 'Damascus',
            'state' => 'Damascus',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'features' => $features->pluck('id')->toArray()
        ];
        
        $response = $this->postJson('/api/v1/properties', $propertyData);
        
        $response->assertStatus(201);
        
        $property = Property::latest()->first();
        $this->assertCount(3, $property->features);
    }

    public function test_can_get_featured_properties()
    {
        Property::factory()->count(3)->create(['is_featured' => true, 'status' => 'active']);
        Property::factory()->count(2)->create(['is_featured' => false, 'status' => 'active']);
        
        $response = $this->getJson('/api/v1/properties?featured=1');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(3, $data);
        foreach ($data as $property) {
            $this->assertTrue($property['is_featured']);
        }
    }

    public function test_properties_are_paginated()
    {
        Property::factory()->count(25)->create(['status' => 'active']);
        
        $response = $this->getJson('/api/v1/properties?per_page=10');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page'
                    ]
                ]);
        
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    public function test_can_sort_properties()
    {
        Property::factory()->create(['price' => 1000, 'status' => 'active']);
        Property::factory()->create(['price' => 2000, 'status' => 'active']);
        Property::factory()->create(['price' => 1500, 'status' => 'active']);
        
        $response = $this->getJson('/api/v1/properties?sort=price&order=asc');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertEquals(1000, $data[0]['price']);
        $this->assertEquals(1500, $data[1]['price']);
        $this->assertEquals(2000, $data[2]['price']);
    }
}