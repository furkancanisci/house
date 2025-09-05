<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Property Management System',
                'type' => 'string',
                'description' => 'The name of your website',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'site_description',
                'value' => 'Professional property management and real estate platform',
                'type' => 'string',
                'description' => 'Brief description of your website',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'contact_email',
                'value' => 'admin@property.com',
                'type' => 'string',
                'description' => 'Main contact email address',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'contact_phone',
                'value' => '+1-234-567-8900',
                'type' => 'string',
                'description' => 'Main contact phone number',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'address',
                'value' => '123 Main Street, City, State 12345',
                'type' => 'string',
                'description' => 'Business address',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'timezone',
                'value' => 'UTC',
                'type' => 'string',
                'description' => 'Default timezone',
                'is_public' => false,
            ],
            [
                'group' => 'general',
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Default currency code',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'description' => 'Currency symbol',
                'is_public' => true,
            ],
            [
                'group' => 'general',
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode',
                'is_public' => true,
            ],

            // Listing Settings
            [
                'group' => 'listings',
                'key' => 'listings_per_page',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Number of listings to display per page',
                'is_public' => true,
            ],
            [
                'group' => 'listings',
                'key' => 'auto_approve_listings',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically approve new listings',
                'is_public' => false,
            ],
            [
                'group' => 'listings',
                'key' => 'featured_listings_limit',
                'value' => '8',
                'type' => 'integer',
                'description' => 'Maximum number of featured listings on homepage',
                'is_public' => true,
            ],
            [
                'group' => 'listings',
                'key' => 'max_images_per_listing',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Maximum images allowed per listing',
                'is_public' => false,
            ],
            [
                'group' => 'listings',
                'key' => 'allow_guest_inquiries',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Allow non-registered users to make inquiries',
                'is_public' => true,
            ],

            // SEO Settings
            [
                'group' => 'seo',
                'key' => 'meta_title',
                'value' => 'Property Management - Find Your Dream Home',
                'type' => 'string',
                'description' => 'Default meta title for SEO',
                'is_public' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'meta_description',
                'value' => 'Discover the best properties for rent and sale. Professional property management services with modern amenities and prime locations.',
                'type' => 'string',
                'description' => 'Default meta description for SEO',
                'is_public' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'meta_keywords',
                'value' => 'property, real estate, rental, house, apartment, management',
                'type' => 'string',
                'description' => 'Default meta keywords for SEO',
                'is_public' => true,
            ],
            [
                'group' => 'seo',
                'key' => 'google_analytics_id',
                'value' => '',
                'type' => 'string',
                'description' => 'Google Analytics tracking ID',
                'is_public' => false,
            ],
            [
                'group' => 'seo',
                'key' => 'google_tag_manager_id',
                'value' => '',
                'type' => 'string',
                'description' => 'Google Tag Manager ID',
                'is_public' => false,
            ],

            // Media Settings
            [
                'group' => 'media',
                'key' => 'max_upload_size',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Maximum file upload size in MB',
                'is_public' => false,
            ],
            [
                'group' => 'media',
                'key' => 'allowed_image_types',
                'value' => 'jpeg,jpg,png,gif,webp',
                'type' => 'string',
                'description' => 'Allowed image file extensions',
                'is_public' => false,
            ],
            [
                'group' => 'media',
                'key' => 'image_quality',
                'value' => '85',
                'type' => 'integer',
                'description' => 'Image compression quality (1-100)',
                'is_public' => false,
            ],
            [
                'group' => 'media',
                'key' => 'generate_thumbnails',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Automatically generate image thumbnails',
                'is_public' => false,
            ],
            [
                'group' => 'media',
                'key' => 'watermark_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable watermarks on uploaded images',
                'is_public' => false,
            ],

            // Maps Settings
            [
                'group' => 'maps',
                'key' => 'google_maps_api_key',
                'value' => '',
                'type' => 'string',
                'description' => 'Google Maps API key for map functionality',
                'is_public' => false,
            ],
            [
                'group' => 'maps',
                'key' => 'default_latitude',
                'value' => '40.7128',
                'type' => 'float',
                'description' => 'Default map center latitude',
                'is_public' => true,
            ],
            [
                'group' => 'maps',
                'key' => 'default_longitude',
                'value' => '-74.0060',
                'type' => 'float',
                'description' => 'Default map center longitude',
                'is_public' => true,
            ],
            [
                'group' => 'maps',
                'key' => 'default_zoom',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Default map zoom level',
                'is_public' => true,
            ],
            [
                'group' => 'maps',
                'key' => 'enable_street_view',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable Google Street View on property pages',
                'is_public' => true,
            ],

            // SMTP Settings
            [
                'group' => 'smtp',
                'key' => 'mail_host',
                'value' => 'smtp.gmail.com',
                'type' => 'string',
                'description' => 'SMTP server host',
                'is_public' => false,
            ],
            [
                'group' => 'smtp',
                'key' => 'mail_port',
                'value' => '587',
                'type' => 'integer',
                'description' => 'SMTP server port',
                'is_public' => false,
            ],
            [
                'group' => 'smtp',
                'key' => 'mail_username',
                'value' => '',
                'type' => 'string',
                'description' => 'SMTP username',
                'is_public' => false,
            ],
            [
                'group' => 'smtp',
                'key' => 'mail_password',
                'value' => '',
                'type' => 'string',
                'description' => 'SMTP password',
                'is_public' => false,
            ],
            [
                'group' => 'smtp',
                'key' => 'mail_encryption',
                'value' => 'tls',
                'type' => 'string',
                'description' => 'SMTP encryption method (tls/ssl)',
                'is_public' => false,
            ],
            [
                'group' => 'smtp',
                'key' => 'mail_from_address',
                'value' => 'noreply@property.com',
                'type' => 'string',
                'description' => 'Default sender email address',
                'is_public' => false,
            ],
            [
                'group' => 'smtp',
                'key' => 'mail_from_name',
                'value' => 'Property Management',
                'type' => 'string',
                'description' => 'Default sender name',
                'is_public' => false,
            ],

            // Social Media Settings
            [
                'group' => 'social',
                'key' => 'facebook_url',
                'value' => '',
                'type' => 'string',
                'description' => 'Facebook page URL',
                'is_public' => true,
            ],
            [
                'group' => 'social',
                'key' => 'twitter_url',
                'value' => '',
                'type' => 'string',
                'description' => 'Twitter profile URL',
                'is_public' => true,
            ],
            [
                'group' => 'social',
                'key' => 'instagram_url',
                'value' => '',
                'type' => 'string',
                'description' => 'Instagram profile URL',
                'is_public' => true,
            ],
            [
                'group' => 'social',
                'key' => 'linkedin_url',
                'value' => '',
                'type' => 'string',
                'description' => 'LinkedIn page URL',
                'is_public' => true,
            ],
            [
                'group' => 'social',
                'key' => 'youtube_url',
                'value' => '',
                'type' => 'string',
                'description' => 'YouTube channel URL',
                'is_public' => true,
            ],

            // Security Settings
            [
                'group' => 'security',
                'key' => 'enable_recaptcha',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable Google reCAPTCHA on forms',
                'is_public' => false,
            ],
            [
                'group' => 'security',
                'key' => 'recaptcha_site_key',
                'value' => '',
                'type' => 'string',
                'description' => 'Google reCAPTCHA site key',
                'is_public' => true,
            ],
            [
                'group' => 'security',
                'key' => 'recaptcha_secret_key',
                'value' => '',
                'type' => 'string',
                'description' => 'Google reCAPTCHA secret key',
                'is_public' => false,
            ],
            [
                'group' => 'security',
                'key' => 'max_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Maximum login attempts before lockout',
                'is_public' => false,
            ],
            [
                'group' => 'security',
                'key' => 'lockout_duration',
                'value' => '900',
                'type' => 'integer',
                'description' => 'Account lockout duration in seconds',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                [
                    'key' => $setting['key'],
                    'group' => $setting['group']
                ],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully!');
    }
}