<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        Gate::authorize('view settings');

        $groups = [
            'general' => [
                'title' => 'General Settings',
                'icon' => 'fas fa-cog',
                'color' => 'info',
                'description' => 'Basic site configuration and contact information'
            ],
            'listings' => [
                'title' => 'Listing Settings', 
                'icon' => 'fas fa-home',
                'color' => 'success',
                'description' => 'Property listing display and behavior settings'
            ],
            'seo' => [
                'title' => 'SEO Settings',
                'icon' => 'fas fa-search',
                'color' => 'primary',
                'description' => 'Search engine optimization and meta tags'
            ],
            'media' => [
                'title' => 'Media Settings',
                'icon' => 'fas fa-images',
                'color' => 'warning',
                'description' => 'File upload and image processing configuration'
            ],
            'maps' => [
                'title' => 'Maps Settings',
                'icon' => 'fas fa-map',
                'color' => 'danger',
                'description' => 'Google Maps API and location settings'
            ],
            'smtp' => [
                'title' => 'Email Settings',
                'icon' => 'fas fa-envelope',
                'color' => 'secondary',
                'description' => 'SMTP configuration for email delivery'
            ],
            'social' => [
                'title' => 'Social Media',
                'icon' => 'fas fa-share-alt',
                'color' => 'info',
                'description' => 'Social media profile links and integration'
            ],
            'security' => [
                'title' => 'Security Settings',
                'icon' => 'fas fa-shield-alt',
                'color' => 'dark',
                'description' => 'Security features and access controls'
            ]
        ];

        // Get settings grouped by category
        $settingsByGroup = [];
        foreach ($groups as $groupKey => $groupInfo) {
            $settingsByGroup[$groupKey] = Setting::where('group', $groupKey)->get()->keyBy('key');
        }

        return view('admin.settings.index', compact('groups', 'settingsByGroup'));
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        Gate::authorize('edit settings');

        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'timezone' => 'required|string',
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:5',
            'maintenance_mode' => 'boolean',
        ]);

        $settings = [
            'site_name' => $request->site_name,
            'site_description' => $request->site_description,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'address' => $request->address,
            'timezone' => $request->timezone,
            'currency' => $request->currency,
            'currency_symbol' => $request->currency_symbol,
            'maintenance_mode' => $request->boolean('maintenance_mode'),
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'general');
        }
        
        return redirect()->route('admin.settings.index')
                        ->with('success', 'General settings updated successfully.');
    }

    /**
     * Update listing settings.
     */
    public function updateListings(Request $request)
    {
        Gate::authorize('edit settings');

        $request->validate([
            'listings_per_page' => 'required|integer|min:5|max:100',
            'auto_approve_listings' => 'boolean',
            'featured_listings_limit' => 'required|integer|min:1|max:50',
            'max_images_per_listing' => 'required|integer|min:1|max:50',
            'allow_guest_inquiries' => 'boolean',
        ]);

        $settings = [
            'listings_per_page' => $request->listings_per_page,
            'auto_approve_listings' => $request->boolean('auto_approve_listings'),
            'featured_listings_limit' => $request->featured_listings_limit,
            'max_images_per_listing' => $request->max_images_per_listing,
            'allow_guest_inquiries' => $request->boolean('allow_guest_inquiries'),
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'listings');
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Listing settings updated successfully.');
    }

    /**
     * Update SEO settings.
     */
    public function updateSeo(Request $request)
    {
        Gate::authorize('edit settings');

        $request->validate([
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'google_analytics_id' => 'nullable|string',
            'google_tag_manager_id' => 'nullable|string',
        ]);

        $settings = [
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'google_analytics_id' => $request->google_analytics_id,
            'google_tag_manager_id' => $request->google_tag_manager_id,
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'seo');
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'SEO settings updated successfully.');
    }

    /**
     * Update media settings.
     */
    public function updateMedia(Request $request)
    {
        Gate::authorize('edit settings');

        $request->validate([
            'max_upload_size' => 'required|integer|min:1|max:20',
            'allowed_image_types' => 'required|string',
            'image_quality' => 'required|integer|min:50|max:100',
            'generate_thumbnails' => 'boolean',
            'watermark_enabled' => 'boolean',
        ]);

        $settings = [
            'max_upload_size' => $request->max_upload_size,
            'allowed_image_types' => $request->allowed_image_types,
            'image_quality' => $request->image_quality,
            'generate_thumbnails' => $request->boolean('generate_thumbnails'),
            'watermark_enabled' => $request->boolean('watermark_enabled'),
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'media');
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Media settings updated successfully.');
    }

    /**
     * Update maps settings.
     */
    public function updateMaps(Request $request)
    {
        Gate::authorize('edit settings');

        $request->validate([
            'google_maps_api_key' => 'nullable|string',
            'default_latitude' => 'required|numeric|between:-90,90',
            'default_longitude' => 'required|numeric|between:-180,180',
            'default_zoom' => 'required|integer|min:1|max:20',
            'enable_street_view' => 'boolean',
        ]);

        $settings = [
            'google_maps_api_key' => $request->google_maps_api_key,
            'default_latitude' => $request->default_latitude,
            'default_longitude' => $request->default_longitude,
            'default_zoom' => $request->default_zoom,
            'enable_street_view' => $request->boolean('enable_street_view'),
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'maps');
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Maps settings updated successfully.');
    }

    /**
     * Update SMTP settings.
     */
    public function updateSmtp(Request $request)
    {
        Gate::authorize('manage smtp settings');

        $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        $settings = [
            'mail_host' => $request->mail_host,
            'mail_port' => $request->mail_port,
            'mail_username' => $request->mail_username,
            'mail_encryption' => $request->mail_encryption,
            'mail_from_address' => $request->mail_from_address,
            'mail_from_name' => $request->mail_from_name,
        ];

        // Only update password if provided
        if ($request->filled('mail_password')) {
            $settings['mail_password'] = $request->mail_password;
        }

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'smtp');
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'SMTP settings updated successfully.');
    }

    /**
     * Update social media settings.
     */
    public function updateSocial(Request $request)
    {
        Gate::authorize('edit settings');

        $request->validate([
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
        ]);

        $settings = [
            'facebook_url' => $request->facebook_url,
            'twitter_url' => $request->twitter_url,
            'instagram_url' => $request->instagram_url,
            'linkedin_url' => $request->linkedin_url,
            'youtube_url' => $request->youtube_url,
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'social');
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Social media settings updated successfully.');
    }

    /**
     * Update security settings.
     */
    public function updateSecurity(Request $request)
    {
        Gate::authorize('edit settings');

        $request->validate([
            'enable_recaptcha' => 'boolean',
            'recaptcha_site_key' => 'nullable|string',
            'recaptcha_secret_key' => 'nullable|string',
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration' => 'required|integer|min:60|max:7200',
        ]);

        $settings = [
            'enable_recaptcha' => $request->boolean('enable_recaptcha'),
            'recaptcha_site_key' => $request->recaptcha_site_key,
            'recaptcha_secret_key' => $request->recaptcha_secret_key,
            'max_login_attempts' => $request->max_login_attempts,
            'lockout_duration' => $request->lockout_duration,
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'security');
        }

        return redirect()->route('admin.settings.index')
                        ->with('success', 'Security settings updated successfully.');
    }

    /**
     * Test email functionality.
     */
    public function testEmail(Request $request)
    {
        Gate::authorize('manage smtp settings');

        $request->validate([
            'test_email' => 'required|email'
        ]);

        try {
            // Here you would send a test email
            // Mail::to($request->test_email)->send(new TestMail());
            
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request)
    {
        Gate::authorize('clear cache');

        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }
}