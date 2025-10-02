<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\BunnyStorageService;

class ImageProcessingService
{
    protected $bunnyStorage;

    // Get quality settings from config
    private function getQualitySettings(): array
    {
        return config('images.quality_settings', [
            'full' => ['width' => 1200, 'height' => 800, 'quality' => 90, 'aspect_ratio' => '3:2'],
            'large' => ['width' => 800, 'height' => 533, 'quality' => 85, 'aspect_ratio' => '3:2'],
            'medium' => ['width' => 600, 'height' => 400, 'quality' => 80, 'aspect_ratio' => '3:2'],
            'thumbnail' => ['width' => 400, 'height' => 300, 'quality' => 75, 'aspect_ratio' => '4:3'],
            'small' => ['width' => 300, 'height' => 200, 'quality' => 70, 'aspect_ratio' => '3:2'],
        ]);
    }

    // Get supported formats from config
    private function getSupportedFormats(): array
    {
        return config('images.upload.allowed_extensions', ['jpeg', 'jpg', 'png', 'webp']);
    }
    
    // Get maximum file size from config
    private function getMaxFileSize(): int
    {
        return config('images.upload.max_file_size', 5 * 1024 * 1024);
    }

    public function __construct(BunnyStorageService $bunnyStorage)
    {
        $this->bunnyStorage = $bunnyStorage;
        // Configure Intervention Image to use GD driver
        Image::configure(['driver' => 'gd']);
    }

