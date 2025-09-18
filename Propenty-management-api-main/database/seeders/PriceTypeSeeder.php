<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PriceType;
use Illuminate\Support\Facades\DB;

class PriceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('price_types')->truncate();
        
        $priceTypes = [
            [
                'name_ar' => 'قابل للتفاوض',
                'name_en' => 'Negotiable',
                'name_ku' => 'Danûstandinî',
                'key' => 'negotiable',
                'listing_type' => 'both',
                'is_active' => true,
            ],
            [
                'name_ar' => 'سعر نهائي',
                'name_en' => 'Final Price',
                'name_ku' => 'Nirxa Dawîn',
                'key' => 'final_price',
                'listing_type' => 'both',
                'is_active' => true,
            ],
            [
                'name_ar' => 'مثل شعبي (فوقو سكرا)',
                'name_en' => 'Popular Saying (Above Sugar)',
                'name_ku' => 'Gotina Gel (Li ser Şekir)',
                'key' => 'popular_saying',
                'listing_type' => 'both',
                'is_active' => true,
            ],
            [
                'name_ar' => 'سعر من الآخر',
                'name_en' => 'Price From Last',
                'name_ku' => 'Nirx ji Dawîyê',
                'key' => 'price_from_last',
                'listing_type' => 'both',
                'is_active' => true,
            ],
            [
                'name_ar' => 'شهري',
                'name_en' => 'Monthly',
                'name_ku' => 'Mehane',
                'key' => 'monthly',
                'listing_type' => 'rent',
                'is_active' => true,
            ],
            [
                'name_ar' => 'سنوي',
                'name_en' => 'Yearly',
                'name_ku' => 'Salane',
                'key' => 'yearly',
                'listing_type' => 'rent',
                'is_active' => true,
            ],
            [
                'name_ar' => 'السعر الإجمالي',
                'name_en' => 'Total Price',
                'name_ku' => 'Nirxa Giştî',
                'key' => 'total',
                'listing_type' => 'sale',
                'is_active' => true,
            ],
            [
                'name_ar' => 'سعر ثابت',
                'name_en' => 'Fixed Price',
                'name_ku' => 'Nirxa Sabît',
                'key' => 'fixed',
                'listing_type' => 'both',
                'is_active' => true,
            ],
        ];
        
        foreach ($priceTypes as $priceType) {
            PriceType::create($priceType);
        }
        
        $this->command->info('Price types seeded successfully!');
    }
}
