<?php

namespace Tests\Browser;

use App\Models\Property;
use App\Models\User;
use App\Models\PropertyType;
use App\Models\City;
use App\Models\District;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PropertySearchTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $user;
    protected PropertyType $propertyType;
    protected City $city;
    protected District $district;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        
        $this->propertyType = PropertyType::factory()->create(['name' => 'Apartment']);
        $this->city = City::factory()->create(['name' => 'Istanbul']);
        $this->district = District::factory()->create([
            'name' => 'Kadikoy',
            'city_id' => $this->city->id
        ]);
    }

    /**
     * Test property search functionality from frontend
     */
    public function test_user_can_search_properties()
    {
        // Arrange
        Property::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active',
            'title' => 'Beautiful Apartment in Istanbul'
        ]);

        Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active',
            'title' => 'Luxury Villa in Ankara',
            'price' => 2000000
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->waitFor('.search-form', 10)
                    ->type('input[name="search"]', 'Istanbul')
                    ->press('Search')
                    ->waitFor('.property-card', 10)
                    ->assertSeeIn('.property-results', 'Beautiful Apartment')
                    ->assertDontSeeIn('.property-results', 'Luxury Villa');
        });
    }

    /**
     * Test property filtering functionality
     */
    public function test_user_can_filter_properties_by_price()
    {
        // Arrange
        Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active',
            'title' => 'Affordable Apartment',
            'price' => 500000
        ]);

        Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active',
            'title' => 'Expensive Penthouse',
            'price' => 3000000
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->waitFor('.filter-form', 10)
                    ->type('input[name="min_price"]', '400000')
                    ->type('input[name="max_price"]', '1000000')
                    ->press('Apply Filters')
                    ->waitFor('.property-card', 10)
                    ->assertSeeIn('.property-results', 'Affordable Apartment')
                    ->assertDontSeeIn('.property-results', 'Expensive Penthouse');
        });
    }

    /**
     * Test property detail view
     */
    public function test_user_can_view_property_details()
    {
        // Arrange
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active',
            'title' => 'Test Property Details',
            'description' => 'This is a detailed description of the property',
            'price' => 750000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 120
        ]);

        $this->browse(function (Browser $browser) use ($property) {
            $browser->visit('/')
                    ->waitFor('.property-card', 10)
                    ->click('.property-card:first-child .view-details')
                    ->waitForLocation('/properties/' . $property->id)
                    ->assertSeeIn('.property-title', 'Test Property Details')
                    ->assertSeeIn('.property-description', 'detailed description')
                    ->assertSeeIn('.property-price', '750,000')
                    ->assertSeeIn('.property-specs', '3 bedrooms')
                    ->assertSeeIn('.property-specs', '2 bathrooms')
                    ->assertSeeIn('.property-specs', '120 m²');
        });
    }

    /**
     * Test user authentication flow
     */
    public function test_user_can_login_and_create_property()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('form', 10)
                    ->type('email', 'test@example.com')
                    ->type('password', 'password')
                    ->press('Login')
                    ->waitForLocation('/dashboard')
                    ->assertSee('Dashboard')
                    ->click('a[href="/properties/create"]')
                    ->waitForLocation('/properties/create')
                    ->type('title', 'New Property from E2E Test')
                    ->type('description', 'This property was created during an E2E test')
                    ->type('price', '850000')
                    ->select('property_type_id', $this->propertyType->id)
                    ->select('city_id', $this->city->id)
                    ->select('district_id', $this->district->id)
                    ->type('address', 'Test Address 123')
                    ->type('bedrooms', '3')
                    ->type('bathrooms', '2')
                    ->type('area', '130')
                    ->press('Create Property')
                    ->waitFor('.success-message', 10)
                    ->assertSee('Property created successfully');
        });

        // Verify property was created in database
        $this->assertDatabaseHas('properties', [
            'title' => 'New Property from E2E Test',
            'user_id' => $this->user->id
        ]);
    }

    /**
     * Test responsive design on mobile
     */
    public function test_mobile_responsive_design()
    {
        // Arrange
        Property::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active'
        ]);

        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667) // iPhone 6/7/8 size
                    ->visit('/')
                    ->waitFor('.mobile-menu-toggle', 10)
                    ->assertVisible('.mobile-menu-toggle')
                    ->click('.mobile-menu-toggle')
                    ->waitFor('.mobile-menu', 5)
                    ->assertVisible('.mobile-menu')
                    ->assertVisible('.property-card')
                    ->click('.property-card:first-child')
                    ->waitFor('.property-details', 10)
                    ->assertVisible('.property-image-carousel')
                    ->assertVisible('.property-info-mobile');
        });
    }

    /**
     * Test property image upload functionality
     */
    public function test_user_can_upload_property_images()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/properties/create')
                    ->waitFor('form', 10)
                    ->type('title', 'Property with Images')
                    ->type('description', 'This property has uploaded images')
                    ->type('price', '950000')
                    ->select('property_type_id', $this->propertyType->id)
                    ->select('city_id', $this->city->id)
                    ->select('district_id', $this->district->id)
                    ->type('address', 'Image Test Address')
                    ->attach('images[]', __DIR__.'/fixtures/test-image.jpg')
                    ->waitFor('.image-preview', 10)
                    ->assertVisible('.image-preview')
                    ->press('Create Property')
                    ->waitFor('.success-message', 10)
                    ->assertSee('Property created successfully');
        });
    }

    /**
     * Test property search with no results
     */
    public function test_search_with_no_results_shows_appropriate_message()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->waitFor('.search-form', 10)
                    ->type('input[name="search"]', 'NonexistentPropertySearch123')
                    ->press('Search')
                    ->waitFor('.no-results-message', 10)
                    ->assertSee('No properties found')
                    ->assertSee('Try adjusting your search criteria');
        });
    }

    /**
     * Test property contact form
     */
    public function test_user_can_contact_property_owner()
    {
        // Arrange
        $property = Property::factory()->create([
            'user_id' => $this->user->id,
            'property_type_id' => $this->propertyType->id,
            'city_id' => $this->city->id,
            'district_id' => $this->district->id,
            'status' => 'active'
        ]);

        $contactUser = User::factory()->create([
            'email' => 'contact@example.com',
            'password' => bcrypt('password')
        ]);

        $this->browse(function (Browser $browser) use ($property, $contactUser) {
            $browser->loginAs($contactUser)
                    ->visit('/properties/' . $property->id)
                    ->waitFor('.contact-form', 10)
                    ->type('message', 'I am interested in this property. Please contact me.')
                    ->type('phone', '+90 555 123 4567')
                    ->press('Send Message')
                    ->waitFor('.success-message', 10)
                    ->assertSee('Message sent successfully');
        });
    }
}