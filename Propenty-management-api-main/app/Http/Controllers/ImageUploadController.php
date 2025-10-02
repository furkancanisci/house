<?php

namespace App\Http\Controllers;

use App\Services\BunnyStorageService;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class ImageUploadController extends Controller
{
    protected $bunnyStorage;
    protected $imageProcessor;

    public function __construct(BunnyStorageService $bunnyStorage, ImageProcessingService $imageProcessor)
    {
        $this->bunnyStorage = $bunnyStorage;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * Upload single image to Bunny Storage
     */
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
                'folder' => 'nullable|string|max:100',
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

            $image = $request->file('image');
            $folder = $request->input('folder', 'uploads');
            $resizeWidth = $request->input('resize_width');
            $resizeHeight = $request->input('resize_height');
            $quality = $request->input('quality', 85);

            // Generate unique filename
            $filename = $this->generateUniqueFilename($image->getClientOriginalExtension());
            $path = $folder . '/' . $filename;

            // Process image (resize, compress)
            $processedImage = $this->imageProcessor->processUploadedImage(
                $image,
                $resizeWidth,
                $resizeHeight,
                $quality
            );

            // Upload to Bunny Storage
            $uploadResult = $this->bunnyStorage->uploadFile($path, $processedImage);

            if (!$uploadResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload image to storage'
                ], 500);
            }

            // Get CDN URL
            $cdnUrl = $this->bunnyStorage->getCdnUrl($path);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => $cdnUrl,
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
     * Upload multiple images to Bunny Storage
     */
    public function uploadMultipleImages(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array|max:10',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'folder' => 'nullable|string|max:100',
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
            $folder = $request->input('folder', 'uploads');
            $resizeWidth = $request->input('resize_width');
            $resizeHeight = $request->input('resize_height');
            $quality = $request->input('quality', 85);

            $uploadedImages = [];
            $errors = [];

            foreach ($images as $index => $image) {
                try {
                    // Generate unique filename
                    $filename = $this->generateUniqueFilename($image->getClientOriginalExtension());
                    $path = $folder . '/' . $filename;

                    // Process image
                    $processedImage = $this->imageProcessor->processUploadedImage(
                        $image,
                        $resizeWidth,
                        $resizeHeight,
                        $quality
                    );

                    // Upload to Bunny Storage
                    $uploadResult = $this->bunnyStorage->uploadFile($path, $processedImage);

                    if ($uploadResult) {
                        $uploadedImages[] = [
                            'filename' => $filename,
                            'path' => $path,
                            'url' => $this->bunnyStorage->getCdnUrl($path),
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
            $result = $this->imageProcessor->processBase64Image(
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

            // Upload to Bunny Storage
            $uploadResult = $this->bunnyStorage->uploadFile($path, $processedImage);

            if (!$uploadResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload image to storage'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Base64 image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => $this->bunnyStorage->getCdnUrl($path),
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
            if (!$this->bunnyStorage->fileExists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Delete from Bunny Storage
            $deleteResult = $this->bunnyStorage->deleteFile($path);

            if (!$deleteResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete image from storage'
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
            if (!$this->bunnyStorage->fileExists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'url' => $this->bunnyStorage->getCdnUrl($path),
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