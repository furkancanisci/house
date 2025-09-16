<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Feature;
use Illuminate\Support\Str;

class AdvancedFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $advancedFeatures = [
            [
                'name_en' => 'Central Heating',
                'name_ar' => 'تدفئة مركزية',
                'name_ku' => 'گەرمکردنی ناوەندی',
                'description_en' => 'Central heating system for the entire property',
                'description_ar' => 'نظام تدفئة مركزي للعقار بالكامل',
                'description_ku' => 'سیستەمی گەرمکردنی ناوەندی بۆ تەواوی خانووەکە',
                'category' => 'heating_cooling',
                'icon' => 'thermometer',
                'sort_order' => 100,
                'is_active' => true,
            ],
            [
                'name_en' => 'Central Air Conditioning',
                'name_ar' => 'تكييف مركزي',
                'name_ku' => 'ئەیر کۆندیشنی ناوەندی',
                'description_en' => 'Central air conditioning system',
                'description_ar' => 'نظام تكييف مركزي',
                'description_ku' => 'سیستەمی ئەیر کۆندیشنی ناوەندی',
                'category' => 'heating_cooling',
                'icon' => 'wind',
                'sort_order' => 101,
                'is_active' => true,
            ],
            [
                'name_en' => 'Natural Gas',
                'name_ar' => 'غاز طبيعي',
                'name_ku' => 'گازی سروشتی',
                'description_en' => 'Natural gas connection available',
                'description_ar' => 'توصيل الغاز الطبيعي متاح',
                'description_ku' => 'بەستنەوەی گازی سروشتی بەردەستە',
                'category' => 'utilities',
                'icon' => 'flame',
                'sort_order' => 102,
                'is_active' => true,
            ],
            [
                'name_en' => 'Security System',
                'name_ar' => 'نظام أمني',
                'name_ku' => 'سیستەمی ئاسایش',
                'description_en' => 'Advanced security alarm system',
                'description_ar' => 'نظام إنذار أمني متقدم',
                'description_ku' => 'سیستەمی ئاگادارکردنەوەی ئاسایشی پێشکەوتوو',
                'category' => 'security',
                'icon' => 'shield',
                'sort_order' => 103,
                'is_active' => true,
            ],
            [
                'name_en' => 'Jacuzzi',
                'name_ar' => 'جاكوزي',
                'name_ku' => 'جاکووزی',
                'description_en' => 'Private jacuzzi or hot tub',
                'description_ar' => 'جاكوزي أو حوض استحمام ساخن خاص',
                'description_ku' => 'جاکووزی تایبەت یان حەوزی ئاوی گەرم',
                'category' => 'luxury',
                'icon' => 'waves',
                'sort_order' => 104,
                'is_active' => true,
            ],
            [
                'name_en' => 'Smart Home System',
                'name_ar' => 'نظام منزل ذكي',
                'name_ku' => 'سیستەمی ماڵی زیرەک',
                'description_en' => 'Integrated smart home automation system',
                'description_ar' => 'نظام أتمتة منزلي ذكي متكامل',
                'description_ku' => 'سیستەمی یەکگرتووی ئۆتۆماتیکی ماڵی زیرەک',
                'category' => 'technology',
                'icon' => 'smartphone',
                'sort_order' => 105,
                'is_active' => true,
            ],
        ];

        foreach ($advancedFeatures as $feature) {
            $feature['slug'] = Str::slug($feature['name_en']);
            Feature::updateOrCreate(
                ['slug' => $feature['slug']],
                $feature
            );
        }
    }
}
