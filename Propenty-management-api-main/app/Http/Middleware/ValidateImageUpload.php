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
        // Check if request has image uploads
        if ($request->hasFile('main_image') || $request->hasFile('images') || $request->has('base64_images')) {
            $maxImages = config('images.upload.max_images_per_property', 20);
            $totalImages = 0;

            // Count main image
            if ($request->hasFile('main_image')) {
                $totalImages += 1;
            }

            // Count regular images
            if ($request->hasFile('images')) {
                $totalImages += count($request->file('images'));
            }

            // Count base64 images
            if ($request->has('base64_images') && is_array($request->base64_images)) {
                $totalImages += count($request->base64_images);
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