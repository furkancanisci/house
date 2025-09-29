<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Property;

class PropertyMediaController extends Controller
{
    /**
     * Get all images for a specific property
     */
    public function getPropertyImages(Request $request, $propertyId): JsonResponse
    {
        try {
            // Validate property exists
            $property = Property::find($propertyId);
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found'
                ], 404);
            }

            $imagesPath = "properties/{$propertyId}/images";
            $images = [];

            // Check if images directory exists
            if (Storage::disk('public')->exists($imagesPath)) {
                $files = Storage::disk('public')->files($imagesPath);
                
                foreach ($files as $file) {
                    $filename = basename($file);
                    $images[] = [
                        'filename' => $filename,
                        'path' => $file,
                        'url' => Storage::disk('public')->url($file),
                        'size' => Storage::disk('public')->size($file),
                        'last_modified' => Storage::disk('public')->lastModified($file)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Property images retrieved successfully',
                'data' => [
                    'property_id' => $propertyId,
                    'images' => $images,
                    'total_images' => count($images)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all videos for a specific property
     */
    public function getPropertyVideos(Request $request, $propertyId): JsonResponse
    {
        try {
            // Validate property exists
            $property = Property::find($propertyId);
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found'
                ], 404);
            }

            $videosPath = "properties/{$propertyId}/videos";
            $videos = [];

            // Check if videos directory exists
            if (Storage::disk('public')->exists($videosPath)) {
                $files = Storage::disk('public')->files($videosPath);
                
                foreach ($files as $file) {
                    $filename = basename($file);
                    $videos[] = [
                        'filename' => $filename,
                        'path' => $file,
                        'url' => Storage::disk('public')->url($file),
                        'size' => Storage::disk('public')->size($file),
                        'last_modified' => Storage::disk('public')->lastModified($file)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Property videos retrieved successfully',
                'data' => [
                    'property_id' => $propertyId,
                    'videos' => $videos,
                    'total_videos' => count($videos)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property videos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all media (images and videos) for a specific property
     */
    public function getPropertyMedia(Request $request, $propertyId): JsonResponse
    {
        try {
            // Validate property exists
            $property = Property::find($propertyId);
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found'
                ], 404);
            }

            $images = [];
            $videos = [];

            // Get all media using Spatie Media Library from all collections
            $allMedia = collect();
            $allMedia = $allMedia->merge($property->getMedia('images'));
            $allMedia = $allMedia->merge($property->getMedia('videos'));
            $allMedia = $allMedia->merge($property->getMedia('main_image'));
            $allMedia = $allMedia->merge($property->getMedia('gallery'));
            $allMedia = $allMedia->merge($property->getMedia('documents'));
            
            foreach ($allMedia as $mediaItem) {
                $mediaData = [
                    'id' => $mediaItem->id,
                    'filename' => $mediaItem->file_name,
                    'name' => $mediaItem->name,
                    'url' => $mediaItem->getUrl(),
                    'size' => $mediaItem->size,
                    'mime_type' => $mediaItem->mime_type,
                    'collection' => $mediaItem->collection_name,
                    'created_at' => $mediaItem->created_at->toISOString()
                ];

                // Categorize by collection or mime type
                if ($mediaItem->collection_name === 'videos' || str_starts_with($mediaItem->mime_type, 'video/')) {
                    $mediaData['type'] = 'video';
                    $videos[] = $mediaData;
                } else {
                    $mediaData['type'] = 'image';
                    $images[] = $mediaData;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Property media retrieved successfully',
                'data' => [
                    'property_id' => $propertyId,
                    'images' => $images,
                    'videos' => $videos,
                    'total_images' => count($images),
                    'total_videos' => count($videos),
                    'total_media' => count($images) + count($videos)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific media file for a property
     */
    public function deletePropertyMedia(Request $request, $propertyId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file_path' => 'required|string',
                'media_type' => 'required|string|in:image,video'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate property exists
            $property = Property::find($propertyId);
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found'
                ], 404);
            }

            $filePath = $request->input('file_path');
            $mediaType = $request->input('media_type');

            // Validate file path belongs to this property
            $expectedPath = "properties/{$propertyId}/{$mediaType}s";
            if (!str_starts_with($filePath, $expectedPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file path for this property'
                ], 400);
            }

            // Check if file exists
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Delete the file
            $deleteResult = Storage::disk('public')->delete($filePath);

            if (!$deleteResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete file'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Media file deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete media file: ' . $e->getMessage()
            ], 500);
        }
    }
}