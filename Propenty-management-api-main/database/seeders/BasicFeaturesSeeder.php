<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Feature;
use Illuminate\Support\Str;

class BasicFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basicFeatures = [
            [
                'name_ar' => 'مصعد',
                'name_en' => 'Elevator',
                'name_ku' => 'Asansor',
                'description_ar' => 'يحتوي العقار على مصعد',
                'description_en' => 'Property has an elevator',
                'description_ku' => 'Xanî asansorek heye',
                'icon' => 'elevator',
                'category' => 'building',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name_ar' => 'شرفة',
                'name_en' => 'Balcony',
                'name_ku' => 'Balkon',
                'description_ar' => 'يحتوي العقار على شرفة',
                'description_en' => 'Property has a balcony',
                'description_ku' => 'Xanî balkonek heye',
                'icon' => 'balcony',
                'category' => 'interior',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name_ar' => 'حارس أمن',
                'name_en' => 'Security Guard',
                'name_ku' => 'Parastina Ewlehiyê',
                'description_ar' => 'يوجد حارس أمن في المبنى',
                'description_en' => 'Building has security guard',
                'description_ku' => 'Avahî parastina ewlehiyê heye',
                'icon' => 'shield-check',
                'category' => 'security',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($basicFeatures as $featureData) {
            // Generate slug from English name
            $featureData['slug'] = Str::slug($featureData['name_en']);
            
            // Check if feature already exists
            $existingFeature = Feature::where('slug', $featureData['slug'])->first();
            
            if (!$existingFeature) {
                Feature::create($featureData);
            }
        }
    }
}
