<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|in:en,ar'
        ]);
        
        $locale = $request->input('locale');
        
        // Store the locale in session
        Session::put('locale', $locale);
        
        // If user is authenticated, update their preferred language
        if (auth()->check()) {
            auth()->user()->update([
                'preferred_language' => $locale
            ]);
        }
        
        // Redirect back to the previous page
        return Redirect::back()->with('success', __('admin.language') . ' ' . __('admin.updated_successfully'));
    }
    
    /**
     * Get current locale information
     */
    public function current()
    {
        return response()->json([
            'current_locale' => app()->getLocale(),
            'available_locales' => $this->getAvailableLocales(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr'
        ]);
    }
    
    /**
     * Get available locales with their display names
     */
    private function getAvailableLocales(): array
    {
        return [
            'en' => [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'flag' => '🇺🇸'
            ],
            'ar' => [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'flag' => '🇸🇦'
            ]
        ];
    }
}