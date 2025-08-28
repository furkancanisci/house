<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateImageUpload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if request has image uploads (support both camelCase and snake_case)
        $hasMainImage = $request->hasFile('main_image') || $request->hasFile('mainImage');
        $hasImages = $request->hasFile('images');
        $hasBase64Images = $request->has('base64_images');
        
        if ($hasMainImage || $hasImages || $hasBase64Images) {
            $maxImages = config('images.upload.max_images_per_property', 20);
            $totalImages = 0;

            // Count main image (support both formats)
            if ($hasMainImage) {
                $totalImages += 1;
            }

            // Count regular images - ensure safe array handling
            if ($hasImages) {
                $images = $request->file('images');
                if (is_array($images)) {
                    $totalImages += count($images);
                } else {
                    // Single file uploaded
                    $totalImages += 1;
                }
            }

            // Count base64 images - ensure safe array handling
            if ($hasBase64Images) {
                $base64Images = $request->base64_images;
                if (is_array($base64Images) && !empty($base64Images)) {
                    $totalImages += count($base64Images);
                }
            }

            // Check if total exceeds limit
            if ($totalImages > $maxImages) {
                return response()->json([
                    'message' => 'Too many images uploaded',
                    'errors' => [
                        'images' => ["Maximum {$maxImages} images allowed per property. You uploaded {$totalImages} images."]
                    ]
                ], 422);
            }
        }

        return $next($request);
    }
}