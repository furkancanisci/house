<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get property owners
        $propertyOwners = User::where('user_type', 'property_owner')
            ->where('is_verified', true)
            ->get();

        if ($propertyOwners->isEmpty()) {
            return;
        }

        // Sample properties data
        $properties = [
            [
                'title' => 'Modern Downtown Apartment',
                'description' => 'Stunning modern apartment in the heart of downtown with floor-to-ceiling windows, hardwood floors, and amazing city views. Features include granite countertops, stainless steel appliances, in-unit laundry, and building amenities like fitness center and rooftop deck.',
                'property_type' => 'apartment',
                'listing_type' => 'rent',
                'price' => 2500.00,
                'price_type' => 'monthly',
                'street_address' => '123 Main Street, Unit 15A',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'latitude' => 40.7506,
                'longitude' => -73.9756,
                'neighborhood' => 'Midtown',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_feet' => 1200,
                'year_built' => 2020,
                'parking_type' => 'garage',
                'parking_spaces' => 1,
                'amenities' => ['Air Conditioning', 'Dishwasher', 'Laundry in Unit', 'Balcony', 'Hardwood Floors', 'High Ceilings', 'Elevator', 'Gym', 'Roof Deck', 'Internet'],
                'status' => 'active',
                'is_featured' => true,
                'is_available' => true,
            ],
            [
                'title' => 'Luxury Family Villa',
                'description' => 'Breathtaking 4-bedroom luxury villa with private pool, landscaped gardens, and panoramic mountain views. This executive home features gourmet kitchen, formal dining room, family room with fireplace, master suite with walk-in closet, and 3-car garage.',
                'property_type' => 'villa',
                'listing_type' => 'sale',
                'price' => 850000.00,
                'price_type' => 'total',
                'street_address' => '456 Oak Hill Drive',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90210',
                'latitude' => 34.0901,
                'longitude' => -118.4065,
                'neighborhood' => 'Beverly Hills',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'square_feet' => 3500,
                'lot_size' => 8000,
                'year_built' => 2018,
                'parking_type' => 'garage',
                'parking_spaces' => 3,
                'amenities' => ['Air Conditioning', 'Heating', 'Dishwasher', 'Garden', 'Fireplace', 'Hardwood Floors', 'Walk-in Closet', 'Storage', 'Garage', 'Pool', 'Security System'],
                'status' => 'active',
                'is_featured' => true,
                'is_available' => true,
            ],
            [
                'title' => 'Cozy Studio Loft',
                'description' => 'Charming studio loft in trendy arts district. Open floor plan with exposed brick walls, high ceilings, and large windows. Perfect for young professionals or artists. Walking distance to galleries, cafes, and public transportation.',
                'property_type' => 'studio',
                'listing_type' => 'rent',
                'price' => 1200.00,
                'price_type' => 'monthly',
                'street_address' => '789 Artist Way',
                'city' => 'Austin',
                'state' => 'TX',
                'postal_code' => '78701',
                'latitude' => 30.2672,
                'longitude' => -97.7431,
                'neighborhood' => 'Arts District',
                'bedrooms' => 0,
                'bathrooms' => 1,
                'square_feet' => 650,
                'year_built' => 2015,
                'parking_type' => 'street',
                'parking_spaces' => 0,
                'amenities' => ['Air Conditioning', 'Hardwood Floors', 'High Ceilings', 'Internet', 'Recently Renovated'],
                'status' => 'active',
                'is_featured' => false,
                'is_available' => true,
            ],
            [
                'title' => 'Suburban Family Home',
                'description' => 'Perfect family home in quiet suburban neighborhood. Features spacious living areas, updated kitchen, fenced backyard, and excellent school district. Great for families with children. Close to parks, shopping, and recreational facilities.',
                'property_type' => 'house',
                'listing_type' => 'sale',
                'price' => 415000.00,
                'price_type' => 'total',
                'street_address' => '321 Maple Lane',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'postal_code' => '85001',
                'latitude' => 33.4484,
                'longitude' => -112.0740,
                'neighborhood' => 'Sunset Valley',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 1800,
                'lot_size' => 6000,
                'year_built' => 2010,
                'parking_type' => 'driveway',
                'parking_spaces' => 2,
                'amenities' => ['Air Conditioning', 'Heating', 'Dishwasher', 'Garden', 'Fireplace', 'Carpet', 'Storage', 'Pet Friendly'],
                'status' => 'active',
                'is_featured' => false,
                'is_available' => true,
            ],
            [
                'title' => 'Waterfront Condo',
                'description' => 'Stunning waterfront condominium with breathtaking ocean views. Features floor-to-ceiling windows, modern kitchen, spacious balcony, and access to private beach. Building amenities include concierge, valet parking, and infinity pool.',
                'property_type' => 'condo',
                'listing_type' => 'rent',
                'price' => 3200.00,
                'price_type' => 'monthly',
                'street_address' => '555 Ocean Drive, Unit 22B',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33139',
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'neighborhood' => 'South Beach',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_feet' => 1400,
                'year_built' => 2019,
                'parking_type' => 'garage',
                'parking_spaces' => 1,
                'amenities' => ['Air Conditioning', 'Dishwasher', 'Balcony', 'Hardwood Floors', 'High Ceilings', 'Elevator', 'Doorman', 'Concierge', 'Pool', 'Internet'],
                'status' => 'active',
                'is_featured' => true,
                'is_available' => true,
            ],
            [
                'title' => 'Historic Townhouse',
                'description' => 'Beautifully restored historic townhouse in charming old town district. Original hardwood floors, exposed beams, updated kitchen and bathrooms while maintaining historic character. Walking distance to restaurants and shops.',
                'property_type' => 'townhouse',
                'listing_type' => 'sale',
                'price' => 650000.00,
                'price_type' => 'total',
                'street_address' => '876 Heritage Street',
                'city' => 'Boston',
                'state' => 'MA',
                'postal_code' => '02101',
                'latitude' => 42.3601,
                'longitude' => -71.0589,
                'neighborhood' => 'Historic District',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 2200,
                'year_built' => 1890,
                'parking_type' => 'street',
                'parking_spaces' => 0,
                'amenities' => ['Heating', 'Fireplace', 'Hardwood Floors', 'High Ceilings', 'Storage', 'Recently Renovated'],
                'status' => 'active',
                'is_featured' => false,
                'is_available' => true,
            ],
            [
                'title' => 'Modern Loft Space',
                'description' => 'Industrial-style loft in converted warehouse. Open floor plan with exposed brick, concrete floors, and oversized windows. Perfect for creative professionals. Includes dedicated workspace area and modern kitchen.',
                'property_type' => 'loft',
                'listing_type' => 'rent',
                'price' => 1800.00,
                'price_type' => 'monthly',
                'street_address' => '999 Factory Street, Unit 5',
                'city' => 'Portland',
                'state' => 'OR',
                'postal_code' => '97201',
                'latitude' => 45.5152,
                'longitude' => -122.6784,
                'neighborhood' => 'Industrial District',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_feet' => 1000,
                'year_built' => 2016,
                'parking_type' => 'garage',
                'parking_spaces' => 1,
                'amenities' => ['Air Conditioning', 'High Ceilings', 'Storage', 'Internet', 'New Construction'],
                'status' => 'active',
                'is_featured' => false,
                'is_available' => true,
            ],
            [
                'title' => 'Commercial Office Space',
                'description' => 'Premium commercial office space in business district. Open floor plan suitable for various business types. Features include conference room, reception area, kitchenette, and ample parking. Ready for immediate occupancy.',
                'property_type' => 'commercial',
                'listing_type' => 'rent',
                'price' => 4500.00,
                'price_type' => 'monthly',
                'street_address' => '1200 Business Plaza',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'neighborhood' => 'Financial District',
                'bedrooms' => 0,
                'bathrooms' => 2,
                'square_feet' => 2500,
                'year_built' => 2012,
                'parking_type' => 'garage',
                'parking_spaces' => 4,
                'amenities' => ['Air Conditioning', 'Heating', 'Elevator', 'Security System', 'Internet'],
                'status' => 'active',
                'is_featured' => false,
                'is_available' => true,
            ],
        ];

        // Create properties and assign to random property owners
        foreach ($properties as $index => $propertyData) {
            $property = Property::create(array_merge($propertyData, [
                'user_id' => $propertyOwners->random()->id,
                'slug' => Str::slug($propertyData['title']) . '-' . ($index + 1),
                'published_at' => now()->subDays(rand(1, 30)),
                'views_count' => rand(0, 500),
            ]));

            // Add some nearby places
            $nearbyPlaces = [
                ['name' => 'Starbucks', 'type' => 'cafe', 'distance' => 0.2],
                ['name' => 'Metro Station', 'type' => 'transport', 'distance' => 0.3],
                ['name' => 'Whole Foods', 'type' => 'grocery', 'distance' => 0.5],
                ['name' => 'Central Park', 'type' => 'park', 'distance' => 0.8],
                ['name' => 'ABC Elementary', 'type' => 'school', 'distance' => 1.2],
            ];

            $property->update([
                'nearby_places' => array_slice($nearbyPlaces, 0, rand(2, 4))
            ]);
        }

        // Create some draft properties
        $draftProperties = [
            [
                'title' => 'Draft Property - Luxury Penthouse',
                'description' => 'This is a draft property that hasn\'t been published yet.',
                'property_type' => 'apartment',
                'listing_type' => 'sale',
                'price' => 1200000.00,
                'status' => 'draft',
                'is_available' => false,
            ],
            [
                'title' => 'Draft Property - Country Cottage',
                'description' => 'Another draft property in progress.',
                'property_type' => 'house',
                'listing_type' => 'rent',
                'price' => 1500.00,
                'status' => 'draft',
                'is_available' => false,
            ],
        ];

        foreach ($draftProperties as $index => $propertyData) {
            Property::create(array_merge($propertyData, [
                'user_id' => $propertyOwners->random()->id,
                'slug' => Str::slug($propertyData['title']) . '-draft-' . ($index + 1),
                'street_address' => '123 Draft Street',
                'city' => 'Draft City',
                'state' => 'CA',
                'postal_code' => '90000',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'square_feet' => 1000,
                'year_built' => 2020,
                'amenities' => ['Air Conditioning', 'Heating'],
            ]));
        }
    }
}
