<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Feature;
use App\Models\Utility;

class FeaturesAndUtilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Features data with multilingual support
        $features = [
            [
                'name_en' => 'Swimming Pool',
                'name_ar' => 'مسبح',
                'name_ku' => 'Hewza Avê',
                'icon' => 'pool'
            ],
            [
                'name_en' => 'Gym/Fitness Center',
                'name_ar' => 'صالة رياضية',
                'name_ku' => 'Navendê Werzîşê',
                'icon' => 'dumbbell'
            ],
            [
                'name_en' => 'Garden',
                'name_ar' => 'حديقة',
                'name_ku' => 'Baxçe',
                'icon' => 'trees'
            ],
            [
                'name_en' => 'Balcony',
                'name_ar' => 'شرفة',
                'name_ku' => 'Balkon',
                'icon' => 'home'
            ],
            [
                'name_en' => 'Parking Garage',
                'name_ar' => 'مرآب سيارات',
                'name_ku' => 'Garajê Otomobîlan',
                'icon' => 'car'
            ],
            [
                'name_en' => 'Security System',
                'name_ar' => 'نظام أمان',
                'name_ku' => 'Sîstema Ewlehiyê',
                'icon' => 'shield'
            ],
            [
                'name_en' => 'Air Conditioning',
                'name_ar' => 'تكييف هواء',
                'name_ku' => 'Kondîsyona Hewayê',
                'icon' => 'wind'
            ],
            [
                'name_en' => 'Central Heating',
                'name_ar' => 'تدفئة مركزية',
                'name_ku' => 'Germkirina Navendî',
                'icon' => 'thermometer'
            ],
            [
                'name_en' => 'Fireplace',
                'name_ar' => 'مدفأة',
                'name_ku' => 'Agirdan',
                'icon' => 'flame'
            ],
            [
                'name_en' => 'Walk-in Closet',
                'name_ar' => 'خزانة ملابس كبيرة',
                'name_ku' => 'Dolabê Cilan',
                'icon' => 'shirt'
            ],
            [
                'name_en' => 'Elevator',
                'name_ar' => 'مصعد',
                'name_ku' => 'Asansor',
                'icon' => 'move-vertical'
            ],
            [
                'name_en' => 'Terrace/Rooftop',
                'name_ar' => 'تراس/سطح',
                'name_ku' => 'Teras/Serban',
                'icon' => 'building'
            ]
        ];

        // Utilities data with multilingual support
        $utilities = [
            [
                'name_en' => 'Electricity',
                'name_ar' => 'كهرباء',
                'name_ku' => 'Kareba',
                'icon' => 'zap'
            ],
            [
                'name_en' => 'Water',
                'name_ar' => 'مياه',
                'name_ku' => 'Av',
                'icon' => 'droplets'
            ],
            [
                'name_en' => 'Gas',
                'name_ar' => 'غاز',
                'name_ku' => 'Gaz',
                'icon' => 'flame'
            ],
            [
                'name_en' => 'Internet/WiFi',
                'name_ar' => 'إنترنت/واي فاي',
                'name_ku' => 'Înternet/WiFi',
                'icon' => 'wifi'
            ],
            [
                'name_en' => 'Cable TV',
                'name_ar' => 'تلفزيون كابل',
                'name_ku' => 'Televîzyona Kablê',
                'icon' => 'tv'
            ],
            [
                'name_en' => 'Trash Collection',
                'name_ar' => 'جمع القمامة',
                'name_ku' => 'Berhevkirina Zibilê',
                'icon' => 'trash-2'
            ],
            [
                'name_en' => 'Sewage',
                'name_ar' => 'صرف صحي',
                'name_ku' => 'Avê Pîs',
                'icon' => 'droplets'
            ],
            [
                'name_en' => 'Security Service',
                'name_ar' => 'خدمة أمان',
                'name_ku' => 'Xizmeta Ewlehiyê',
                'icon' => 'shield-check'
            ],
            [
                'name_en' => 'Maintenance',
                'name_ar' => 'صيانة',
                'name_ku' => 'Çêkirina',
                'icon' => 'wrench'
            ],
            [
                'name_en' => 'Cleaning Service',
                'name_ar' => 'خدمة تنظيف',
                'name_ku' => 'Xizmeta Paqijkirinê',
                'icon' => 'sparkles'
            ]
        ];

        // Insert features
        foreach ($features as $feature) {
            Feature::updateOrCreate(
                ['name_en' => $feature['name_en']],
                $feature
            );
        }

        // Insert utilities
        foreach ($utilities as $utility) {
            Utility::updateOrCreate(
                ['name_en' => $utility['name_en']],
                $utility
            );
        }

        $this->command->info('Features and Utilities seeded successfully!');
    }
}