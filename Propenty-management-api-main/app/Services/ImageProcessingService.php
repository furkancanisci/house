<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageProcessingService
{
    private ImageManager $imageManager;
    
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

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Validate uploaded image file
     */
    public function validateImage(UploadedFile $file): array
    {
        $errors = [];
        $maxFileSize = $this->getMaxFileSize();
        $supportedFormats = $this->getSupportedFormats();
        $minWidth = config('images.upload.min_width', 400);
        $minHeight = config('images.upload.min_height', 300);
        $allowedMimeTypes = config('images.upload.allowed_mime_types', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);

        // Check file size
        if ($file->getSize() > $maxFileSize) {
            $errors[] = 'File size exceeds maximum allowed size of ' . ($maxFileSize / 1024 / 1024) . 'MB';
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

        // Check minimum dimensions
        try {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                
                if ($width < $minWidth || $height < $minHeight) {
                    $errors[] = "Image dimensions too small. Minimum size is {$minWidth}x{$minHeight} pixels.";
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
        $originalImage = $this->imageManager->read($file->getPathname());
        $qualitySettings = $this->getQualitySettings();
        $storagePath = config('images.storage.path', 'properties');
        
        foreach ($qualitySettings as $size => $settings) {
            // Calculate dimensions maintaining aspect ratio
            $dimensions = $this->calculateDimensions(
                $originalImage->width(),
                $originalImage->height(),
                $settings['width'],
                $settings['height']
            );
            
            // Create resized image
            $resizedImage = $originalImage->clone()
                ->resize($dimensions['width'], $dimensions['height']);
            
            // Apply format conversion based on config
            $format = $settings['format'] ?? 'webp';
            if ($format === 'webp') {
                $resizedImage = $resizedImage->toWebp($settings['quality']);
            } elseif ($format === 'jpeg') {
                $resizedImage = $resizedImage->toJpeg($settings['quality']);
            }
            
            // Generate filename
            $filename = $propertySlug . '_' . $size . '_' . time() . '.' . $format;
            $path = $storagePath . '/' . $filename;
            $fullPath = storage_path('app/public/' . $path);
            
            // Ensure directory exists
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Save the image
            $resizedImage->save($fullPath);
            
            $processedImages[$size] = [
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
    }

    /**
     * Process base64 encoded image
     */
    public function processBase64Image(string $base64Data, string $propertySlug): array
    {
        $supportedFormats = $this->getSupportedFormats();
        $maxFileSize = $this->getMaxFileSize();
        $minWidth = config('images.upload.min_width', 400);
        $minHeight = config('images.upload.min_height', 300);
        
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
            // Load and validate image
            $image = $this->imageManager->read($tempPath);
            
            // Check minimum dimensions
            if ($image->width() < $minWidth || $image->height() < $minHeight) {
                throw new \InvalidArgumentException("Image dimensions must be at least {$minWidth}x{$minHeight} pixels");
            }
            
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
                
                // Create resized image
                $resizedImage = $image->clone()
                    ->resize($dimensions['width'], $dimensions['height']);
                
                // Apply format conversion based on config
                $format = $settings['format'] ?? 'webp';
                if ($format === 'webp') {
                    $resizedImage = $resizedImage->toWebp($settings['quality']);
                } elseif ($format === 'jpeg') {
                    $resizedImage = $resizedImage->toJpeg($settings['quality']);
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
                
                // Save the image
                $resizedImage->save($fullPath);
                
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
        
        foreach (array_keys(self::QUALITY_SETTINGS) as $sizeName) {
            $filePath = "properties/{$propertySlug}/{$baseFileName}_{$sizeName}.webp";
            if (Storage::disk('public')->exists($filePath)) {
                $deleted = Storage::disk('public')->delete($filePath) && $deleted;
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
        
        foreach (self::QUALITY_SETTINGS as $sizeName => $settings) {
            $filePath = "properties/{$propertySlug}/{$baseFileName}_{$sizeName}.webp";
            if (Storage::disk('public')->exists($filePath)) {
                $variants[$sizeName] = [
                    'url' => Storage::disk('public')->url($filePath),
                    'width' => $settings['width'],
                    'height' => $settings['height']
                ];
            }
        }
        
        return $variants;
    }

    /**
     * Clean up orphaned images for a property
     */
    public function cleanupPropertyImages(string $propertySlug): int
    {
        $directory = "properties/{$propertySlug}";
        $files = Storage::disk('public')->files($directory);
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if (Storage::disk('public')->delete($file)) {
                $deletedCount++;
            }
        }
        
        // Remove empty directory
        if (empty(Storage::disk('public')->files($directory))) {
            Storage::disk('public')->deleteDirectory($directory);
        }
        
        return $deletedCount;
    }
}