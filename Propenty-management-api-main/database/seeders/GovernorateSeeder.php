<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Governorate;
use Illuminate\Support\Str;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            [
                'name_ar' => 'دمشق',
                'name_en' => 'Damascus',
                'name_ku' => 'دیمەشق',
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'is_active' => true,
            ],
            [
                'name_ar' => 'ريف دمشق',
                'name_en' => 'Rif Dimashq',
                'name_ku' => 'ڕیفی دیمەشق',
                'latitude' => 33.6000,
                'longitude' => 36.3000,
                'is_active' => true,
            ],
            [
                'name_ar' => 'حلب',
                'name_en' => 'Aleppo',
                'name_ku' => 'حەلەب',
                'latitude' => 36.2021,
                'longitude' => 37.1343,
                'is_active' => true,
            ],
            [
                'name_ar' => 'حمص',
                'name_en' => 'Homs',
                'name_ku' => 'حمس',
                'latitude' => 34.7394,
                'longitude' => 36.7163,
                'is_active' => true,
            ],
            [
                'name_ar' => 'حماة',
                'name_en' => 'Hama',
                'name_ku' => 'حەما',
                'latitude' => 35.1320,
                'longitude' => 36.7500,
                'is_active' => true,
            ],
            [
                'name_ar' => 'اللاذقية',
                'name_en' => 'Latakia',
                'name_ku' => 'لازقیە',
                'latitude' => 35.5138,
                'longitude' => 35.7831,
                'is_active' => true,
            ],
            [
                'name_ar' => 'طرطوس',
                'name_en' => 'Tartus',
                'name_ku' => 'تەرتووس',
                'latitude' => 34.8886,
                'longitude' => 35.8869,
                'is_active' => true,
            ],
            [
                'name_ar' => 'إدلب',
                'name_en' => 'Idlib',
                'name_ku' => 'ئیدلیب',
                'latitude' => 35.9333,
                'longitude' => 36.6333,
                'is_active' => true,
            ],
            [
                'name_ar' => 'درعا',
                'name_en' => 'Daraa',
                'name_ku' => 'دەرعا',
                'latitude' => 32.6189,
                'longitude' => 36.1021,
                'is_active' => true,
            ],
            [
                'name_ar' => 'السويداء',
                'name_en' => 'As-Suwayda',
                'name_ku' => 'سوێیدا',
                'latitude' => 32.7094,
                'longitude' => 36.5694,
                'is_active' => true,
            ],
            [
                'name_ar' => 'القنيطرة',
                'name_en' => 'Quneitra',
                'name_ku' => 'قونێیترە',
                'latitude' => 33.1264,
                'longitude' => 35.8244,
                'is_active' => true,
            ],
            [
                'name_ar' => 'دير الزور',
                'name_en' => 'Deir ez-Zor',
                'name_ku' => 'دێرەزۆر',
                'latitude' => 35.3394,
                'longitude' => 40.1467,
                'is_active' => true,
            ],
            [
                'name_ar' => 'الرقة',
                'name_en' => 'Raqqa',
                'name_ku' => 'ڕەققە',
                'latitude' => 35.9500,
                'longitude' => 39.0167,
                'is_active' => true,
            ],
            [
                'name_ar' => 'الحسكة',
                'name_en' => 'Al-Hasakah',
                'name_ku' => 'حەسەکە',
                'latitude' => 36.5000,
                'longitude' => 40.7500,
                'is_active' => true,
            ],
        ];

        foreach ($governorates as $governorateData) {
            // Generate slug from English name
            $governorateData['slug'] = Str::slug($governorateData['name_en']);
            
            Governorate::create($governorateData);
        }

        $this->command->info('Syrian governorates seeded successfully!');
    }
}