<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ImageProcessingService
{

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
            \Illuminate\Support\Facades\Log::info('ImageProcessingService: Checking image dimensions', [
                'file_path' => $file->getPathname(),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);
            
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
                
                \Illuminate\Support\Facades\Log::info('ImageProcessingService: Image dimensions read successfully', [
                    'width' => $width,
                    'height' => $height,
                    'max_width' => $maxWidth,
                    'max_height' => $maxHeight
                ]);
                
                // Minimum dimension check removed - only checking maximum file size
                
                // Check maximum dimensions
                if ($width > $maxWidth || $height > $maxHeight) {
                    $errors[] = "Image dimensions too large. Maximum size is {$maxWidth}x{$maxHeight} pixels. The image will be automatically resized.";
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('ImageProcessingService: getimagesize returned false', [
                    'file_path' => $file->getPathname(),
                    'original_name' => $file->getClientOriginalName()
                ]);
                $errors[] = 'Unable to read image dimensions. File may be corrupted or not a valid image.';
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ImageProcessingService: Exception while reading image dimensions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_path' => $file->getPathname(),
                'original_name' => $file->getClientOriginalName()
            ]);
            $errors[] = 'Unable to read image dimensions: ' . $e->getMessage();
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
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Unable to decode image from file (' . $tempPath . '). Error: ' . $e->getMessage()];
        } finally {
            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Process base64 image with custom dimensions and quality (simple version)
     */
    public function processBase64ImageSimple(string $base64Data, ?int $width = null, ?int $height = null, int $quality = 85, ?string $customFilename = null): array
    {
        // Extract image data and format
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            return ['success' => false, 'message' => 'Invalid base64 image format'];
        }
        
        $imageFormat = strtolower($matches[1]);
        $supportedFormats = $this->getSupportedFormats();
        if (!in_array($imageFormat, $supportedFormats)) {
            return ['success' => false, 'message' => 'Unsupported image format'];
        }
        
        // Decode base64 data
        $imageData = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
        if (!$imageData) {
            return ['success' => false, 'message' => 'Failed to decode base64 image'];
        }
        
        // Check file size
        $maxFileSize = $this->getMaxFileSize();
        if (strlen($imageData) > $maxFileSize) {
            return ['success' => false, 'message' => 'Image size cannot exceed ' . ($maxFileSize / 1024 / 1024) . 'MB'];
        }
        
        // Create temporary file
        $tempPath = tempnam(sys_get_temp_dir(), 'image_upload_');
        file_put_contents($tempPath, $imageData);
        
        try {
            // Verify temp file exists and has content
            if (!file_exists($tempPath)) {
                return ['success' => false, 'message' => 'Temporary file was not created'];
            }
            
            if (filesize($tempPath) === 0) {
                return ['success' => false, 'message' => 'Temporary file is empty'];
            }
            
            // Load the image
            $image = Image::make($tempPath);
            
            // Resize if dimensions are provided
            if ($width && $height) {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } elseif ($width) {
                $image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } elseif ($height) {
                $image->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Generate filename
            $filename = $customFilename ?: (time() . '_' . Str::random(10) . '.jpg');
            if (!str_ends_with($filename, '.jpg')) {
                $filename .= '.jpg';
            }
            
            // Encode the image with specified quality
            $processedImageData = $image->encode('jpg', $quality)->__toString();
            
            return [
                'success' => true,
                'filename' => $filename,
                'image_data' => $processedImageData
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Unable to process image: ' . $e->getMessage()];
        } finally {
            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Process uploaded image with custom dimensions and quality
     */
    public function processUploadedImage(UploadedFile $file, ?int $width = null, ?int $height = null, int $quality = 85): string
    {
        try {
            // Debug: Log file information
            \Log::info('Processing uploaded image', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $file->getPathname(),
                'file_exists' => file_exists($file->getPathname())
            ]);
            
            // Validate the uploaded file
            $errors = $this->validateImage($file);
            if (!empty($errors)) {
                \Log::error('Image validation failed', ['errors' => $errors]);
                throw new \InvalidArgumentException(implode(', ', $errors));
            }

            // Load the image
            \Log::info('Attempting to load image with Intervention Image');
            $image = Image::make($file->getPathname());
            \Log::info('Image loaded successfully', [
                'width' => $image->width(),
                'height' => $image->height()
            ]);
            
            // Resize if dimensions are provided
            if ($width && $height) {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } elseif ($width) {
                $image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } elseif ($height) {
                $image->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Encode the image with specified quality
            \Log::info('Encoding image to JPG');
            $encodedImage = $image->encode('jpg', $quality)->__toString();
            \Log::info('Image processed successfully', ['encoded_size' => strlen($encodedImage)]);
            
            return $encodedImage;
            
        } catch (\Exception $e) {
            \Log::error('Error processing uploaded image', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
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
        $storagePath = config('images.storage.path', 'properties');
        
        foreach (array_keys($qualitySettings) as $sizeName) {
            $filePath = $storagePath . "/" . $propertySlug . "_" . $sizeName . "_*.jpg";
            // Use glob pattern to find files with timestamp
            $files = glob(storage_path('app/public/' . $storagePath . '/') . $propertySlug . '_' . $sizeName . '_*.jpg');
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $deleted = unlink($file) && $deleted;
                }
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
        $storagePath = config('images.storage.path', 'properties');
        
        foreach ($qualitySettings as $sizeName => $settings) {
            // Use glob pattern to find files with timestamp
            $files = glob(storage_path('app/public/' . $storagePath . '/') . $propertySlug . '_' . $sizeName . '_*.jpg');
            if (!empty($files)) {
                $filePath = str_replace(storage_path('app/public/'), '', $files[0]);
                $variants[$sizeName] = [
                    'path' => $filePath,
                    'url' => Storage::url($filePath),
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