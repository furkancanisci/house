<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SecureImageUpload
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): ResponseAlias
    {
        // Rate limiting for image uploads
        $key = 'image-upload:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 10)) { // 10 uploads per minute
            return response()->json([
                'success' => false,
                'message' => 'Too many upload attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], ResponseAlias::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, 60); // 1 minute window

        // Validate content type for file uploads
        if ($request->hasFile('image') || $request->hasFile('images')) {
            $files = $request->hasFile('images') ? $request->file('images') : [$request->file('image')];
            
            foreach ($files as $file) {
                if ($file) {
                    // Check MIME type
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.'
                        ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    // Check file size (10MB max)
                    if ($file->getSize() > 10485760) {
                        return response()->json([
                            'success' => false,
                            'message' => 'File size too large. Maximum size is 10MB.'
                        ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    // Check for malicious file extensions in original name
                    $originalName = $file->getClientOriginalName();
                    $dangerousExtensions = ['php', 'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js'];
                    
                    foreach ($dangerousExtensions as $ext) {
                        if (stripos($originalName, '.' . $ext) !== false) {
                            return response()->json([
                                'success' => false,
                                'message' => 'File contains dangerous extension.'
                            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
                        }
                    }
                }
            }
        }

        // Validate base64 image data
        if ($request->has('image_data')) {
            $imageData = $request->input('image_data');
            
            // Check if it's a valid base64 image
            if (!preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/', $imageData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid base64 image format.'
                ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check base64 size (roughly 10MB when decoded)
            if (strlen($imageData) > 14000000) { // Base64 is ~33% larger than binary
                return response()->json([
                    'success' => false,
                    'message' => 'Base64 image too large. Maximum size is 10MB.'
                ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        // Sanitize folder input
        if ($request->has('folder')) {
            $folder = $request->input('folder');
            
            // Remove dangerous characters and path traversal attempts
            $folder = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $folder);
            $folder = str_replace(['../', '../', '..\\', '..\\'], '', $folder);
            $folder = trim($folder, '/');
            
            if (empty($folder)) {
                $folder = 'uploads';
            }
            
            $request->merge(['folder' => $folder]);
        }

        // Validate and sanitize filename
        if ($request->has('filename')) {
            $filename = $request->input('filename');
            
            // Remove dangerous characters
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
            
            if (empty($filename)) {
                $request->request->remove('filename');
            } else {
                $request->merge(['filename' => $filename]);
            }
        }

        return $next($request);
    }
}