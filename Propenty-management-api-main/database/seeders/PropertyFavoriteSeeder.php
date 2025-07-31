<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertyFavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get general users (who can favorite properties)
        $generalUsers = User::where('user_type', 'general_user')->get();
        
        // Get active properties
        $activeProperties = Property::where('status', 'active')->get();

        if ($generalUsers->isEmpty() || $activeProperties->isEmpty()) {
            return;
        }

        // Create random favorites
        foreach ($generalUsers as $user) {
            // Each user favorites 1-5 random properties
            $favoritesCount = rand(1, 5);
            $propertiesToFavorite = $activeProperties->random(min($favoritesCount, $activeProperties->count()));

            foreach ($propertiesToFavorite as $property) {
                // Don't favorite own properties
                if ($property->user_id !== $user->id) {
                    $user->favoriteProperties()->attach($property->id, [
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(1, 30)),
                    ]);
                }
            }
        }

        // Some property owners might also be looking at properties
        $propertyOwners = User::where('user_type', 'property_owner')->get();

        foreach ($propertyOwners as $owner) {
            // 50% chance a property owner favorites other properties
            if (rand(0, 1)) {
                $favoritesCount = rand(1, 3);
                $propertiesToFavorite = $activeProperties
                    ->where('user_id', '!=', $owner->id) // Don't favorite own properties
                    ->random(min($favoritesCount, $activeProperties->where('user_id', '!=', $owner->id)->count()));

                foreach ($propertiesToFavorite as $property) {
                    $owner->favoriteProperties()->attach($property->id, [
                        'created_at' => now()->subDays(rand(1, 60)),
                        'updated_at' => now()->subDays(rand(1, 60)),
                    ]);
                }
            }
        }
    }
}
