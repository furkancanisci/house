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
        try {
            // Clear existing data
            DB::table('price_types')->truncate();
            
            $now = now();
            
            $priceTypes = [
                [
                    'name_ar' => 'سنوي',
                    'name_en' => 'Yearly',
                    'name_ku' => 'Salane',
                    'key' => 'yearly',
                    'listing_type' => 'both',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name_ar' => 'شهري',
                    'name_en' => 'Monthly',
                    'name_ku' => 'Mehane',
                    'key' => 'monthly',
                    'listing_type' => 'both',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name_ar' => 'يومي',
                    'name_en' => 'Daily',
                    'name_ku' => 'Rojane',
                    'key' => 'daily',
                    'listing_type' => 'both',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name_ar' => 'أسبوعي',
                    'name_en' => 'Weekly',
                    'name_ku' => 'Heftane',
                    'key' => 'weekly',
                    'listing_type' => 'both',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
            
            // Use bulk insert for better performance
            DB::table('price_types')->insert($priceTypes);
            
            $this->command->info('Price types seeded successfully!');
            
        } catch (\Exception $e) {
            $this->command->error('Failed to seed price types: ' . $e->getMessage());
            throw $e;
        }
    }
}
