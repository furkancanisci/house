<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the locale from request, session, or use default
        $locale = $this->getLocale($request);
        
        // Set the application locale
        App::setLocale($locale);
        
        // Store the locale in session for future requests
        Session::put('locale', $locale);
        
        // Set the locale for the current request
        $request->attributes->set('locale', $locale);
        
        return $next($request);
    }
    
    /**
     * Get the locale from various sources
     */
    private function getLocale(Request $request): string
    {
        // 1. Check if locale is explicitly set in request (URL parameter or form)
        if ($request->has('locale') && $this->isValidLocale($request->get('locale'))) {
            return $request->get('locale');
        }
        
        // 2. Check session for previously set locale
        if (Session::has('locale') && $this->isValidLocale(Session::get('locale'))) {
            return Session::get('locale');
        }
        
        // 3. Check user preference if authenticated
        if (auth()->check() && auth()->user()->preferred_language && $this->isValidLocale(auth()->user()->preferred_language)) {
            return auth()->user()->preferred_language;
        }
        
        // 4. Check browser language preference
        $browserLocale = $this->getBrowserLocale($request);
        if ($browserLocale && $this->isValidLocale($browserLocale)) {
            return $browserLocale;
        }
        
        // 5. Fall back to default locale
        return config('app.locale', 'en');
    }
    
    /**
     * Check if the given locale is supported
     */
    private function isValidLocale(string $locale): bool
    {
        return in_array($locale, config('app.supported_locales', ['en', 'ar']));
    }
    
    /**
     * Get browser preferred language
     */
    private function getBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }
        
        // Parse Accept-Language header
        $languages = [];
        preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)\s*(?:;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptLanguage, $matches);
        
        if (count($matches[1])) {
            $languages = array_combine($matches[1], $matches[2]);
            
            foreach ($languages as $lang => $priority) {
                $lang = strtolower(substr($lang, 0, 2)); // Get first two characters
                
                if ($this->isValidLocale($lang)) {
                    return $lang;
                }
            }
        }
        
        return null;
    }
}