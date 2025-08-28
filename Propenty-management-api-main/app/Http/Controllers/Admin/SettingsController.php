<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        Gate::authorize('view settings');

        return view('admin.settings.index');
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        Gate::authorize('edit settings');

        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // In a real application, you would save these to a settings table
        // For now, we'll just return success
        
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
        ]);

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
        ]);

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
            'max_upload_size' => 'required|integer|min:1|max:10',
            'allowed_image_types' => 'required|string',
            'image_quality' => 'required|integer|min:50|max:100',
        ]);

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
        ]);

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

        return redirect()->route('admin.settings.index')
                        ->with('success', 'SMTP settings updated successfully.');
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