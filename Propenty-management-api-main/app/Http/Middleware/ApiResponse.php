<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set headers for API responses
        $response = $next($request);

        // Add API-specific headers
        $response->headers->set('X-API-Version', '1.0.0');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // If it's a JSON response, ensure proper content type
        if ($response->headers->get('Content-Type') === null && 
            ($request->wantsJson() || $request->expectsJson() || $request->is('api/*'))) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
