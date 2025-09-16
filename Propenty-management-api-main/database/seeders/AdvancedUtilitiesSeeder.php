<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Utility;
use Illuminate\Support\Str;

class AdvancedUtilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $advancedUtilities = [
            [
                'name_en' => 'Pharmacy',
                'name_ar' => 'صيدلية',
                'name_ku' => 'Dermanxane',
                'description_en' => 'Nearby pharmacy for medical needs',
                'description_ar' => 'صيدلية قريبة للاحتياجات الطبية',
                'description_ku' => 'Dermanxaneya nêzîk ji bo hewcedariyên bijîjkî',
                'slug' => 'pharmacy',
                'category' => 'healthcare',
                'icon' => 'fa-pills',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name_en' => 'Gas Station',
                'name_ar' => 'محطة وقود',
                'name_ku' => 'Stasyona Benzînê',
                'description_en' => 'Fuel station for vehicles',
                'description_ar' => 'محطة وقود للمركبات',
                'description_ku' => 'Stasyona benzînê ji bo otomobîlan',
                'slug' => 'gas-station',
                'category' => 'automotive',
                'icon' => 'fa-gas-pump',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name_en' => 'Bank/ATM',
                'name_ar' => 'بنك/صراف آلي',
                'name_ku' => 'Bank/ATM',
                'description_en' => 'Banking services and ATM access',
                'description_ar' => 'خدمات مصرفية ووصول للصراف الآلي',
                'description_ku' => 'Karûbarên bankê û gihîştina ATM-ê',
                'slug' => 'bank-atm',
                'category' => 'financial',
                'icon' => 'fa-university',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name_en' => 'Gym/Fitness Center',
                'name_ar' => 'صالة رياضية/مركز لياقة',
                'name_ku' => 'Salona Werzişê/Navenda Fitness',
                'description_en' => 'Fitness and exercise facilities',
                'description_ar' => 'مرافق اللياقة البدنية والتمارين',
                'description_ku' => 'Cîhazên fitness û werzişê',
                'slug' => 'gym-fitness-center',
                'category' => 'fitness',
                'icon' => 'fa-dumbbell',
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name_en' => 'Beauty Salon',
                'name_ar' => 'صالون تجميل',
                'name_ku' => 'Salona Bedewiyê',
                'description_en' => 'Hair and beauty services',
                'description_ar' => 'خدمات الشعر والتجميل',
                'description_ku' => 'Karûbarên por û bedewiyê',
                'slug' => 'beauty-salon',
                'category' => 'beauty',
                'icon' => 'fa-cut',
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name_en' => 'Car Wash',
                'name_ar' => 'غسيل سيارات',
                'name_ku' => 'Şuştina Otomobîlan',
                'description_en' => 'Vehicle cleaning services',
                'description_ar' => 'خدمات تنظيف المركبات',
                'description_ku' => 'Karûbarên paqijkirina otomobîlan',
                'slug' => 'car-wash',
                'category' => 'automotive',
                'icon' => 'fa-car',
                'is_active' => true,
                'sort_order' => 6
            ]
        ];

        foreach ($advancedUtilities as $utility) {
            Utility::updateOrCreate(
                ['slug' => $utility['slug']],
                $utility
            );
        }
    }
}
