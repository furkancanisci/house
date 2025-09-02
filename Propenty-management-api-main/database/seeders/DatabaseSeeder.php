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
            PropertyDocumentTypeSeeder::class,
            FeaturesAndUtilitiesSeeder::class,
            UserSeeder::class,
            PropertySeeder::class,
            PropertyFavoriteSeeder::class,
            PropertyViewSeeder::class,
        ]);
    }
}
