<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Models\User;
use App\Models\PropertyType;
use App\Models\Feature;
use App\Models\Utility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_belongs_to_user()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $property->user);
        $this->assertEquals($user->id, $property->user->id);
    }

    public function test_property_has_many_media()
    {
        $property = Property::factory()->create();
        
        // Add media to property
        $property->addMediaFromUrl('https://via.placeholder.com/640x480.png/00aa00?text=test')
            ->toMediaCollection('images');

        $this->assertCount(1, $property->getMedia('images'));
    }

    public function test_property_belongs_to_many_features()
    {
        $property = Property::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $property->features()->attach($features->pluck('id'));

        $this->assertCount(3, $property->features);
        $this->assertInstanceOf(Feature::class, $property->features->first());
    }

    public function test_property_belongs_to_many_utilities()
    {
        $property = Property::factory()->create();
        $utilities = Utility::factory()->count(2)->create();

        $property->utilities()->attach($utilities->pluck('id'));

        $this->assertCount(2, $property->utilities);
        $this->assertInstanceOf(Utility::class, $property->utilities->first());
    }

    public function test_property_slug_is_generated_automatically()
    {
        $property = Property::factory()->create([
            'title' => 'Beautiful Apartment in Damascus'
        ]);

        $this->assertNotNull($property->slug);
        $this->assertStringContainsString('beautiful-apartment-in-damascus', $property->slug);
    }

    public function test_property_active_scope()
    {
        // Create active properties
        Property::factory()->count(3)->create(['status' => 'active']);
        
        // Create inactive properties
        Property::factory()->count(2)->create(['status' => 'inactive']);

        $activeProperties = Property::active()->get();
        
        $this->assertCount(3, $activeProperties);
        foreach ($activeProperties as $property) {
            $this->assertEquals('active', $property->status);
        }
    }

    public function test_property_for_listing_scope()
    {
        // Create rent properties
        Property::factory()->count(2)->create(['listing_type' => 'rent']);
        
        // Create sale properties
        Property::factory()->count(3)->create(['listing_type' => 'sale']);

        $rentProperties = Property::forListing('rent')->get();
        $saleProperties = Property::forListing('sale')->get();
        
        $this->assertCount(2, $rentProperties);
        $this->assertCount(3, $saleProperties);
        
        foreach ($rentProperties as $property) {
            $this->assertEquals('rent', $property->listing_type);
        }
        
        foreach ($saleProperties as $property) {
            $this->assertEquals('sale', $property->listing_type);
        }
    }

    public function test_property_featured_scope()
    {
        // Create featured properties
        Property::factory()->count(2)->create(['is_featured' => true]);
        
        // Create non-featured properties
        Property::factory()->count(3)->create(['is_featured' => false]);

        $featuredProperties = Property::featured()->get();
        
        $this->assertCount(2, $featuredProperties);
        foreach ($featuredProperties as $property) {
            $this->assertTrue($property->is_featured);
        }
    }

    public function test_property_available_scope()
    {
        // Create available properties
        Property::factory()->count(3)->create(['is_available' => true]);
        
        // Create unavailable properties
        Property::factory()->count(2)->create(['is_available' => false]);

        $availableProperties = Property::available()->get();
        
        $this->assertCount(3, $availableProperties);
        foreach ($availableProperties as $property) {
            $this->assertTrue($property->is_available);
        }
    }

    public function test_property_increment_views()
    {
        $property = Property::factory()->create(['views_count' => 5]);
        
        $initialViews = $property->views_count;
        $property->incrementViews();
        
        $this->assertEquals($initialViews + 1, $property->fresh()->views_count);
    }

    public function test_property_price_formatting()
    {
        $property = Property::factory()->create(['price' => 150000]);
        
        // Test formatted price method if it exists
        if (method_exists($property, 'getFormattedPriceAttribute')) {
            $this->assertIsString($property->formatted_price);
        }
        
        $this->assertEquals(150000, $property->price);
    }

    public function test_property_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Try to create property without required fields
        Property::create([]);
    }

    public function test_property_fillable_attributes()
    {
        $data = [
            'title' => 'Test Property',
            'description' => 'A test property',
            'property_type' => 'apartment',
            'listing_type' => 'rent',
            'price' => 1000,
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'user_id' => User::factory()->create()->id
        ];

        $property = Property::create($data);
        
        $this->assertEquals('Test Property', $property->title);
        $this->assertEquals('apartment', $property->property_type);
        $this->assertEquals('rent', $property->listing_type);
        $this->assertEquals(1000, $property->price);
    }

    public function test_property_casts()
    {
        $property = Property::factory()->create([
            'is_featured' => 1,
            'is_available' => 0,
            'price' => '1500.50'
        ]);

        $this->assertIsBool($property->is_featured);
        $this->assertIsBool($property->is_available);
        $this->assertTrue($property->is_featured);
        $this->assertFalse($property->is_available);
    }

    public function test_property_search_functionality()
    {
        $property1 = Property::factory()->create([
            'title' => 'Beautiful Apartment',
            'city' => 'Damascus',
            'status' => 'active'
        ]);

        $property2 = Property::factory()->create([
            'title' => 'Modern House',
            'city' => 'Aleppo',
            'status' => 'active'
        ]);

        // Test search by title
        $results = Property::where('title', 'like', '%Beautiful%')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($property1->id, $results->first()->id);

        // Test search by city
        $results = Property::where('city', 'Damascus')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($property1->id, $results->first()->id);
    }
}