<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Language switching routes
Route::prefix('language')->name('language.')->group(function () {
    Route::post('/switch', [LanguageController::class, 'switch'])->name('switch');
    Route::get('/current', [LanguageController::class, 'current'])->name('current');
});

 

// Health check route (also available via API)
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
    ]);
});

// Test upload configuration
Route::get('/test-upload-config', function () {
    return response()->json([
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_time' => ini_get('max_input_time'),
    ]);
});

Route::post('/test-video-upload', function (Illuminate\Http\Request $request) {
    \Log::info('Test Video Upload Debug', [
        'request_all' => $request->all(),
        'files' => $_FILES,
        'has_videos' => $request->hasFile('videos'),
        'videos_count' => $request->hasFile('videos') ? count($request->file('videos')) : 0,
        'content_length' => $request->header('Content-Length'),
        'content_type' => $request->header('Content-Type'),
    ]);
    
    if ($request->hasFile('videos')) {
        foreach ($request->file('videos') as $index => $video) {
            \Log::info("Video {$index} details", [
                'original_name' => $video->getClientOriginalName(),
                'size' => $video->getSize(),
                'mime_type' => $video->getMimeType(),
                'error' => $video->getError(),
                'is_valid' => $video->isValid(),
            ]);
        }
    }
    
    return response()->json([
        'status' => 'debug_complete',
        'has_files' => $request->hasFile('videos'),
        'files_count' => $_FILES ? count($_FILES) : 0,
    ]);
});

// Documentation route (placeholder)
Route::get('/docs', function () {
    return response()->json([
        'message' => 'API Documentation',
        'note' => 'This is a placeholder. In production, you would integrate with Laravel Scribe or similar.',
        'swagger_url' => url('/api/documentation'),
        'postman_collection' => url('/api/postman-collection'),
    ]);
});

// Redirect login to admin login
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Bunny Storage Connection Test Route
Route::get('/test-bunny', function () {
    try {
        // Load environment variables
        $storageZone = env('BUNNY_STORAGE_ZONE');
        $accessKey = env('BUNNY_API_KEY');
        $pullZone = env('BUNNY_PULL_ZONE');
        
        if (!$storageZone || !$accessKey || !$pullZone) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bunny Storage configuration missing',
                'missing' => [
                    'BUNNY_STORAGE_ZONE' => !$storageZone,
                    'BUNNY_API_KEY' => !$accessKey,
                    'BUNNY_PULL_ZONE' => !$pullZone
                ]
            ], 500);
        }
        
        $baseUrl = "https://storage.bunnycdn.com/{$storageZone}";
        
        // Test connectivity
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'AccessKey: ' . $accessKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Connection failed',
                'error' => $error,
                'config' => [
                    'storage_zone' => $storageZone,
                    'pull_zone' => $pullZone,
                    'base_url' => $baseUrl
                ]
            ], 500);
        }
        
        // Test file upload
        $testContent = 'Bunny test file - ' . now()->toISOString();
        $fileName = 'test-' . time() . '.txt';
        $filePath = 'test-uploads/' . $fileName;
        
        $uploadCh = curl_init();
        curl_setopt($uploadCh, CURLOPT_URL, $baseUrl . '/' . $filePath);
        curl_setopt($uploadCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($uploadCh, CURLOPT_HTTPHEADER, [
            'AccessKey: ' . $accessKey,
            'Content-Type: text/plain'
        ]);
        curl_setopt($uploadCh, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($uploadCh, CURLOPT_POSTFIELDS, $testContent);
        curl_setopt($uploadCh, CURLOPT_TIMEOUT, 30);
        curl_setopt($uploadCh, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($uploadCh, CURLOPT_SSL_VERIFYHOST, false);
        
        $uploadResponse = curl_exec($uploadCh);
        $uploadHttpCode = curl_getinfo($uploadCh, CURLINFO_HTTP_CODE);
        $uploadError = curl_error($uploadCh);
        curl_close($uploadCh);
        
        $cdnUrl = "https://{$pullZone}.b-cdn.net/{$filePath}";
        
        return response()->json([
            'status' => 'success',
            'message' => 'Bunny Storage connection test completed',
            'results' => [
                'connectivity' => [
                    'status' => $httpCode === 200 ? 'success' : 'failed',
                    'http_code' => $httpCode,
                    'response' => $httpCode !== 200 ? $response : 'Connected successfully'
                ],
                'file_upload' => [
                    'status' => $uploadHttpCode === 201 ? 'success' : 'failed',
                    'http_code' => $uploadHttpCode,
                    'file_path' => $filePath,
                    'cdn_url' => $cdnUrl,
                    'error' => $uploadError ?: null
                ]
            ],
            'config' => [
                'storage_zone' => $storageZone,
                'pull_zone' => $pullZone,
                'base_url' => $baseUrl,
                'access_key_preview' => substr($accessKey, 0, 8) . '...'
            ],
            'timestamp' => now()->toISOString()
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Test failed with exception',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});


// Redirect to admin panel for authenticated users
Route::get('/', function () {
    if (auth()->check() && auth()->user()->can('view dashboard')) {
        return redirect()->route('admin.dashboard');
    }
    return view('welcome');
});
