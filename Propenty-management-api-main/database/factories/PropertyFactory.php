<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $slug = Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999);
        $propertyType = fake()->randomElement(['apartment', 'house', 'condo', 'townhouse', 'studio', 'loft', 'villa', 'commercial', 'land']);
        $listingType = fake()->randomElement(['rent', 'sale']);
        $price = $listingType === 'rent' 
            ? fake()->numberBetween(500, 5000) 
            : fake()->numberBetween(100000, 1000000);
        $priceType = $listingType === 'rent' 
            ? fake()->randomElement(['monthly', 'yearly']) 
            : 'total';
        
        $city = fake()->city();
        $state = fake()->stateAbbr();
        $status = fake()->randomElement(['draft', 'active', 'pending', 'sold', 'rented', 'inactive']);
        
        return [
            'user_id' => User::factory()->propertyOwner(),
            'title' => $title,
            'description' => fake()->paragraphs(3, true),
            'property_type' => $propertyType,
            'listing_type' => $listingType,
            'price' => $price,
            'price_type' => $priceType,
            'street_address' => fake()->streetAddress(),
            'city' => $city,
            'state' => $state,
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(24, 49),
            'longitude' => fake()->longitude(-125, -66),
            'neighborhood' => fake()->word() . ' District',
            'bedrooms' => $propertyType === 'land' ? 0 : fake()->numberBetween(0, 5),
            'bathrooms' => $propertyType === 'land' ? 0 : fake()->numberBetween(1, 4),
            'square_feet' => $propertyType === 'land' ? null : fake()->numberBetween(500, 4000),
            'lot_size' => $propertyType === 'land' ? fake()->numberBetween(1000, 50000) : null,
            'year_built' => fake()->numberBetween(1950, 2023),
            'parking_type' => fake()->randomElement(['none', 'street', 'garage', 'driveway', 'carport']),
            'parking_spaces' => fake()->numberBetween(0, 3),
            'status' => $status,
            'is_featured' => fake()->boolean(20),
            'is_available' => in_array($status, ['active', 'pending']),
            'available_from' => fake()->dateTimeBetween('now', '+3 months'),
            'slug' => $slug,
            'amenities' => $this->getRandomAmenities(),
            'nearby_places' => $this->getRandomNearbyPlaces(),
            'views_count' => fake()->numberBetween(0, 500),
            'published_at' => $status === 'draft' ? null : fake()->dateTimeBetween('-3 months', 'now'),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Property $property) {
            // Add media here if needed
        });
    }

    /**
     * Get random property amenities.
     *
     * @return array
     */
    private function getRandomAmenities(): array
    {
        $allAmenities = [
            'Air Conditioning',
            'Heating',
            'Dishwasher',
            'Laundry in Unit',
            'Laundry in Building',
            'Balcony',
            'Patio',
            'Garden',
            'Roof Deck',
            'Terrace',
            'Fireplace',
            'Hardwood Floors',
            'Carpet',
            'Tile Floors',
            'High Ceilings',
            'Walk-in Closet',
            'Storage',
            'Basement',
            'Attic',
            'Garage',
            'Parking',
            'Elevator',
            'Doorman',
            'Concierge',
            'Security System',
            'Intercom',
            'Video Security',
            'Gym',
            'Pool',
            'Hot Tub',
            'Sauna',
            'Tennis Court',
            'Basketball Court',
            'Playground',
            'Dog Park',
            'Pet Friendly',
            'No Pets',
            'Furnished',
            'Unfurnished',
            'Internet',
            'Cable TV',
            'Utilities Included',
            'Recently Renovated',
            'New Construction',
        ];
        
        // Select 3-10 random amenities
        $count = fake()->numberBetween(3, 10);
        $keys = array_rand($allAmenities, $count);
        
        $selectedAmenities = [];
        foreach ($keys as $key) {
            $selectedAmenities[] = $allAmenities[$key];
        }
        
        return $selectedAmenities;
    }

    /**
     * Get random nearby places.
     *
     * @return array
     */
    private function getRandomNearbyPlaces(): array
    {
        $places = [
            ['name' => 'Starbucks', 'type' => 'cafe', 'distance' => fake()->randomFloat(1, 0.1, 2.0)],
            ['name' => 'Whole Foods', 'type' => 'grocery', 'distance' => fake()->randomFloat(1, 0.2, 3.0)],
            ['name' => 'Metro Station', 'type' => 'transportation', 'distance' => fake()->randomFloat(1, 0.1, 1.5)],
            ['name' => 'City Park', 'type' => 'park', 'distance' => fake()->randomFloat(1, 0.3, 2.5)],
            ['name' => 'Elementary School', 'type' => 'school', 'distance' => fake()->randomFloat(1, 0.4, 2.0)],
            ['name' => 'Gym', 'type' => 'fitness', 'distance' => fake()->randomFloat(1, 0.2, 1.8)],
            ['name' => 'Hospital', 'type' => 'healthcare', 'distance' => fake()->randomFloat(1, 0.5, 4.0)],
            ['name' => 'Shopping Mall', 'type' => 'shopping', 'distance' => fake()->randomFloat(1, 0.7, 5.0)],
            ['name' => 'Restaurant Row', 'type' => 'dining', 'distance' => fake()->randomFloat(1, 0.3, 2.0)],
            ['name' => 'Public Library', 'type' => 'education', 'distance' => fake()->randomFloat(1, 0.5, 3.0)],
        ];
        
        // Select 2-5 random places
        $count = fake()->numberBetween(2, 5);
        $keys = array_rand($places, $count);
        
        $selectedPlaces = [];
        if (is_array($keys)) {
            foreach ($keys as $key) {
                $selectedPlaces[] = $places[$key];
            }
        } else {
            // If only one place is selected, $keys is not an array
            $selectedPlaces[] = $places[$keys];
        }
        
        return $selectedPlaces;
    }

    /**
     * Indicate that the property is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'is_available' => true,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Indicate that the property is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'is_available' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the property is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the property is for rent.
     */
    public function forRent(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'rent',
            'price_type' => fake()->randomElement(['monthly', 'yearly']),
            'price' => fake()->numberBetween(500, 5000),
        ]);
    }

    /**
     * Indicate that the property is for sale.
     */
    public function forSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'sale',
            'price_type' => 'total',
            'price' => fake()->numberBetween(100000, 1000000),
        ]);
    }
}