<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create parent categories first
        $residential = PropertyType::create([
            'name' => 'Residential',
            'slug' => 'residential',
            'description' => 'Properties designed for people to live in',
            'icon' => 'fas fa-home',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $commercial = PropertyType::create([
            'name' => 'Commercial',
            'slug' => 'commercial',
            'description' => 'Properties used for business purposes',
            'icon' => 'fas fa-building',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $industrial = PropertyType::create([
            'name' => 'Industrial',
            'slug' => 'industrial',
            'description' => 'Properties used for manufacturing and production',
            'icon' => 'fas fa-industry',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $land = PropertyType::create([
            'name' => 'Land',
            'slug' => 'land',
            'description' => 'Vacant land and plots',
            'icon' => 'fas fa-map',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        // Create residential subcategories
        PropertyType::create([
            'name' => 'Apartment',
            'slug' => 'apartment',
            'description' => 'Multi-unit residential building',
            'icon' => 'fas fa-building',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PropertyType::create([
            'name' => 'Villa',
            'slug' => 'villa',
            'description' => 'Large luxury residential property',
            'icon' => 'fas fa-home',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PropertyType::create([
            'name' => 'House',
            'slug' => 'house',
            'description' => 'Single-family residential property',
            'icon' => 'fas fa-home',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        PropertyType::create([
            'name' => 'Townhouse',
            'slug' => 'townhouse',
            'description' => 'Multi-story house sharing walls with neighbors',
            'icon' => 'fas fa-home',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        PropertyType::create([
            'name' => 'Studio',
            'slug' => 'studio',
            'description' => 'Small one-room apartment',
            'icon' => 'fas fa-door-open',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 5,
        ]);

        // Create commercial subcategories
        PropertyType::create([
            'name' => 'Office',
            'slug' => 'office',
            'description' => 'Commercial office space',
            'icon' => 'fas fa-briefcase',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PropertyType::create([
            'name' => 'Retail',
            'slug' => 'retail',
            'description' => 'Shop or retail space',
            'icon' => 'fas fa-store',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PropertyType::create([
            'name' => 'Restaurant',
            'slug' => 'restaurant',
            'description' => 'Food service establishment',
            'icon' => 'fas fa-utensils',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        PropertyType::create([
            'name' => 'Warehouse',
            'slug' => 'warehouse',
            'description' => 'Storage and distribution facility',
            'icon' => 'fas fa-warehouse',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        // Create land subcategories
        PropertyType::create([
            'name' => 'Residential Plot',
            'slug' => 'residential-plot',
            'description' => 'Land designated for residential development',
            'icon' => 'fas fa-home',
            'parent_id' => $land->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PropertyType::create([
            'name' => 'Commercial Plot',
            'slug' => 'commercial-plot',
            'description' => 'Land designated for commercial development',
            'icon' => 'fas fa-building',
            'parent_id' => $land->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PropertyType::create([
            'name' => 'Agricultural Land',
            'slug' => 'agricultural-land',
            'description' => 'Land used for farming and agriculture',
            'icon' => 'fas fa-seedling',
            'parent_id' => $land->id,
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
