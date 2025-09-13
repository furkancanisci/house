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
            'name_ar' => 'سكني',
            'name_ku' => 'Nijgeh',
            'slug' => 'residential',
            'description' => 'Properties designed for people to live in',
            'icon' => 'fas fa-home',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $commercial = PropertyType::create([
            'name' => 'Commercial',
            'name_ar' => 'تجاري',
            'name_ku' => 'Bazirganî',
            'slug' => 'commercial',
            'description' => 'Properties used for business purposes',
            'icon' => 'fas fa-building',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $industrial = PropertyType::create([
            'name' => 'Industrial',
            'name_ar' => 'صناعي',
            'name_ku' => 'Pîşesazî',
            'slug' => 'industrial',
            'description' => 'Properties used for manufacturing and production',
            'icon' => 'fas fa-industry',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $land = PropertyType::create([
            'name' => 'Land',
            'name_ar' => 'أرض',
            'name_ku' => 'Erd',
            'slug' => 'land',
            'description' => 'Vacant land and plots',
            'icon' => 'fas fa-map',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        // Create residential subcategories
        PropertyType::create([
            'name' => 'Apartment',
            'name_ar' => 'شقة',
            'name_ku' => 'Apartman',
            'slug' => 'apartment',
            'description' => 'Multi-unit residential building',
            'icon' => 'fas fa-building',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PropertyType::create([
            'name' => 'Villa',
            'name_ar' => 'فيلا',
            'name_ku' => 'Vîlla',
            'slug' => 'villa',
            'description' => 'Large luxury residential property',
            'icon' => 'fas fa-home',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PropertyType::create([
            'name' => 'House',
            'name_ar' => 'منزل',
            'name_ku' => 'Mal',
            'slug' => 'house',
            'description' => 'Single-family residential property',
            'icon' => 'fas fa-home',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        PropertyType::create([
            'name' => 'Townhouse',
            'name_ar' => 'منزل متصل',
            'name_ku' => 'Mala Girêdayî',
            'slug' => 'townhouse',
            'description' => 'Multi-story house sharing walls with neighbors',
            'icon' => 'fas fa-home',
            'parent_id' => $residential->id,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        PropertyType::create([
            'name' => 'Studio',
            'name_ar' => 'استوديو',
            'name_ku' => 'Studyo',
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
            'name_ar' => 'مكتب',
            'name_ku' => 'Nivîsgeh',
            'slug' => 'office',
            'description' => 'Office space for business use',
            'icon' => 'fas fa-briefcase',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PropertyType::create([
            'name' => 'Retail',
            'name_ar' => 'متجر',
            'name_ku' => 'Firotgeh',
            'slug' => 'retail',
            'description' => 'Retail space for shops and stores',
            'icon' => 'fas fa-store',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PropertyType::create([
            'name' => 'Restaurant',
            'name_ar' => 'مطعم',
            'name_ku' => 'Xwarinxane',
            'slug' => 'restaurant',
            'description' => 'Restaurant and food service space',
            'icon' => 'fas fa-utensils',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        PropertyType::create([
            'name' => 'Hotel',
            'name_ar' => 'فندق',
            'name_ku' => 'Otel',
            'slug' => 'hotel',
            'description' => 'Hotel and hospitality property',
            'icon' => 'fas fa-bed',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        PropertyType::create([
            'name' => 'Warehouse',
            'name_ar' => 'مستودع',
            'name_ku' => 'Anbar',
            'slug' => 'warehouse',
            'description' => 'Storage and distribution facility',
            'icon' => 'fas fa-warehouse',
            'parent_id' => $commercial->id,
            'is_active' => true,
            'sort_order' => 5,
        ]);

        // Create industrial subcategories
        PropertyType::create([
            'name' => 'Factory',
            'name_ar' => 'مصنع',
            'name_ku' => 'Karxane',
            'slug' => 'factory',
            'description' => 'Manufacturing facility',
            'icon' => 'fas fa-industry',
            'parent_id' => $industrial->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PropertyType::create([
            'name' => 'Industrial Land',
            'name_ar' => 'أرض صناعية',
            'name_ku' => 'Erdê Pîşesazî',
            'slug' => 'industrial-land',
            'description' => 'Land zoned for industrial use',
            'icon' => 'fas fa-map',
            'parent_id' => $industrial->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Create land subcategories
        PropertyType::create([
            'name' => 'Residential Plot',
            'name_ar' => 'قطعة أرض سكنية',
            'name_ku' => 'Parçeya Erdê Nijgehî',
            'slug' => 'residential-plot',
            'description' => 'Land designated for residential development',
            'icon' => 'fas fa-home',
            'parent_id' => $land->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PropertyType::create([
            'name' => 'Commercial Plot',
            'name_ar' => 'قطعة أرض تجارية',
            'name_ku' => 'Parçeya Erdê Bazirganî',
            'slug' => 'commercial-plot',
            'description' => 'Land designated for commercial development',
            'icon' => 'fas fa-building',
            'parent_id' => $land->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PropertyType::create([
            'name' => 'Agricultural Land',
            'name_ar' => 'أرض زراعية',
            'name_ku' => 'Erdê Çandinî',
            'slug' => 'agricultural-land',
            'description' => 'Land used for farming and agriculture',
            'icon' => 'fas fa-seedling',
            'parent_id' => $land->id,
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
