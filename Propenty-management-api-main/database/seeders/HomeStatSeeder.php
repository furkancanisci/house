<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HomeStat;

class HomeStatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stats = [
            [
                'key' => 'properties_listed',
                'icon' => 'HomeIcon',
                'number' => '1000+',
                'label_ar' => 'عقارات مدرجة',
                'label_en' => 'Properties Listed',
                'label_ku' => 'Xanî Lîstekirî',
                'color' => 'text-primary-600',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'happy_customers',
                'icon' => 'Users',
                'number' => '500+',
                'label_ar' => 'عملاء سعداء',
                'label_en' => 'Happy Customers',
                'label_ku' => 'Xerîdarên Kêfxweş',
                'color' => 'text-primary-700',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'key' => 'success_rate',
                'icon' => 'TrendingUp',
                'number' => '95%',
                'label_ar' => 'معدل النجاح',
                'label_en' => 'Success Rate',
                'label_ku' => 'Rêjeya Serkeftinê',
                'color' => 'text-primary-800',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'key' => 'years_experience',
                'icon' => 'Award',
                'number' => '10+',
                'label_ar' => 'سنوات خبرة',
                'label_en' => 'Years Experience',
                'label_ku' => 'Salên Ezmûnê',
                'color' => 'text-primary-500',
                'order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($stats as $stat) {
            HomeStat::updateOrCreate(
                ['key' => $stat['key']],
                $stat
            );
        }

        $this->command->info('Home statistics seeded successfully!');
    }
}
