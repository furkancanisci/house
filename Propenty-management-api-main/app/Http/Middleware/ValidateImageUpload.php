<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ImageProcessingService;

class ValidateImageUpload
{
    protected $imageService;
    
    public function __construct(ImageProcessingService $imageService)
    {
        $this->imageService = $imageService;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $errors = [];
        $maxImages = config('images.upload.max_images_per_property', 20);
        $maxFileSize = config('images.upload.max_file_size', 10 * 1024 * 1024);
        $totalImages = 0;
        
        // Check main image (support both camelCase and snake_case)
        $mainImageField = $request->hasFile('main_image') ? 'main_image' : 
                         ($request->hasFile('mainImage') ? 'mainImage' : null);
        
        if ($mainImageField) {
            $mainImage = $request->file($mainImageField);
            $totalImages += 1;
            
            // Validate main image
            $validationErrors = $this->imageService->validateImage($mainImage);
            if (!empty($validationErrors)) {
                $errors[$mainImageField] = $validationErrors;
            }
        }

        // Check gallery images
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $images = is_array($images) ? $images : [$images];
            $totalImages += count($images);
            
            // Validate each image
            foreach ($images as $index => $image) {
                if ($image) {
                    $validationErrors = $this->imageService->validateImage($image);
                    if (!empty($validationErrors)) {
                        $errors["images.{$index}"] = $validationErrors;
                    }
                }
            }
        }

        // Check base64 images
        if ($request->has('base64_images')) {
            $base64Images = $request->base64_images;
            if (is_array($base64Images)) {
                $totalImages += count($base64Images);
                
                // Validate base64 images
                foreach ($base64Images as $index => $base64Data) {
                    if (!preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $base64Data)) {
                        $errors["base64_images.{$index}"] = ['Invalid base64 image format'];
                    } else {
                        // Check approximate size
                        $sizeInBytes = strlen(base64_decode(substr($base64Data, strpos($base64Data, ',') + 1)));
                        if ($sizeInBytes > $maxFileSize) {
                            $errors["base64_images.{$index}"] = [
                                'Image size exceeds maximum allowed size of ' . ($maxFileSize / 1024 / 1024) . 'MB'
                            ];
                        }
                    }
                }
            }
        }

        // Check total image count
        if ($totalImages > $maxImages) {
            $errors['total_images'] = [
                "Maximum {$maxImages} images allowed per property. You uploaded {$totalImages} images."
            ];
        }
        
        // Return errors if any
        if (!empty($errors)) {
            return response()->json([
                'message' => 'Image validation failed',
                'errors' => $errors
            ], 422);
        }

        return $next($request);
    }
}