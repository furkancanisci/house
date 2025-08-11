<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $syrianCities = [
            // محافظة دمشق
            [
                'name_ar' => 'دمشق',
                'name_en' => 'Damascus',
                'state_ar' => 'دمشق',
                'state_en' => 'Damascus',
                'latitude' => 33.5138,
                'longitude' => 36.2765
            ],
            [
                'name_ar' => 'جرمانا',
                'name_en' => 'Jaramana',
                'state_ar' => 'دمشق',
                'state_en' => 'Damascus',
                'latitude' => 33.4892,
                'longitude' => 36.3467
            ],
            [
                'name_ar' => 'دوما',
                'name_en' => 'Douma',
                'state_ar' => 'دمشق',
                'state_en' => 'Damascus',
                'latitude' => 33.5722,
                'longitude' => 36.4028
            ],

            // محافظة حلب
            [
                'name_ar' => 'حلب',
                'name_en' => 'Aleppo',
                'state_ar' => 'حلب',
                'state_en' => 'Aleppo',
                'latitude' => 36.2021,
                'longitude' => 37.1343
            ],
            [
                'name_ar' => 'عفرين',
                'name_en' => 'Afrin',
                'state_ar' => 'حلب',
                'state_en' => 'Aleppo',
                'latitude' => 36.5167,
                'longitude' => 36.8667
            ],
            [
                'name_ar' => 'اعزاز',
                'name_en' => 'Azaz',
                'state_ar' => 'حلب',
                'state_en' => 'Aleppo',
                'latitude' => 36.5869,
                'longitude' => 37.0458
            ],
            [
                'name_ar' => 'الباب',
                'name_en' => 'Al-Bab',
                'state_ar' => 'حلب',
                'state_en' => 'Aleppo',
                'latitude' => 36.3708,
                'longitude' => 37.5158
            ],

            // محافظة حمص
            [
                'name_ar' => 'حمص',
                'name_en' => 'Homs',
                'state_ar' => 'حمص',
                'state_en' => 'Homs',
                'latitude' => 34.7394,
                'longitude' => 36.7163
            ],
            [
                'name_ar' => 'تدمر',
                'name_en' => 'Palmyra',
                'state_ar' => 'حمص',
                'state_en' => 'Homs',
                'latitude' => 34.5553,
                'longitude' => 38.2842
            ],

            // محافظة حماة
            [
                'name_ar' => 'حماة',
                'name_en' => 'Hama',
                'state_ar' => 'حماة',
                'state_en' => 'Hama',
                'latitude' => 35.1320,
                'longitude' => 36.7500
            ],
            [
                'name_ar' => 'سلمية',
                'name_en' => 'Salamiyah',
                'state_ar' => 'حماة',
                'state_en' => 'Hama',
                'latitude' => 35.0117,
                'longitude' => 37.0533
            ],

            // محافظة اللاذقية
            [
                'name_ar' => 'اللاذقية',
                'name_en' => 'Latakia',
                'state_ar' => 'اللاذقية',
                'state_en' => 'Latakia',
                'latitude' => 35.5138,
                'longitude' => 35.7831
            ],
            [
                'name_ar' => 'جبلة',
                'name_en' => 'Jableh',
                'state_ar' => 'اللاذقية',
                'state_en' => 'Latakia',
                'latitude' => 35.3608,
                'longitude' => 35.9281
            ],

            // محافظة طرطوس
            [
                'name_ar' => 'طرطوس',
                'name_en' => 'Tartus',
                'state_ar' => 'طرطوس',
                'state_en' => 'Tartus',
                'latitude' => 34.8897,
                'longitude' => 35.8869
            ],
            [
                'name_ar' => 'بانياس',
                'name_en' => 'Baniyas',
                'state_ar' => 'طرطوس',
                'state_en' => 'Tartus',
                'latitude' => 35.1817,
                'longitude' => 35.9483
            ],

            // محافظة إدلب
            [
                'name_ar' => 'إدلب',
                'name_en' => 'Idlib',
                'state_ar' => 'إدلب',
                'state_en' => 'Idlib',
                'latitude' => 35.9333,
                'longitude' => 36.6333
            ],
            [
                'name_ar' => 'معرة النعمان',
                'name_en' => 'Maarat al-Numan',
                'state_ar' => 'إدلب',
                'state_en' => 'Idlib',
                'latitude' => 35.6489,
                'longitude' => 36.6792
            ],

            // محافظة درعا
            [
                'name_ar' => 'درعا',
                'name_en' => 'Daraa',
                'state_ar' => 'درعا',
                'state_en' => 'Daraa',
                'latitude' => 32.6189,
                'longitude' => 36.1022
            ],
            [
                'name_ar' => 'بصرى الشام',
                'name_en' => 'Bosra',
                'state_ar' => 'درعا',
                'state_en' => 'Daraa',
                'latitude' => 32.5167,
                'longitude' => 36.4833
            ],

            // محافظة السويداء
            [
                'name_ar' => 'السويداء',
                'name_en' => 'As-Suwayda',
                'state_ar' => 'السويداء',
                'state_en' => 'As-Suwayda',
                'latitude' => 32.7094,
                'longitude' => 36.5694
            ],

            // محافظة القنيطرة
            [
                'name_ar' => 'القنيطرة',
                'name_en' => 'Quneitra',
                'state_ar' => 'القنيطرة',
                'state_en' => 'Quneitra',
                'latitude' => 33.1267,
                'longitude' => 35.8244
            ],

            // محافظة الرقة
            [
                'name_ar' => 'الرقة',
                'name_en' => 'Raqqa',
                'state_ar' => 'الرقة',
                'state_en' => 'Raqqa',
                'latitude' => 35.9500,
                'longitude' => 39.0167
            ],

            // محافظة دير الزور
            [
                'name_ar' => 'دير الزور',
                'name_en' => 'Deir ez-Zor',
                'state_ar' => 'دير الزور',
                'state_en' => 'Deir ez-Zor',
                'latitude' => 35.3394,
                'longitude' => 40.1467
            ],

            // محافظة الحسكة
            [
                'name_ar' => 'الحسكة',
                'name_en' => 'Al-Hasakah',
                'state_ar' => 'الحسكة',
                'state_en' => 'Al-Hasakah',
                'latitude' => 36.5000,
                'longitude' => 40.7500
            ],
            [
                'name_ar' => 'القامشلي',
                'name_en' => 'Qamishli',
                'state_ar' => 'الحسكة',
                'state_en' => 'Al-Hasakah',
                'latitude' => 37.0522,
                'longitude' => 41.2317
            ],
            [
                'name_ar' => 'رأس العين',
                'name_en' => 'Ras al-Ayn',
                'state_ar' => 'الحسكة',
                'state_en' => 'Al-Hasakah',
                'latitude' => 36.8508,
                'longitude' => 40.0694
            ],
        ];

        foreach ($syrianCities as $city) {
            City::create($city);
        }
    }
}