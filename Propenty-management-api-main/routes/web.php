<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return response()->json([
        'message' => 'Property Management API',
        'version' => '1.0.0',
        'documentation' => url('/docs'),
        'api_base_url' => url('/api/v1'),
        'status' => 'active',
        'features' => [
            'User Authentication & Authorization',
            'Property CRUD Operations',
            'Advanced Search & Filtering',
            'File Upload & Media Management',
            'Analytics & Reporting',
            'Favorites System',
            'Real-time Notifications',
            'RESTful API Design',
        ],
        'endpoints' => [
            'authentication' => url('/api/v1/auth'),
            'properties' => url('/api/v1/properties'),
            'dashboard' => url('/api/v1/dashboard'),
            'search' => url('/api/v1/search'),
            'locations' => url('/api/v1/locations'),
            'stats' => url('/api/v1/stats'),
        ],
    ]);
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

// Documentation route (placeholder)
Route::get('/docs', function () {
    return response()->json([
        'message' => 'API Documentation',
        'note' => 'This is a placeholder. In production, you would integrate with Laravel Scribe or similar.',
        'swagger_url' => url('/api/documentation'),
        'postman_collection' => url('/api/postman-collection'),
    ]);
});

// Catch-all route for SPA frontend
Route::get('/{any}', function () {
    return response()->json([
        'message' => 'This is an API-only application.',
        'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
        'api_url' => url('/api/v1'),
    ]);
})->where('any', '.*');
