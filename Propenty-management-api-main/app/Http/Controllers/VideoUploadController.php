<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class VideoUploadController extends Controller
{
    /**
     * Upload single video file
     */
    public function uploadVideo(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'video' => 'required|file|mimes:mp4,avi,mov,wmv,flv,webm|max:512000', // 500MB max
                'property_id' => 'required|integer|exists:properties,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input detected. Please check your data and try again.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $video = $request->file('video');
            $propertyId = $request->input('property_id');
            $propertyFolder = "properties/{$propertyId}/videos";

            // Create directory if it doesn't exist
            if (!Storage::disk('public')->exists($propertyFolder)) {
                Storage::disk('public')->makeDirectory($propertyFolder);
            }

            // Generate unique filename
            $filename = $this->generateUniqueFilename($video->getClientOriginalExtension());
            $path = $propertyFolder . '/' . $filename;

            // Upload to local storage
            $uploadResult = Storage::disk('public')->putFileAs($propertyFolder, $video, $filename);

            if (!$uploadResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload video to storage'
                ], 500);
            }

            // Get local URL
            $localUrl = Storage::disk('public')->url($uploadResult);

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $uploadResult,
                    'url' => $localUrl,
                    'size' => $video->getSize(),
                    'mime_type' => $video->getMimeType()
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
     * Upload multiple video files
     */
    public function uploadMultipleVideos(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'videos' => 'required|array|max:1',
                'videos.*' => 'required|file|mimes:mp4,avi,mov,wmv,flv,webm|max:512000',
                'property_id' => 'required|integer|exists:properties,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input detected. Please check your data and try again.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $videos = $request->file('videos');
            $propertyId = $request->input('property_id');
            $propertyFolder = "properties/{$propertyId}/videos";

            // Create directory if it doesn't exist
            if (!Storage::disk('public')->exists($propertyFolder)) {
                Storage::disk('public')->makeDirectory($propertyFolder);
            }

            $uploadedVideos = [];
            $errors = [];

            foreach ($videos as $index => $video) {
                try {
                    // Generate unique filename
                    $filename = $this->generateUniqueFilename($video->getClientOriginalExtension());
                    
                    // Upload to local storage
                    $uploadResult = Storage::disk('public')->putFileAs($propertyFolder, $video, $filename);

                    if ($uploadResult) {
                        $uploadedVideos[] = [
                            'filename' => $filename,
                            'path' => $uploadResult,
                            'url' => Storage::disk('public')->url($uploadResult),
                            'size' => $video->getSize(),
                            'mime_type' => $video->getMimeType()
                        ];
                    } else {
                        $errors[] = "Failed to upload video at index {$index}";
                    }

                } catch (Exception $e) {
                    $errors[] = "Error uploading video at index {$index}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => count($uploadedVideos) > 0,
                'message' => count($uploadedVideos) . ' videos uploaded successfully',
                'data' => [
                    'uploaded' => $uploadedVideos,
                    'errors' => $errors,
                    'total_uploaded' => count($uploadedVideos),
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
     * Generate unique filename
     */
    private function generateUniqueFilename(string $extension): string
    {
        return time() . '_' . Str::random(10) . '.' . $extension;
    }

    /**
     * Delete video file
     */
    public function deleteVideo(Request $request): JsonResponse
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

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Video deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Video file not found'
                ], 404);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
}