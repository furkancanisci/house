<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SyrianCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            // Damascus Governorate
            [
                'name_en' => 'Damascus',
                'name_ar' => 'دمشق',
                'state_en' => 'Damascus',
                'state_ar' => 'دمشق',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'is_active' => true
            ],
            [
                'name_en' => 'Douma',
                'name_ar' => 'دوما',
                'state_en' => 'Damascus',
                'state_ar' => 'دمشق',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 33.5722,
                'longitude' => 36.4028,
                'is_active' => true
            ],
            
            // Aleppo Governorate
            [
                'name_en' => 'Aleppo',
                'name_ar' => 'حلب',
                'state_en' => 'Aleppo',
                'state_ar' => 'حلب',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 36.2021,
                'longitude' => 37.1343,
                'is_active' => true
            ],
            [
                'name_en' => 'Afrin',
                'name_ar' => 'عفرين',
                'state_en' => 'Aleppo',
                'state_ar' => 'حلب',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 36.5116,
                'longitude' => 36.8693,
                'is_active' => true
            ],
            
            // Homs Governorate
            [
                'name_en' => 'Homs',
                'name_ar' => 'حمص',
                'state_en' => 'Homs',
                'state_ar' => 'حمص',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 34.7394,
                'longitude' => 36.7061,
                'is_active' => true
            ],
            [
                'name_en' => 'Palmyra',
                'name_ar' => 'تدمر',
                'state_en' => 'Homs',
                'state_ar' => 'حمص',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 34.5618,
                'longitude' => 38.2690,
                'is_active' => true
            ],
            
            // Hama Governorate
            [
                'name_en' => 'Hama',
                'name_ar' => 'حماة',
                'state_en' => 'Hama',
                'state_ar' => 'حماة',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 35.1319,
                'longitude' => 36.7540,
                'is_active' => true
            ],
            
            // Latakia Governorate
            [
                'name_en' => 'Latakia',
                'name_ar' => 'اللاذقية',
                'state_en' => 'Latakia',
                'state_ar' => 'اللاذقية',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 35.5138,
                'longitude' => 35.7858,
                'is_active' => true
            ],
            [
                'name_en' => 'Jableh',
                'name_ar' => 'جبلة',
                'state_en' => 'Latakia',
                'state_ar' => 'اللاذقية',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 35.3622,
                'longitude' => 35.9282,
                'is_active' => true
            ],
            
            // Tartus Governorate
            [
                'name_en' => 'Tartus',
                'name_ar' => 'طرطوس',
                'state_en' => 'Tartus',
                'state_ar' => 'طرطوس',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 34.8898,
                'longitude' => 35.8867,
                'is_active' => true
            ],
            
            // Daraa Governorate
            [
                'name_en' => 'Daraa',
                'name_ar' => 'درعا',
                'state_en' => 'Daraa',
                'state_ar' => 'درعا',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 32.6189,
                'longitude' => 36.1027,
                'is_active' => true
            ],
            
            // Idlib Governorate
            [
                'name_en' => 'Idlib',
                'name_ar' => 'إدلب',
                'state_en' => 'Idlib',
                'state_ar' => 'إدلب',
                'country_en' => 'Syria',
                'country_ar' => 'سوريا',
                'latitude' => 35.9333,
                'longitude' => 36.6333,
                'is_active' => true
            ]
        ];

        foreach ($cities as $cityData) {
            $cityData['slug'] = Str::slug($cityData['name_en']);
            
            City::updateOrCreate(
                [
                    'name_en' => $cityData['name_en'],
                    'state_en' => $cityData['state_en']
                ],
                $cityData
            );
        }

        $this->command->info('Syrian cities seeded successfully!');
    }
}