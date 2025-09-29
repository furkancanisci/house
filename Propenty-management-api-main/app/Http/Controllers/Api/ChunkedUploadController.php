<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class ChunkedUploadController extends Controller
{
    /**
     * Initialize a chunked upload session
     */
    public function initiate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string|max:255',
            'filesize' => 'required|integer|min:1',
            'chunk_size' => 'required|integer|min:1024|max:10485760', // 1KB to 10MB
            'total_chunks' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate unique upload session ID
        $uploadId = Str::uuid()->toString();
        
        // Create upload session directory
        $sessionPath = "chunked-uploads/{$uploadId}";
        Storage::disk('local')->makeDirectory($sessionPath);
        
        // Store session metadata
        $metadata = [
            'upload_id' => $uploadId,
            'filename' => $request->filename,
            'filesize' => $request->filesize,
            'chunk_size' => $request->chunk_size,
            'total_chunks' => $request->total_chunks,
            'uploaded_chunks' => [],
            'created_at' => now()->toISOString(),
            'status' => 'initiated'
        ];
        
        Storage::disk('local')->put("{$sessionPath}/metadata.json", json_encode($metadata));
        
        return response()->json([
            'success' => true,
            'upload_id' => $uploadId,
            'message' => 'Upload session initiated successfully'
        ]);
    }

    /**
     * Upload a file chunk
     */
    public function uploadChunk(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'upload_id' => 'required|string',
            'chunk_number' => 'required|integer|min:0',
            'chunk' => 'required|file'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadId = $request->upload_id;
        $chunkNumber = $request->chunk_number;
        $sessionPath = "chunked-uploads/{$uploadId}";
        
        // Check if session exists
        if (!Storage::disk('local')->exists("{$sessionPath}/metadata.json")) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session not found'
            ], 404);
        }
        
        // Load session metadata
        $metadata = json_decode(Storage::disk('local')->get("{$sessionPath}/metadata.json"), true);
        
        // Validate chunk number
        if ($chunkNumber >= $metadata['total_chunks']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid chunk number'
            ], 400);
        }
        
        // Store the chunk
        $chunkFile = $request->file('chunk');
        $chunkPath = "{$sessionPath}/chunk_{$chunkNumber}";
        
        try {
            Storage::disk('local')->putFileAs(
                dirname($chunkPath),
                $chunkFile,
                basename($chunkPath)
            );
            
            // Update metadata
            if (!in_array($chunkNumber, $metadata['uploaded_chunks'])) {
                $metadata['uploaded_chunks'][] = $chunkNumber;
                sort($metadata['uploaded_chunks']);
            }
            
            $metadata['updated_at'] = now()->toISOString();
            Storage::disk('local')->put("{$sessionPath}/metadata.json", json_encode($metadata));
            
            $progress = (count($metadata['uploaded_chunks']) / $metadata['total_chunks']) * 100;
            
            return response()->json([
                'success' => true,
                'message' => 'Chunk uploaded successfully',
                'progress' => round($progress, 2),
                'uploaded_chunks' => count($metadata['uploaded_chunks']),
                'total_chunks' => $metadata['total_chunks']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload chunk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete the chunked upload by combining all chunks
     */
    public function complete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'upload_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadId = $request->upload_id;
        $sessionPath = "chunked-uploads/{$uploadId}";
        
        // Check if session exists
        if (!Storage::disk('local')->exists("{$sessionPath}/metadata.json")) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session not found'
            ], 404);
        }
        
        // Load session metadata
        $metadata = json_decode(Storage::disk('local')->get("{$sessionPath}/metadata.json"), true);
        
        // Check if all chunks are uploaded
        if (count($metadata['uploaded_chunks']) !== $metadata['total_chunks']) {
            return response()->json([
                'success' => false,
                'message' => 'Not all chunks have been uploaded',
                'uploaded_chunks' => count($metadata['uploaded_chunks']),
                'total_chunks' => $metadata['total_chunks']
            ], 400);
        }
        
        try {
            // Create final file path
            $finalPath = "uploads/" . Str::uuid() . "_" . $metadata['filename'];
            $tempPath = storage_path("app/{$finalPath}");
            
            // Ensure directory exists
            $directory = dirname($tempPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Combine chunks
            $finalFile = fopen($tempPath, 'wb');
            
            for ($i = 0; $i < $metadata['total_chunks']; $i++) {
                $chunkPath = storage_path("app/{$sessionPath}/chunk_{$i}");
                
                if (!file_exists($chunkPath)) {
                    fclose($finalFile);
                    unlink($tempPath);
                    
                    return response()->json([
                        'success' => false,
                        'message' => "Missing chunk {$i}"
                    ], 400);
                }
                
                $chunkData = file_get_contents($chunkPath);
                fwrite($finalFile, $chunkData);
            }
            
            fclose($finalFile);
            
            // Verify file size
            $actualSize = filesize($tempPath);
            if ($actualSize !== $metadata['filesize']) {
                unlink($tempPath);
                
                return response()->json([
                    'success' => false,
                    'message' => 'File size mismatch after combining chunks'
                ], 400);
            }
            
            // Clean up chunks
            Storage::disk('local')->deleteDirectory($sessionPath);
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file_path' => $finalPath,
                'file_size' => $actualSize,
                'filename' => $metadata['filename']
            ]);
            
        } catch (\Exception $e) {
            // Clean up on error
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upload progress
     */
    public function getProgress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'upload_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadId = $request->upload_id;
        $sessionPath = "chunked-uploads/{$uploadId}";
        
        // Check if session exists
        if (!Storage::disk('local')->exists("{$sessionPath}/metadata.json")) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session not found'
            ], 404);
        }
        
        // Load session metadata
        $metadata = json_decode(Storage::disk('local')->get("{$sessionPath}/metadata.json"), true);
        
        $progress = (count($metadata['uploaded_chunks']) / $metadata['total_chunks']) * 100;
        
        return response()->json([
            'success' => true,
            'upload_id' => $uploadId,
            'progress' => round($progress, 2),
            'uploaded_chunks' => count($metadata['uploaded_chunks']),
            'total_chunks' => $metadata['total_chunks'],
            'status' => $metadata['status'],
            'filename' => $metadata['filename']
        ]);
    }

    /**
     * Cancel an upload session
     */
    public function cancel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'upload_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadId = $request->upload_id;
        $sessionPath = "chunked-uploads/{$uploadId}";
        
        try {
            // Clean up session directory
            if (Storage::disk('local')->exists($sessionPath)) {
                Storage::disk('local')->deleteDirectory($sessionPath);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Upload session cancelled successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel upload: ' . $e->getMessage()
            ], 500);
        }
    }
}