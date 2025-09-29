<?php

namespace App\Http\Controllers;

use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ImageUploadController extends Controller
{
    protected $imageProcessor;

    public function __construct(ImageProcessingService $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function uploadImage(Request $request)
    {
        try {
            // Validate required fields
            $validator = Validator::make($request->all(), [
                'image' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'property_id' => 'required|integer|exists:properties,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Enhanced logging for debugging 500 errors
            \Log::info('ImageUploadController: uploadImage started', [
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length'),
                'user_agent' => $request->header('User-Agent'),
                'has_image_file' => $request->hasFile('image'),
                'property_id' => $request->input('property_id'),
                'all_files' => array_keys($request->allFiles()),
                'request_size' => strlen($request->getContent()),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version()
            ]);
            
            $image = $request->file('image');
            $propertyId = $request->input('property_id');
            
            if (!$image) {
                \Log::warning('ImageUploadController: No image file provided', [
                    'request_data' => $request->all(),
                    'files' => $request->allFiles(),
                    'raw_input' => substr($request->getContent(), 0, 500) // First 500 chars
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No image file provided'
                ], 400);
            }

            \Log::info('ImageUploadController: Processing image', [
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize(),
                'extension' => $image->getClientOriginalExtension(),
                'is_valid' => $image->isValid(),
                'error' => $image->getError(),
                'temp_path' => $image->getPathname(),
                'real_path' => $image->getRealPath()
            ]);

            // Use the correct service property and method
            $result = $this->imageProcessor->processUploadedImage($image);

            \Log::info('ImageUploadController: Image processed successfully', [
                'result_size' => strlen($result),
                'result_type' => 'binary_image_data',
                'final_memory_usage' => memory_get_usage(true),
                'final_memory_peak' => memory_get_peak_usage(true)
            ]);

            // Generate a unique filename for the processed image
            $filename = $this->generateUniqueFilename('jpg');
            $propertyFolder = "properties/{$propertyId}/images";
            $path = $propertyFolder . '/' . $filename;
            
            // Create directory if it doesn't exist
            if (!Storage::disk('public')->exists($propertyFolder)) {
                Storage::disk('public')->makeDirectory($propertyFolder);
            }
            
            // Store the processed image
            $uploadResult = Storage::disk('public')->put($path, $result);
            
            if (!$uploadResult) {
                throw new \Exception('Failed to store processed image');
            }

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'size' => strlen($result)
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('ImageUploadController: Exception in uploadImage', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['image']), // Exclude image data from log
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'image_info' => $request->hasFile('image') ? [
                    'name' => $request->file('image')->getClientOriginalName(),
                    'size' => $request->file('image')->getSize(),
                    'mime' => $request->file('image')->getMimeType(),
                    'error' => $request->file('image')->getError()
                ] : 'No image file'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple images to Bunny Storage
     */
    public function uploadMultipleImages(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array|max:10',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'property_id' => 'required|integer|exists:properties,id',
                'resize_width' => 'nullable|integer|min:50|max:2000',
                'resize_height' => 'nullable|integer|min:50|max:2000',
                'quality' => 'nullable|integer|min:10|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $images = $request->file('images');
            $propertyId = $request->input('property_id');
            $propertyFolder = "properties/{$propertyId}/images";
            $resizeWidth = $request->input('resize_width');
            $resizeHeight = $request->input('resize_height');
            $quality = $request->input('quality', 85);

            // Create directory if it doesn't exist
            if (!Storage::disk('public')->exists($propertyFolder)) {
                Storage::disk('public')->makeDirectory($propertyFolder);
            }

            $uploadedImages = [];
            $errors = [];

            foreach ($images as $index => $image) {
                try {
                    // Generate unique filename
                    $filename = $this->generateUniqueFilename($image->getClientOriginalExtension());
                    $path = $propertyFolder . '/' . $filename;

                    // Process image
                    $processedImage = $this->imageProcessor->processUploadedImage(
                        $image,
                        $resizeWidth,
                        $resizeHeight,
                        $quality
                    );

                    // Upload to local storage
                    $uploadResult = Storage::disk('public')->put($path, $processedImage);

                    if ($uploadResult) {
                        $uploadedImages[] = [
                            'filename' => $filename,
                            'path' => $path,
                            'url' => Storage::disk('public')->url($path),
                            'size' => strlen($processedImage)
                        ];
                    } else {
                        $errors[] = "Failed to upload image at index {$index}";
                    }

                } catch (Exception $e) {
                    $errors[] = "Error uploading image at index {$index}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => count($uploadedImages) > 0,
                'message' => count($uploadedImages) . ' images uploaded successfully',
                'data' => [
                    'uploaded' => $uploadedImages,
                    'errors' => $errors,
                    'total_uploaded' => count($uploadedImages),
                    'total_errors' => count($errors)
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload base64 image to Bunny Storage
     */
    public function uploadBase64Image(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image_data' => 'required|string',
                'folder' => 'nullable|string|max:100',
                'filename' => 'nullable|string|max:100',
                'resize_width' => 'nullable|integer|min:50|max:2000',
                'resize_height' => 'nullable|integer|min:50|max:2000',
                'quality' => 'nullable|integer|min:10|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $imageData = $request->input('image_data');
            $folder = $request->input('folder', 'uploads');
            $customFilename = $request->input('filename');
            $resizeWidth = $request->input('resize_width');
            $resizeHeight = $request->input('resize_height');
            $quality = $request->input('quality', 85);

            // Process base64 image
            $result = $this->imageProcessor->processBase64ImageSimple(
                $imageData,
                $resizeWidth,
                $resizeHeight,
                $quality,
                $customFilename
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            $filename = $result['filename'];
            $processedImage = $result['image_data'];
            $path = $folder . '/' . $filename;

            // Upload to local storage
            $uploadResult = Storage::disk('public')->put($path, $processedImage);

            if (!$uploadResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload image to local storage'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Base64 image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'size' => strlen($processedImage)
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete image from Bunny Storage
     */
    public function deleteImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->input('path');

            // Check if file exists
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Delete from local storage
            $deleteResult = Storage::disk('public')->delete($path);

            if (!$deleteResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete image from local storage'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get image info and CDN URL
     */
    public function getImageInfo(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->input('path');

            // Check if file exists
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'exists' => true
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get image info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(string $extension): string
    {
        return time() . '_' . Str::random(10) . '.' . strtolower($extension);
    }
}