<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdvancedDetailsPermissionsSeeder::class,
            PriceTypePermissionsSeeder::class,
            PropertyDocumentTypeSeeder::class,
            PropertyTypeSeeder::class,
            FeaturesAndUtilitiesSeeder::class,
            ViewTypeAndDirectionSeeder::class,
            GovernorateSeeder::class,
            UserSeeder::class,
            PropertyFavoriteSeeder::class,
            PropertyViewSeeder::class,
            AdvancedFeaturesSeeder::class,
            CitySeeder::class,
            SyrianCitiesSeeder::class,
        ]);
    }
}
