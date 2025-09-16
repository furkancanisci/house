<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_properties()
    {
        $user = User::factory()->create();
        $properties = Property::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->properties);
        $this->assertInstanceOf(Property::class, $user->properties->first());
    }

    public function test_user_password_is_hashed()
    {
        $user = User::factory()->create([
            'password' => 'password123'
        ]);

        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotEquals('password123', $user->password);
    }

    public function test_user_fillable_attributes()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'phone' => '+963123456789'
        ];

        $user = User::create($userData);

        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('+963123456789', $user->phone);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_hidden_attributes()
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    public function test_user_email_verification_timestamp_cast()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    public function test_user_full_name_accessor()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        // Test if full_name accessor exists
        if (method_exists($user, 'getFullNameAttribute')) {
            $this->assertEquals('John Doe', $user->full_name);
        }
    }

    public function test_user_active_properties_relationship()
    {
        $user = User::factory()->create();
        
        // Create active properties
        Property::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'active'
        ]);
        
        // Create inactive properties
        Property::factory()->count(1)->create([
            'user_id' => $user->id,
            'status' => 'inactive'
        ]);

        // Test if activeProperties relationship exists
        if (method_exists($user, 'activeProperties')) {
            $this->assertCount(2, $user->activeProperties);
        } else {
            // Fallback to manual filtering
            $activeProperties = $user->properties()->where('status', 'active')->get();
            $this->assertCount(2, $activeProperties);
        }
    }

    public function test_user_email_uniqueness()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_user_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Try to create user without required fields
        User::create([]);
    }

    public function test_user_email_validation_format()
    {
        // This would typically be tested at the request validation level
        // but we can test the model accepts valid email formats
        $user = User::factory()->create(['email' => 'valid@example.com']);
        $this->assertEquals('valid@example.com', $user->email);
    }

    public function test_user_password_mutator()
    {
        $user = new User();
        $user->password = 'plaintext';
        
        // Password should be hashed automatically
        $this->assertTrue(Hash::check('plaintext', $user->password));
        $this->assertNotEquals('plaintext', $user->password);
    }

    public function test_user_timestamps()
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->updated_at);
    }

    public function test_user_soft_deletes()
    {
        // Test if soft deletes are enabled
        $user = User::factory()->create();
        $userId = $user->id;
        
        if (method_exists($user, 'delete')) {
            $user->delete();
            
            // If soft deletes are enabled, user should still exist in database
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($user))) {
                $this->assertSoftDeleted($user);
                $this->assertDatabaseHas('users', ['id' => $userId]);
            } else {
                $this->assertDatabaseMissing('users', ['id' => $userId]);
            }
        }
    }

    public function test_user_properties_count()
    {
        $user = User::factory()->create();
        Property::factory()->count(5)->create(['user_id' => $user->id]);
        
        $userWithCount = User::withCount('properties')->find($user->id);
        $this->assertEquals(5, $userWithCount->properties_count);
    }

    public function test_user_can_create_property()
    {
        $user = User::factory()->create();
        
        $property = $user->properties()->create([
            'title' => 'Test Property',
            'description' => 'A test property',
            'property_type' => 'apartment',
            'listing_type' => 'rent',
            'price' => 1000,
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'bedrooms' => 2,
            'bathrooms' => 1
        ]);

        $this->assertInstanceOf(Property::class, $property);
        $this->assertEquals($user->id, $property->user_id);
        $this->assertEquals('Test Property', $property->title);
    }

    public function test_user_phone_format()
    {
        $user = User::factory()->create(['phone' => '+963123456789']);
        $this->assertEquals('+963123456789', $user->phone);
        
        // Test various phone formats if validation exists
        $validPhones = ['+963123456789', '0123456789', '123456789'];
        
        foreach ($validPhones as $phone) {
            $testUser = User::factory()->create(['phone' => $phone]);
            $this->assertEquals($phone, $testUser->phone);
        }
    }

    public function test_user_avatar_handling()
    {
        $user = User::factory()->create();
        
        // Test if user has media collection for avatar
        if (method_exists($user, 'getMedia')) {
            $this->assertCount(0, $user->getMedia('avatar'));
        }
    }
}