<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): Response
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = (int) $maxAttempts;
        $decayMinutes = (int) $decayMinutes;
        
        // Get current attempt count
        $attempts = Cache::get($key, 0);
        
        // Check if rate limit exceeded
        if ($attempts >= $maxAttempts) {
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts
            ]);
            
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $decayMinutes * 60
            ], 429);
        }
        
        // Increment attempt count
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        
        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);
        
        return $response;
    }
    
    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $user = $request->user();
        
        if ($user) {
            // For authenticated users, use user ID
            return 'rate_limit:user:' . $user->id . ':' . $request->path();
        }
        
        // For guests, use IP address and user agent
        return 'rate_limit:ip:' . $request->ip() . ':' . md5($request->userAgent()) . ':' . $request->path();
    }
    
    /**
     * Get the cache key for the given request.
     */
    protected function getCacheKey(Request $request, string $suffix = ''): string
    {
        $signature = $this->resolveRequestSignature($request);
        
        return $suffix ? $signature . ':' . $suffix : $signature;
    }
}