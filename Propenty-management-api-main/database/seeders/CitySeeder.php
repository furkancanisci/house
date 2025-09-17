<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing cities to avoid duplicates
        DB::table('cities')->truncate();

        // Get governorates for mapping
        $governorates = Governorate::all()->keyBy('name_en');

        $syrianCities = [
            // محافظة دمشق - Damascus Governorate
            [
                'name_ar' => 'دمشق',
                'name_en' => 'Damascus',
                'name_ku' => 'Şam',
                'governorate' => 'Damascus',
                'latitude' => 33.5138,
                'longitude' => 36.2765
            ],
            [
                'name_ar' => 'جرمانا',
                'name_en' => 'Jaramana',
                'name_ku' => 'Jaramana',
                'governorate' => 'Damascus',
                'latitude' => 33.4892,
                'longitude' => 36.3467
            ],
            [
                'name_ar' => 'دوما',
                'name_en' => 'Douma',
                'name_ku' => 'Douma',
                'governorate' => 'Damascus',
                'latitude' => 33.5722,
                'longitude' => 36.4028
            ],
            [
                'name_ar' => 'داريا',
                'name_en' => 'Daraya',
                'name_ku' => 'Daraya',
                'governorate' => 'Damascus',
                'latitude' => 33.4583,
                'longitude' => 36.2319
            ],
            [
                'name_ar' => 'المزة',
                'name_en' => 'Al-Mezzeh',
                'name_ku' => 'Meze',
                'governorate' => 'Damascus',
                'latitude' => 33.5000,
                'longitude' => 36.2500
            ],

            // ريف دمشق - Rif Dimashq Governorate
            [
                'name_ar' => 'الزبداني',
                'name_en' => 'Zabadani',
                'name_ku' => 'Zebdanî',
                'governorate' => 'Rif Dimashq',
                'latitude' => 33.7250,
                'longitude' => 36.1000
            ],
            [
                'name_ar' => 'قطنا',
                'name_en' => 'Qatana',
                'name_ku' => 'Qetena',
                'governorate' => 'Rif Dimashq',
                'latitude' => 33.4367,
                'longitude' => 36.0783
            ],
            [
                'name_ar' => 'صحنايا',
                'name_en' => 'Sahnaya',
                'name_ku' => 'Sehnaya',
                'governorate' => 'Rif Dimashq',
                'latitude' => 33.3897,
                'longitude' => 36.1489
            ],

            // محافظة حلب - Aleppo Governorate
            [
                'name_ar' => 'حلب',
                'name_en' => 'Aleppo',
                'name_ku' => 'Heleb',
                'governorate' => 'Aleppo',
                'latitude' => 36.2021,
                'longitude' => 37.1343
            ],
            [
                'name_ar' => 'عفرين',
                'name_en' => 'Afrin',
                'name_ku' => 'Efrîn',
                'governorate' => 'Aleppo',
                'latitude' => 36.5167,
                'longitude' => 36.8667
            ],
            [
                'name_ar' => 'اعزاز',
                'name_en' => 'Azaz',
                'name_ku' => 'Ezaz',
                'governorate' => 'Aleppo',
                'latitude' => 36.5869,
                'longitude' => 37.0458
            ],
            [
                'name_ar' => 'الباب',
                'name_en' => 'Al-Bab',
                'name_ku' => 'Bab',
                'governorate' => 'Aleppo',
                'latitude' => 36.3708,
                'longitude' => 37.5158
            ],
            [
                'name_ar' => 'منبج',
                'name_en' => 'Manbij',
                'name_ku' => 'Minbic',
                'governorate' => 'Aleppo',
                'latitude' => 36.5281,
                'longitude' => 37.9558
            ],
            [
                'name_ar' => 'جرابلس',
                'name_en' => 'Jarabulus',
                'name_ku' => 'Cerablus',
                'governorate' => 'Aleppo',
                'latitude' => 36.8167,
                'longitude' => 38.0111
            ],
            [
                'name_ar' => 'كوباني',
                'name_en' => 'Kobani',
                'name_ku' => 'Kobanê',
                'governorate' => 'Aleppo',
                'latitude' => 36.8900,
                'longitude' => 38.3531
            ],

            // محافظة حمص - Homs Governorate
            [
                'name_ar' => 'حمص',
                'name_en' => 'Homs',
                'name_ku' => 'Hims',
                'governorate' => 'Homs',
                'latitude' => 34.7394,
                'longitude' => 36.7163
            ],
            [
                'name_ar' => 'تدمر',
                'name_en' => 'Palmyra',
                'name_ku' => 'Tedmur',
                'governorate' => 'Homs',
                'latitude' => 34.5553,
                'longitude' => 38.2842
            ],
            [
                'name_ar' => 'القصير',
                'name_en' => 'Al-Qusayr',
                'name_ku' => 'Qusêr',
                'governorate' => 'Homs',
                'latitude' => 34.5056,
                'longitude' => 36.5794
            ],
            [
                'name_ar' => 'الرستن',
                'name_en' => 'Rastan',
                'name_ku' => 'Rostan',
                'governorate' => 'Homs',
                'latitude' => 34.9264,
                'longitude' => 36.7325
            ],

            // محافظة حماة - Hama Governorate
            [
                'name_ar' => 'حماة',
                'name_en' => 'Hama',
                'name_ku' => 'Hema',
                'governorate' => 'Hama',
                'latitude' => 35.1320,
                'longitude' => 36.7500
            ],
            [
                'name_ar' => 'سلمية',
                'name_en' => 'Salamiyah',
                'name_ku' => 'Selemiye',
                'governorate' => 'Hama',
                'latitude' => 35.0117,
                'longitude' => 37.0533
            ],
            [
                'name_ar' => 'مصياف',
                'name_en' => 'Masyaf',
                'name_ku' => 'Misyaf',
                'governorate' => 'Hama',
                'latitude' => 35.0650,
                'longitude' => 36.3433
            ],
            [
                'name_ar' => 'محردة',
                'name_en' => 'Mahardah',
                'name_ku' => 'Meherde',
                'governorate' => 'Hama',
                'latitude' => 35.1644,
                'longitude' => 36.6172
            ],

            // محافظة اللاذقية - Latakia Governorate
            [
                'name_ar' => 'اللاذقية',
                'name_en' => 'Latakia',
                'name_ku' => 'Lazqiye',
                'governorate' => 'Latakia',
                'latitude' => 35.5138,
                'longitude' => 35.7831
            ],
            [
                'name_ar' => 'جبلة',
                'name_en' => 'Jableh',
                'name_ku' => 'Ceble',
                'governorate' => 'Latakia',
                'latitude' => 35.3608,
                'longitude' => 35.9281
            ],
            [
                'name_ar' => 'القرداحة',
                'name_en' => 'Qardaha',
                'name_ku' => 'Qerdehe',
                'governorate' => 'Latakia',
                'latitude' => 35.4583,
                'longitude' => 35.9889
            ],

            // محافظة طرطوس - Tartus Governorate
            [
                'name_ar' => 'طرطوس',
                'name_en' => 'Tartus',
                'name_ku' => 'Tertûs',
                'governorate' => 'Tartus',
                'latitude' => 34.8897,
                'longitude' => 35.8869
            ],
            [
                'name_ar' => 'بانياس',
                'name_en' => 'Baniyas',
                'name_ku' => 'Banyas',
                'governorate' => 'Tartus',
                'latitude' => 35.1817,
                'longitude' => 35.9483
            ],
            [
                'name_ar' => 'صافيتا',
                'name_en' => 'Safita',
                'name_ku' => 'Safîta',
                'governorate' => 'Tartus',
                'latitude' => 34.8194,
                'longitude' => 36.1211
            ],

            // محافظة إدلب - Idlib Governorate
            [
                'name_ar' => 'إدلب',
                'name_en' => 'Idlib',
                'name_ku' => 'Îdlîb',
                'governorate' => 'Idlib',
                'latitude' => 35.9333,
                'longitude' => 36.6333
            ],
            [
                'name_ar' => 'معرة النعمان',
                'name_en' => 'Maarat al-Numan',
                'name_ku' => 'Meareya Nûman',
                'governorate' => 'Idlib',
                'latitude' => 35.6489,
                'longitude' => 36.6792
            ],
            [
                'name_ar' => 'جسر الشغور',
                'name_en' => 'Jisr al-Shughur',
                'name_ku' => 'Pirê Şixûr',
                'governorate' => 'Idlib',
                'latitude' => 35.8142,
                'longitude' => 36.3178
            ],
            [
                'name_ar' => 'أريحا',
                'name_en' => 'Ariha',
                'name_ku' => 'Arîha',
                'governorate' => 'Idlib',
                'latitude' => 35.8133,
                'longitude' => 36.6075
            ],

            // محافظة درعا - Daraa Governorate
            [
                'name_ar' => 'درعا',
                'name_en' => 'Daraa',
                'name_ku' => 'Dera',
                'governorate' => 'Daraa',
                'latitude' => 32.6189,
                'longitude' => 36.1022
            ],
            [
                'name_ar' => 'بصرى الشام',
                'name_en' => 'Bosra',
                'name_ku' => 'Busra',
                'governorate' => 'Daraa',
                'latitude' => 32.5167,
                'longitude' => 36.4833
            ],
            [
                'name_ar' => 'إزرع',
                'name_en' => 'Izra',
                'name_ku' => 'Îzre',
                'governorate' => 'Daraa',
                'latitude' => 32.8611,
                'longitude' => 36.2583
            ],

            // محافظة السويداء - As-Suwayda Governorate
            [
                'name_ar' => 'السويداء',
                'name_en' => 'As-Suwayda',
                'name_ku' => 'Siwêda',
                'governorate' => 'As-Suwayda',
                'latitude' => 32.7094,
                'longitude' => 36.5694
            ],
            [
                'name_ar' => 'صلخد',
                'name_en' => 'Salkhad',
                'name_ku' => 'Selxed',
                'governorate' => 'As-Suwayda',
                'latitude' => 32.4958,
                'longitude' => 36.7136
            ],
            [
                'name_ar' => 'شهبا',
                'name_en' => 'Shahba',
                'name_ku' => 'Şehba',
                'governorate' => 'As-Suwayda',
                'latitude' => 32.8500,
                'longitude' => 36.6167
            ],

            // محافظة القنيطرة - Quneitra Governorate
            [
                'name_ar' => 'القنيطرة',
                'name_en' => 'Quneitra',
                'name_ku' => 'Qinêtra',
                'governorate' => 'Quneitra',
                'latitude' => 33.1267,
                'longitude' => 35.8244
            ],
            [
                'name_ar' => 'خان أرنبة',
                'name_en' => 'Khan Arnabah',
                'name_ku' => 'Xan Ernebe',
                'governorate' => 'Quneitra',
                'latitude' => 33.2167,
                'longitude' => 35.9833
            ],

            // محافظة الرقة - Raqqa Governorate
            [
                'name_ar' => 'الرقة',
                'name_en' => 'Raqqa',
                'name_ku' => 'Reqqa',
                'governorate' => 'Raqqa',
                'latitude' => 35.9500,
                'longitude' => 39.0167
            ],
            [
                'name_ar' => 'تل أبيض',
                'name_en' => 'Tell Abyad',
                'name_ku' => 'Girê Spî',
                'governorate' => 'Raqqa',
                'latitude' => 36.6958,
                'longitude' => 38.9519
            ],
            [
                'name_ar' => 'الطبقة',
                'name_en' => 'Al-Thawrah',
                'name_ku' => 'Tebeqe',
                'governorate' => 'Raqqa',
                'latitude' => 35.8333,
                'longitude' => 38.5500
            ],

            // محافظة دير الزور - Deir ez-Zor Governorate
            [
                'name_ar' => 'دير الزور',
                'name_en' => 'Deir ez-Zor',
                'name_ku' => 'Dêrika Zor',
                'governorate' => 'Deir ez-Zor',
                'latitude' => 35.3394,
                'longitude' => 40.1467
            ],
            [
                'name_ar' => 'الميادين',
                'name_en' => 'Al-Mayadin',
                'name_ku' => 'Meyadin',
                'governorate' => 'Deir ez-Zor',
                'latitude' => 35.0219,
                'longitude' => 40.4497
            ],
            [
                'name_ar' => 'البوكمال',
                'name_en' => 'Albu Kamal',
                'name_ku' => 'Bûkemal',
                'governorate' => 'Deir ez-Zor',
                'latitude' => 34.4522,
                'longitude' => 40.9181
            ],

            // محافظة الحسكة - Al-Hasakah Governorate
            [
                'name_ar' => 'الحسكة',
                'name_en' => 'Al-Hasakah',
                'name_ku' => 'Hesekê',
                'governorate' => 'Al-Hasakah',
                'latitude' => 36.5000,
                'longitude' => 40.7500
            ],
            [
                'name_ar' => 'القامشلي',
                'name_en' => 'Qamishli',
                'name_ku' => 'Qamişlo',
                'governorate' => 'Al-Hasakah',
                'latitude' => 37.0522,
                'longitude' => 41.2317
            ],
            [
                'name_ar' => 'رأس العين',
                'name_en' => 'Ras al-Ayn',
                'name_ku' => 'Serê Kaniyê',
                'governorate' => 'Al-Hasakah',
                'latitude' => 36.8508,
                'longitude' => 40.0694
            ],
            [
                'name_ar' => 'المالكية',
                'name_en' => 'Al-Malikiyah',
                'name_ku' => 'Dêrik',
                'governorate' => 'Al-Hasakah',
                'latitude' => 37.1953,
                'longitude' => 42.0506
            ],
            [
                'name_ar' => 'عامودا',
                'name_en' => 'Amuda',
                'name_ku' => 'Amûdê',
                'governorate' => 'Al-Hasakah',
                'latitude' => 37.1044,
                'longitude' => 40.9281
            ],
        ];

        foreach ($syrianCities as $cityData) {
            $governorate = $governorates->get($cityData['governorate']);

            if ($governorate) {
                City::create([
                    'name_ar' => $cityData['name_ar'],
                    'name_en' => $cityData['name_en'],
                    'name_ku' => $cityData['name_ku'],
                    'slug' => \Str::slug($cityData['name_en']),
                    'governorate_id' => $governorate->id,
                    'country_ar' => 'سوريا',
                    'country_en' => 'Syria',
                    'state_ar' => $governorate->name_ar, // Keep for backward compatibility
                    'state_en' => $governorate->name_en, // Keep for backward compatibility
                    'latitude' => $cityData['latitude'],
                    'longitude' => $cityData['longitude'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Syrian cities seeded successfully with Kurmanci names!');
    }
}