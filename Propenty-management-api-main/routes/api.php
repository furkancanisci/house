<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyDocumentTypeController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\ImageUploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// In routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/stats-raw', [DashboardController::class, 'statsRaw']);
    Route::get('/test', [PropertyController::class, 'test']);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Property Management API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
})->middleware('auth:sanctum');

Route::get('/testt', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Property Management API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
})->middleware('auth:sanctum');

// API Version 1
Route::prefix('v1')->group(function () {
    
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        // Public authentication routes
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        
        // Email verification routes
        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->name('verification.verify');
        Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
        Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail']);
        
        // User info route - made public for now
        Route::get('/me', [AuthController::class, 'me']);
        
        // Protected authentication routes (only logout operations)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);
            Route::get('/email/verification-status', [AuthController::class, 'getVerificationStatus']);
        });
    });

    // Property Routes - All made public for now
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::get('/featured', [PropertyController::class, 'featured']);
        Route::get('/amenities', [PropertyController::class, 'amenities']);
        Route::get('/price-types', [PropertyController::class, 'priceTypes']);
        Route::get('/test', [PropertyController::class, 'test']);

        // More specific routes should come first to avoid conflicts
        Route::post('/{property}/favorite', [PropertyController::class, 'toggleFavorite']);
        Route::get('/{property}/analytics', [PropertyController::class, 'analytics'])->middleware('auth:sanctum');
        Route::delete('/{property}/images/{mediaId}', [PropertyController::class, 'deleteImage'])->middleware('auth:sanctum');
        Route::get('/{property:slug}/similar', [PropertyController::class, 'similar']);
        Route::get('/{property}/show', [PropertyController::class, 'show']); // Alternative route for ID
        Route::get('/{property:slug}', [PropertyController::class, 'show']);
        
        // Write operations - require authentication
        Route::post('/', [PropertyController::class, 'store'])->middleware(['auth:sanctum', 'validate.image']);
        Route::put('/{property}', [PropertyController::class, 'update'])->middleware(['auth:sanctum', 'validate.image']);
        Route::delete('/{property}', [PropertyController::class, 'destroy'])->middleware('auth:sanctum');

    });

    // Dashboard Routes - All require authentication
    Route::prefix('dashboard')->group(function () {
        Route::get('/overview', [DashboardController::class, 'overview']);
        Route::get('/stats', [DashboardController::class, 'stats']);
        // Removed the properties route as requested
        Route::get('/favorites', [DashboardController::class, 'favorites']);
        Route::get('/analytics', [DashboardController::class, 'analytics']);
        Route::post('/profile', [DashboardController::class, 'updateProfile']);
        Route::get('/notifications', [DashboardController::class, 'notifications']);
    });

    // Search and Filter Routes
    Route::prefix('search')->group(function () {
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::get('/suggestions', [SearchController::class, 'suggestions']);
    });

    // Location and Map Routes
    Route::prefix('locations')->group(function () {
        // Get all states with property counts
        Route::get('/states', 'App\Http\Controllers\Api\LocationController@getStates');
        
        // Get cities (optionally filtered by state)
        Route::get('/cities', 'App\Http\Controllers\Api\LocationController@getCities');
        
        // Get neighborhoods (optionally filtered by city and/or state)
        Route::get('/neighborhoods', 'App\Http\Controllers\Api\LocationController@getNeighborhoods');
    });

    // Cities Routes
    Route::prefix('cities')->group(function () {
        Route::get('/', 'App\Http\Controllers\Api\CityController@index');
        Route::get('/states', 'App\Http\Controllers\Api\CityController@getStates');
        Route::get('/by-state', 'App\Http\Controllers\Api\CityController@getCitiesByState');
        Route::get('/state/{state}', 'App\Http\Controllers\Api\CityController@getCitiesByStateParam');
        Route::get('/search', 'App\Http\Controllers\Api\CityController@search');
    });

    // Property Document Types Routes
    Route::prefix('property-document-types')->group(function () {
        Route::get('/', [PropertyDocumentTypeController::class, 'index']);
        Route::get('/all-languages', [PropertyDocumentTypeController::class, 'getAllLanguages']);
        Route::get('/{propertyDocumentType}', [PropertyDocumentTypeController::class, 'show']);
    });

    // Features Routes
    Route::prefix('features')->group(function () {
        Route::get('/', [FeatureController::class, 'index']);
        Route::get('/categories', [FeatureController::class, 'getCategories']);
        Route::get('/by-category/{category}', [FeatureController::class, 'getByCategory']);
        Route::get('/{feature}', [FeatureController::class, 'show']);
        
        // Admin routes (protected)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [FeatureController::class, 'store']);
            Route::put('/{feature}', [FeatureController::class, 'update']);
            Route::delete('/{feature}', [FeatureController::class, 'destroy']);
            Route::patch('/{feature}/toggle-status', [FeatureController::class, 'toggleStatus']);
            Route::patch('/sort-order', [FeatureController::class, 'updateSortOrder']);
        });
    });

    // Utilities Routes
    Route::prefix('utilities')->group(function () {
        Route::get('/', [UtilityController::class, 'index']);
        Route::get('/categories', [UtilityController::class, 'getCategories']);
        Route::get('/by-category/{category}', [UtilityController::class, 'getByCategory']);
        Route::get('/{utility}', [UtilityController::class, 'show']);
        
        // Admin routes (protected)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [UtilityController::class, 'store']);
            Route::put('/{utility}', [UtilityController::class, 'update']);
            Route::delete('/{utility}', [UtilityController::class, 'destroy']);
            Route::patch('/{utility}/toggle-status', [UtilityController::class, 'toggleStatus']);
            Route::patch('/sort-order', [UtilityController::class, 'updateSortOrder']);
        });
    });

    // Statistics and Analytics Routes
    Route::prefix('stats')->group(function () {
        Route::get('/overview', [StatsController::class, 'overview']);
        Route::get('/price-ranges', [StatsController::class, 'priceRanges']);
    });

    // Image Upload Routes - Bunny Storage
    Route::prefix('images')->middleware(['auth:sanctum', 'secure.image.upload'])->group(function () {
        Route::post('/upload', [ImageUploadController::class, 'uploadImage']);
        Route::post('/upload-multiple', [ImageUploadController::class, 'uploadMultipleImages']);
        Route::post('/upload-base64', [ImageUploadController::class, 'uploadBase64Image']);
        Route::delete('/delete', [ImageUploadController::class, 'deleteImage']);
        Route::get('/info', [ImageUploadController::class, 'getImageInfo']);
    });

    // Contact Routes
    Route::prefix('contact')->group(function () {
        // Public routes
        Route::post('/submit', [ContactController::class, 'store']);
        Route::get('/settings', [ContactController::class, 'getSettings']);
        
        // Admin routes for managing contact messages  
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/messages', [ContactController::class, 'index']);
            Route::get('/messages/{contactMessage}', [ContactController::class, 'show']);
            Route::patch('/messages/{contactMessage}/mark-spam', [ContactController::class, 'markAsSpam']);
            Route::patch('/messages/{contactMessage}/mark-read', [ContactController::class, 'markAsRead']);
            Route::delete('/messages/{contactMessage}', [ContactController::class, 'destroy']);
        });
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found.',
        'available_endpoints' => [
            'GET /api/health' => 'Health check',
            'POST /api/v1/auth/register' => 'User registration',
            'POST /api/v1/auth/login' => 'User login',
            'GET /api/v1/properties' => 'List properties with filters',
            'GET /api/v1/properties/featured' => 'Get featured properties',
            'GET /api/v1/search/properties' => 'Search properties',
            'GET /api/v1/dashboard/overview' => 'Dashboard overview (auth required)',
        ],
    ], 404);
});