    /**
     * Validate uploaded image file
     */
    public function validateImage(UploadedFile $file): array
    {
        $errors = [];
        $supportedFormats = $this->getSupportedFormats();
        $maxFileSize = $this->getMaxFileSize();
        $maxWidth = config('images.upload.max_width', 8000);
        $maxHeight = config('images.upload.max_height', 6000);
        $allowedMimeTypes = config('images.upload.allowed_mime_types', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);

        // Check file size
        if ($file->getSize() > $maxFileSize) {
            $errors[] = 'File size exceeds maximum allowed size of ' . ($maxFileSize / 1024 / 1024) . 'MB. Please compress the image or reduce its dimensions.';
        }

        // Check file type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $supportedFormats)) {
            $errors[] = 'File type not supported. Allowed types: ' . implode(', ', $supportedFormats);
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimeTypes)) {
            $errors[] = 'Invalid MIME type. File must be a valid image.';
        }

        // Check dimensions
        try {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                
                // Minimum dimension check removed - only checking maximum file size
                
                // Check maximum dimensions
                if ($width > $maxWidth || $height > $maxHeight) {
                    $errors[] = "Image dimensions too large. Maximum size is {$maxWidth}x{$maxHeight} pixels. The image will be automatically resized.";
                }
            }
        } catch (\Exception $e) {
            $errors[] = 'Unable to read image dimensions.';
        }

        return $errors;
    }

    /**
     * Process and resize image to multiple sizes
     */
    public function processPropertyImage(UploadedFile $file, string $propertySlug): array
    {
        $processedImages = [];
        $originalImage = Image::make($file->getPathname());
        $qualitySettings = $this->getQualitySettings();
        $storagePath = config('images.storage.path', 'properties');
        $preserveQualityThreshold = config('images.upload.preserve_quality_threshold', 2 * 1024 * 1024);
        $optimizeLargeImages = config('images.upload.optimize_large_images', true);
        
        // Check if we should preserve original quality for smaller images
        $fileSize = $file->getSize();
        $shouldPreserveOriginal = $fileSize <= $preserveQualityThreshold;
        
        // Get original dimensions
        $originalWidth = $originalImage->width();
        $originalHeight = $originalImage->height();
        
        foreach ($qualitySettings as $size => $settings) {
            // For high-quality images under threshold, use higher quality settings
            if ($shouldPreserveOriginal && in_array($size, ['original', 'full'])) {
                $settings['quality'] = min($settings['quality'] + 3, 98); // Boost quality for showcase images
            }
            
            // Calculate dimensions maintaining aspect ratio
            $dimensions = $this->calculateDimensions(
                $originalWidth,
                $originalHeight,
                $settings['width'],
                $settings['height']
            );
            
            // Skip resize if the original is smaller than the target size
            if ($size === 'original' && 
                $originalWidth <= $settings['width'] && 
                $originalHeight <= $settings['height']) {
                // Keep original dimensions for highest quality
                $dimensions = [
                    'width' => $originalWidth,
                    'height' => $originalHeight
                ];
            }
            
            // Create a copy of the original image for processing
            $resizedImage = Image::make($file->getPathname());
            
            // Only resize if dimensions are different
            if ($dimensions['width'] != $originalWidth || 
                $dimensions['height'] != $originalHeight) {
                $resizedImage->resize($dimensions['width'], $dimensions['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Apply intelligent compression based on file size
            if ($optimizeLargeImages && $fileSize > 4 * 1024 * 1024) {
                // For very large files, apply interlace for progressive loading
                $resizedImage->interlace(true);
            }
            
            // Generate filename with appropriate format
            $format = $settings['format'] ?? 'jpg';
            // Intervention Image v2 doesn't support webp directly, use jpg as fallback
            if ($format === 'webp') {
                $format = 'jpg';
            }
            
            $filename = $propertySlug . '_' . $size . '_' . time() . '.' . $format;
            $path = $storagePath . '/' . $filename;
            $fullPath = storage_path('app/public/' . $path);
            
            // Ensure directory exists
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Save the image with the appropriate quality
            $resizedImage->save($fullPath, $settings['quality']);
            
            $processedImages[$size] = [
                'path' => $path,
                'url' => Storage::url($path),
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'size' => filesize($fullPath),
                'quality' => $settings['quality'],
                'format' => $format,
                'is_original_preserved' => $shouldPreserveOriginal && in_array($size, ['original', 'full'])
            ];
        }
        
        return $processedImages;
    }

    /**
     * Process base64 encoded image
     */
    public function processBase64Image(string $base64Data, string $propertySlug): array
    {
        $supportedFormats = $this->getSupportedFormats();
        $maxFileSize = $this->getMaxFileSize();
        
        // Extract image data and format
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            throw new \InvalidArgumentException('Invalid base64 image format');
        }
        
        $imageFormat = strtolower($matches[1]);
        if (!in_array($imageFormat, $supportedFormats)) {
            throw new \InvalidArgumentException('Unsupported image format');
        }
        
        // Decode base64 data
        $imageData = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
        if (!$imageData) {
            throw new \InvalidArgumentException('Failed to decode base64 image');
        }
        
        // Check file size
        if (strlen($imageData) > $maxFileSize) {
            throw new \InvalidArgumentException('Image size cannot exceed ' . ($maxFileSize / 1024 / 1024) . 'MB');
        }
        
        // Create temporary file
        $tempPath = tempnam(sys_get_temp_dir(), 'property_image_');
        file_put_contents($tempPath, $imageData);
        
        try {
            // Load and validate image using Intervention Image v2
            $image = Image::make($tempPath);
            
            // Minimum dimension check removed - only checking maximum file size
            
            $processedImages = [];
            $qualitySettings = $this->getQualitySettings();
            $storagePath = config('images.storage.path', 'properties');
            
            // Process each size variant
            foreach ($qualitySettings as $sizeName => $settings) {
                // Calculate dimensions maintaining aspect ratio
                $dimensions = $this->calculateDimensions(
                    $image->width(),
                    $image->height(),
                    $settings['width'],
                    $settings['height']
                );
                
                // Create resized image from temp file
                $resizedImage = Image::make($tempPath);
                $resizedImage->resize($dimensions['width'], $dimensions['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                
                // Apply format conversion - v2 doesn't support webp directly
                $format = $settings['format'] ?? 'jpg';
                if ($format === 'webp') {
                    $format = 'jpg';
                }
                
                // Generate filename
                $filename = $propertySlug . '_' . $sizeName . '_' . time() . '.' . $format;
                $path = $storagePath . '/' . $filename;
                $fullPath = storage_path('app/public/' . $path);
                
                // Ensure directory exists
                $directory = dirname($fullPath);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Save the image with quality setting
                $resizedImage->save($fullPath, $settings['quality']);
                
                $processedImages[$sizeName] = [
                    'path' => $path,
                    'url' => Storage::url($path),
                    'width' => $dimensions['width'],
                    'height' => $dimensions['height'],
                    'size' => filesize($fullPath),
                    'quality' => $settings['quality'],
                    'format' => $format
                ];
            }
            
            return $processedImages;
            
        } finally {
            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Calculate optimal dimensions maintaining aspect ratio
     */
    private function calculateDimensions(int $originalWidth, int $originalHeight, int $targetWidth, int $targetHeight): array
    {
        $originalRatio = $originalWidth / $originalHeight;
        $targetRatio = $targetWidth / $targetHeight;
        
        if ($originalRatio > $targetRatio) {
            // Original is wider, fit to width
            $width = $targetWidth;
            $height = round($targetWidth / $originalRatio);
        } else {
            // Original is taller, fit to height
            $height = $targetHeight;
            $width = round($targetHeight * $originalRatio);
        }
        
        return [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * Delete all variants of an image
     */
    public function deleteImageVariants(string $propertySlug, string $baseFileName): bool
    {
        $deleted = true;
        $qualitySettings = $this->getQualitySettings();
        
        foreach (array_keys($qualitySettings) as $sizeName) {
            $filePath = "properties/{$propertySlug}/{$baseFileName}_{$sizeName}.webp";
            if ($this->bunnyStorage->fileExists($filePath)) {
                $deleted = $this->bunnyStorage->deleteFile($filePath) && $deleted;
            }
        }
        
        return $deleted;
    }

    /**
     * Get image variants for a property
     */
    public function getImageVariants(string $propertySlug, string $baseFileName): array
    {
        $variants = [];
        $qualitySettings = $this->getQualitySettings();
        
        foreach ($qualitySettings as $sizeName => $settings) {
            $filePath = "properties/{$propertySlug}/{$baseFileName}_{$sizeName}.webp";
            if ($this->bunnyStorage->fileExists($filePath)) {
                $variants[$sizeName] = [
                    'path' => $filePath,
                    'url' => $this->bunnyStorage->getCdnUrl($filePath),
                    'size' => $settings
                ];
            }
        }
        
        return $variants;
    }

    /**
     * Clean up orphaned images for a property
     */
    public function cleanupPropertyImages(string $propertySlug): bool
    {
        $directory = "properties/{$propertySlug}";
        
        // Note: Bunny Storage doesn't have directory deletion, so we need to delete files individually
        // This is a simplified implementation - you might want to track files in database
        try {
            // You would typically get file list from your database here
            // For now, this is a placeholder that returns true
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}