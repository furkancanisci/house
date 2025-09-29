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
     * Validate video file
     */
    protected function validateVideo($video)
    {
        $errors = [];
        $maxVideoSize = config('videos.upload.max_file_size', 500 * 1024 * 1024); // 500MB default
        $allowedMimeTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/webm'];
        
        // Check file size
        if ($video->getSize() > $maxVideoSize) {
            $errors[] = 'Video size exceeds maximum allowed size of ' . ($maxVideoSize / 1024 / 1024) . 'MB';
        }
        
        // Check MIME type
        if (!in_array($video->getMimeType(), $allowedMimeTypes)) {
            $errors[] = 'Invalid video format. Allowed formats: MP4, AVI, MOV, WMV, WEBM';
        }
        
        return $errors;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $errors = [];
            $maxImages = config('images.upload.max_images_per_property', 20);
            $maxFileSize = config('images.upload.max_file_size', 10 * 1024 * 1024);
            $totalImages = 0;
            
            \Illuminate\Support\Facades\Log::info('ValidateImageUpload middleware started', [
                'request_files' => array_keys($request->allFiles()),
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type')
            ]);
            
            // Check main image (support both camelCase and snake_case)
            $mainImageField = $request->hasFile('main_image') ? 'main_image' : 
                             ($request->hasFile('mainImage') ? 'mainImage' : null);
            
            if ($mainImageField) {
                $mainImage = $request->file($mainImageField);
                $totalImages += 1;
                
                \Illuminate\Support\Facades\Log::info('Validating main image', [
                    'field' => $mainImageField,
                    'original_name' => $mainImage->getClientOriginalName(),
                    'size' => $mainImage->getSize(),
                    'mime_type' => $mainImage->getMimeType()
                ]);
                
                // Validate main image
                try {
                    $validationErrors = $this->imageService->validateImage($mainImage);
                    if (!empty($validationErrors)) {
                        $errors[$mainImageField] = $validationErrors;
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error validating main image', [
                        'field' => $mainImageField,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errors[$mainImageField] = ['Image validation failed: ' . $e->getMessage()];
                }
            }

            // Check gallery images
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $images = is_array($images) ? $images : [$images];
                $totalImages += count($images);
                
                \Illuminate\Support\Facades\Log::info('Validating gallery images', [
                    'count' => count($images)
                ]);
                
                // Validate each image
                foreach ($images as $index => $image) {
                    if ($image) {
                        try {
                            $validationErrors = $this->imageService->validateImage($image);
                            if (!empty($validationErrors)) {
                                $errors["images.{$index}"] = $validationErrors;
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error validating gallery image', [
                                'index' => $index,
                                'original_name' => $image->getClientOriginalName(),
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            $errors["images.{$index}"] = ['Image validation failed: ' . $e->getMessage()];
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

        // Check videos
        if ($request->hasFile('videos')) {
            $videos = $request->file('videos');
            $videos = is_array($videos) ? $videos : [$videos];
            $maxVideos = config('videos.upload.max_videos_per_property', 1);
            
            // Check video count
            if (count($videos) > $maxVideos) {
                $errors['videos'] = [
                    "Maximum {$maxVideos} videos allowed per property. You uploaded " . count($videos) . " videos."
                ];
            } else {
                // Validate each video
                foreach ($videos as $index => $video) {
                    if ($video) {
                        $validationErrors = $this->validateVideo($video);
                        if (!empty($validationErrors)) {
                            $errors["videos.{$index}"] = $validationErrors;
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
                \Illuminate\Support\Facades\Log::info('Media validation failed', [
                    'errors' => $errors
                ]);
                return response()->json([
                    'message' => 'Media validation failed',
                    'errors' => $errors
                ], 422);
            }

            \Illuminate\Support\Facades\Log::info('ValidateImageUpload middleware completed successfully');
            return $next($request);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ValidateImageUpload middleware failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_files' => array_keys($request->allFiles()),
                'request_method' => $request->method()
            ]);
            
            return response()->json([
                'message' => 'Media validation failed due to server error',
                'error' => 'Please try again or contact support if the problem persists'
            ], 500);
        }
    }
}