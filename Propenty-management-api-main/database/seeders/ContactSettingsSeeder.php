<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'phone',
                'label' => 'Phone Number',
                'value' => '+1 (555) 123-4567',
                'type' => 'phone',
                'description' => 'Main contact phone number displayed on the contact page',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'email',
                'label' => 'Email Address',
                'value' => 'info@realestate.com',
                'type' => 'email',
                'description' => 'Main contact email address displayed on the contact page',
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'address',
                'label' => 'Physical Address',
                'value' => '123 Main Street, City, State 12345',
                'type' => 'textarea',
                'description' => 'Physical address of the business',
                'is_required' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'business_hours',
                'label' => 'Business Hours',
                'value' => "Monday - Friday: 9:00 AM - 6:00 PM\nSaturday: 10:00 AM - 4:00 PM\nSunday: Closed",
                'type' => 'textarea',
                'description' => 'Operating hours of the business',
                'is_required' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'whatsapp',
                'label' => 'WhatsApp Number',
                'value' => '+1 (555) 123-4567',
                'type' => 'phone',
                'description' => 'WhatsApp contact number (optional)',
                'is_required' => false,
                'sort_order' => 5,
            ],
            [
                'key' => 'website',
                'label' => 'Website URL',
                'value' => 'https://realestate.com',
                'type' => 'url',
                'description' => 'Company website URL (optional)',
                'is_required' => false,
                'sort_order' => 6,
            ],
            [
                'key' => 'facebook',
                'label' => 'Facebook URL',
                'value' => null,
                'type' => 'url',
                'description' => 'Facebook page URL (optional)',
                'is_required' => false,
                'sort_order' => 7,
            ],
            [
                'key' => 'twitter',
                'label' => 'Twitter URL',
                'value' => null,
                'type' => 'url',
                'description' => 'Twitter profile URL (optional)',
                'is_required' => false,
                'sort_order' => 8,
            ],
            [
                'key' => 'linkedin',
                'label' => 'LinkedIn URL',
                'value' => null,
                'type' => 'url',
                'description' => 'LinkedIn profile URL (optional)',
                'is_required' => false,
                'sort_order' => 9,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('contact_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'is_displayed' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
