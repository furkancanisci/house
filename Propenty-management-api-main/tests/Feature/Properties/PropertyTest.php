<?php

namespace Tests\Feature\Properties;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $propertyOwner;
    protected User $generalUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a property owner
        $this->propertyOwner = User::factory()->create([
            'user_type' => 'property_owner',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create a general user
        $this->generalUser = User::factory()->create([
            'user_type' => 'general_user',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_users_can_view_property_listings()
    {
        // Create some properties
        Property::factory(5)->create([
            'user_id' => $this->propertyOwner->id,
            'status' => 'active',
            'is_available' => true,
        ]);

        $response = $this->getJson('/api/v1/properties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'property_type',
                        'listing_type',
                        'price',
                        'location',
                    ],
                ],
                'meta' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                ],
                'links',
            ]);
    }

    public function test_property_owners_can_create_properties()
    {
        Storage::fake('public');

        $token = $this->propertyOwner->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/properties', [
            'title' => 'Test Property',
            'description' => 'This is a test property description',
            'property_type' => 'apartment',
            'listing_type' => 'rent',
            'price' => 1500,
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'postal_code' => '12345',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'square_feet' => 1000,
            'main_image' => UploadedFile::fake()->image('property.jpg'),
            'images' => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'property' => [
                    'id',
                    'title',
                    'description',
                    'property_type',
                    'listing_type',
                    'price',
                    'images',
                ],
            ]);

        $this->assertDatabaseHas('properties', [
            'title' => 'Test Property',
            'user_id' => $this->propertyOwner->id,
            'property_type' => 'apartment',
            'listing_type' => 'rent',
            'price' => 1500,
            'city' => 'Test City',
            'state' => 'TS',
        ]);
    }

    public function test_general_users_cannot_create_properties()
    {
        $token = $this->generalUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/properties', [
            'title' => 'Test Property',
            'description' => 'This is a test property description',
            'property_type' => 'apartment',
            'listing_type' => 'rent',
            'price' => 1500,
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'postal_code' => '12345',
            'bedrooms' => 2,
            'bathrooms' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_users_can_view_property_details()
    {
        $property = Property::factory()->create([
            'user_id' => $this->propertyOwner->id,
            'status' => 'active',
            'is_available' => true,
        ]);

        $response = $this->getJson("/api/v1/properties/{$property->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'property' => [
                    'id',
                    'title',
                    'description',
                    'slug',
                    'property_type',
                    'listing_type',
                    'price',
                    'location',
                    'details',
                    'amenities',
                ],
            ]);
    }

    public function test_property_owners_can_update_their_properties()
    {
        $property = Property::factory()->create([
            'user_id' => $this->propertyOwner->id,
            'status' => 'active',
            'title' => 'Original Title',
        ]);

        $token = $this->propertyOwner->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/v1/properties/{$property->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Property updated successfully.',
                'property' => [
                    'title' => 'Updated Title',
                    'description' => 'Updated description',
                ],
            ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
    }

    public function test_users_cannot_update_properties_they_do_not_own()
    {
        $property = Property::factory()->create([
            'user_id' => $this->propertyOwner->id,
            'status' => 'active',
        ]);

        $token = $this->generalUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/v1/properties/{$property->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_property_owners_can_delete_their_properties()
    {
        $property = Property::factory()->create([
            'user_id' => $this->propertyOwner->id,
        ]);

        $token = $this->propertyOwner->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/v1/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Property deleted successfully.',
            ]);

        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_users_can_toggle_property_favorite_status()
    {
        $property = Property::factory()->create([
            'user_id' => $this->propertyOwner->id,
            'status' => 'active',
            'is_available' => true,
        ]);

        $token = $this->generalUser->createToken('test-token')->plainTextToken;

        // Favorite the property
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/v1/properties/{$property->id}/favorite");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Property added to favorites.',
                'is_favorited' => true,
            ]);

        $this->assertDatabaseHas('property_favorites', [
            'user_id' => $this->generalUser->id,
            'property_id' => $property->id,
        ]);

        // Unfavorite the property
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/v1/properties/{$property->id}/favorite");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Property removed from favorites.',
                'is_favorited' => false,
            ]);

        $this->assertDatabaseMissing('property_favorites', [
            'user_id' => $this->generalUser->id,
            'property_id' => $property->id,
        ]);
    }
}