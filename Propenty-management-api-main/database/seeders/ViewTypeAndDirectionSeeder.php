<?php

namespace Database\Seeders;

use App\Models\ViewType;
use App\Models\Direction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViewTypeAndDirectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to prevent duplicates
        ViewType::truncate();
        Direction::truncate();
        
        // View Types Data
        $viewTypes = [
            [
                'name_en' => 'Sea View',
                'name_ar' => 'إطلالة بحرية',
                'value' => 'sea_view',
                'description_en' => 'Property with sea view',
                'description_ar' => 'عقار بإطلالة بحرية',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name_en' => 'Mountain View',
                'name_ar' => 'إطلالة جبلية',
                'value' => 'mountain_view',
                'description_en' => 'Property with mountain view',
                'description_ar' => 'عقار بإطلالة جبلية',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name_en' => 'City View',
                'name_ar' => 'إطلالة على المدينة',
                'value' => 'city_view',
                'description_en' => 'Property with city view',
                'description_ar' => 'عقار بإطلالة على المدينة',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name_en' => 'Garden View',
                'name_ar' => 'إطلالة على الحديقة',
                'value' => 'garden_view',
                'description_en' => 'Property with garden view',
                'description_ar' => 'عقار بإطلالة على الحديقة',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name_en' => 'Street View',
                'name_ar' => 'إطلالة على الشارع',
                'value' => 'street_view',
                'description_en' => 'Property with street view',
                'description_ar' => 'عقار بإطلالة على الشارع',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name_en' => 'Pool View',
                'name_ar' => 'إطلالة على المسبح',
                'value' => 'pool_view',
                'description_en' => 'Property with pool view',
                'description_ar' => 'عقار بإطلالة على المسبح',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        // Directions Data
        $directions = [
            [
                'name_en' => 'North',
                'name_ar' => 'شمال',
                'value' => 'north',
                'description_en' => 'North direction',
                'description_ar' => 'الاتجاه الشمالي',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name_en' => 'South',
                'name_ar' => 'جنوب',
                'value' => 'south',
                'description_en' => 'South direction',
                'description_ar' => 'الاتجاه الجنوبي',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name_en' => 'East',
                'name_ar' => 'شرق',
                'value' => 'east',
                'description_en' => 'East direction',
                'description_ar' => 'الاتجاه الشرقي',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name_en' => 'West',
                'name_ar' => 'غرب',
                'value' => 'west',
                'description_en' => 'West direction',
                'description_ar' => 'الاتجاه الغربي',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name_en' => 'Northeast',
                'name_ar' => 'شمال شرق',
                'value' => 'northeast',
                'description_en' => 'Northeast direction',
                'description_ar' => 'الاتجاه الشمالي الشرقي',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name_en' => 'Northwest',
                'name_ar' => 'شمال غرب',
                'value' => 'northwest',
                'description_en' => 'Northwest direction',
                'description_ar' => 'الاتجاه الشمالي الغربي',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name_en' => 'Southeast',
                'name_ar' => 'جنوب شرق',
                'value' => 'southeast',
                'description_en' => 'Southeast direction',
                'description_ar' => 'الاتجاه الجنوبي الشرقي',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name_en' => 'Southwest',
                'name_ar' => 'جنوب غرب',
                'value' => 'southwest',
                'description_en' => 'Southwest direction',
                'description_ar' => 'الاتجاه الجنوبي الغربي',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        // Insert View Types
        foreach ($viewTypes as $viewType) {
            ViewType::create($viewType);
        }

        // Insert Directions
        foreach ($directions as $direction) {
            Direction::create($direction);
        }

        $this->command->info('View Types and Directions seeded successfully!');
    }
}