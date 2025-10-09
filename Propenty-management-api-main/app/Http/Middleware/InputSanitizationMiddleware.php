<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InputSanitizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip sanitization for authentication and property endpoints to allow proper validation
        $skipPaths = [
            // v1 routes
            'api/v1/auth/login',
            'api/v1/auth/register',
            'api/v1/auth/me',
            'api/v1/auth/logout',
            'api/v1/properties',
            'api/v1/properties/featured',
            'api/v1/features',
            'api/v1/utilities',
            'api/v1/cities/states',
            'api/v1/property-document-types',
            'api/v1/property-types/options',
            'api/v1/building-types/options',
            'api/v1/window-types/options',
            'api/v1/floor-types/options',
            'api/v1/view-types/options',
            'api/v1/directions/options',
            'api/home-stats',
            'api/v1/home-stats',
            'admin/home-stats',
            // Backward compatibility routes (without v1 prefix)
            'api/auth/login',
            'api/auth/register',
            'api/auth/me',
            'api/auth/logout',
            'api/properties',
            'api/properties/featured',
            'api/features',
            'api/utilities',
            'api/locations/states',
            'api/locations/cities',
            'api/property-types/options',
        ];
        $currentPath = trim($request->path(), '/');

        // Also skip if path starts with api/v1/properties/ (for specific property endpoints)
        $isPropertyEndpoint = str_starts_with($currentPath, 'api/v1/properties/') ||
                              str_starts_with($currentPath, 'api/properties/');

        // Also skip if path starts with api/v1/cities/state/ (for state-specific city endpoints)
        $isCityStateEndpoint = str_starts_with($currentPath, 'api/v1/cities/state/');

        // Also skip if path starts with api/v1/property-document-types/ (for specific document type endpoints)
        $isDocumentTypeEndpoint = str_starts_with($currentPath, 'api/v1/property-document-types/');

        // Also skip if path starts with api/v1/property-types/ (for specific property type endpoints)
        $isPropertyTypeEndpoint = str_starts_with($currentPath, 'api/v1/property-types/');

        // Also skip if path starts with api/v1/building-types/ (for specific building type endpoints)
        $isBuildingTypeEndpoint = str_starts_with($currentPath, 'api/v1/building-types/');

        // Also skip if path starts with api/v1/window-types/ (for specific window type endpoints)
        $isWindowTypeEndpoint = str_starts_with($currentPath, 'api/v1/window-types/');

        // Also skip if path starts with api/v1/floor-types/ (for specific floor type endpoints)
        $isFloorTypeEndpoint = str_starts_with($currentPath, 'api/v1/floor-types/');

        // Also skip if path starts with api/v1/view-types/ (for specific view type endpoints)
        $isViewTypeEndpoint = str_starts_with($currentPath, 'api/v1/view-types/');

        // Also skip if path starts with api/v1/directions/ (for specific direction endpoints)
        $isDirectionEndpoint = str_starts_with($currentPath, 'api/v1/directions/');

        // Also skip admin home-stats paths to allow special characters in labels
        $isAdminHomeStatsEndpoint = str_starts_with($currentPath, 'admin/home-stats') || $currentPath === 'admin/home-stats';

        // Also skip API home-stats paths
        $isApiHomeStatsEndpoint = str_starts_with($currentPath, 'api/home-stats') || $currentPath === 'api/home-stats' ||
                                  str_starts_with($currentPath, 'api/v1/home-stats') || $currentPath === 'api/v1/home-stats';

        if (!in_array($currentPath, $skipPaths) &&
            !$isPropertyEndpoint &&
            !$isCityStateEndpoint &&
            !$isDocumentTypeEndpoint &&
            !$isPropertyTypeEndpoint &&
            !$isBuildingTypeEndpoint &&
            !$isWindowTypeEndpoint &&
            !$isFloorTypeEndpoint &&
            !$isViewTypeEndpoint &&
            !$isDirectionEndpoint &&
            !$isAdminHomeStatsEndpoint &&
            !$isApiHomeStatsEndpoint) {
            // Sanitize input data
            $this->sanitizeInput($request);
            
            // Check for suspicious patterns
            if ($this->containsSuspiciousContent($request)) {
                Log::warning('Suspicious input detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'path' => $request->path(),
                    'input' => $request->all()
                ]);
                
                return response()->json([
                    'message' => 'Invalid input detected. Please check your data and try again.',
                ], 400);
            }
        }
        
        return $next($request);
    }
    
    /**
     * Sanitize input data recursively.
     */
    protected function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        $sanitized = $this->sanitizeArray($input);
        $request->replace($sanitized);
    }
    
    /**
     * Sanitize array data recursively.
     */
    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize string input.
     */
    protected function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Remove control characters except newlines and tabs
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Limit string length to prevent DoS attacks
        if (strlen($value) > 10000) {
            $value = substr($value, 0, 10000);
        }
        
        return $value;
    }
    
    /**
     * Check for suspicious content patterns.
     */
    protected function containsSuspiciousContent(Request $request): bool
    {
        $input = json_encode($request->all());
        $currentPath = trim($request->path(), '/');

        // Skip suspicious content check for home-stats paths completely
        if (str_contains($currentPath, 'home-stats')) {
            return false;
        }

        // SQL injection patterns
        $sqlPatterns = [
            '/\b(union|select|insert|update|delete|drop|create|alter|exec|execute)\s+/i',
            '/\b(or|and)\s+\d+\s*=\s*\d+/i',
            '/[\'"]\s*(or|and)\s+[\'"]\s*[\'"]\s*=/i',
            '/\b(sleep|benchmark|waitfor)\s*\(/i',
        ];
        
        // XSS patterns
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript\s*:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/<object[^>]*>.*?<\/object>/is',
            '/<embed[^>]*>/i',
        ];
        
        // Path traversal patterns
        $pathTraversalPatterns = [
            '/\.\.\//',
            '/\.\.\\\\\//',
            '/%2e%2e%2f/i',
            '/%2e%2e%5c/i',
        ];
        
        // Command injection patterns
        // Note: Removed & from pattern as it's part of normal query strings
        $commandInjectionPatterns = [
            '/[;|`$()]/i',
            '/\b(cat|ls|pwd|whoami|id|uname|wget|curl|nc|netcat)\b/i',
        ];
        
        $allPatterns = array_merge($sqlPatterns, $xssPatterns, $pathTraversalPatterns, $commandInjectionPatterns);
        
        foreach ($allPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
}