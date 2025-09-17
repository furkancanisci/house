<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyDocumentTypeController;
use App\Http\Controllers\Api\PropertyTypeController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuildingTypeController;
use App\Http\Controllers\Api\WindowTypeController;
use App\Http\Controllers\Api\FloorTypeController;
use App\Http\Controllers\Api\ViewTypeController;
use App\Http\Controllers\Api\DirectionController;
use App\Http\Controllers\Api\PropertyDetailController;
use App\Http\Controllers\Api\AdvancedDetailsController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\PropertyStatisticsController;
use App\Http\Controllers\SavedSearchController;
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
        // Public authentication routes with stricter rate limiting
        Route::post('/register', [AuthController::class, 'register'])->middleware('rate.limit:5:15'); // 5 attempts per 15 minutes
        Route::post('/login', [AuthController::class, 'login'])->middleware('rate.limit:10:15'); // 10 attempts per 15 minutes
        Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->middleware('rate.limit:3:60'); // 3 attempts per hour
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('rate.limit:5:60'); // 5 attempts per hour
        
        // Email verification routes
        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->name('verification.verify');
        Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
        Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail']);
        
        // Protected authentication routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
        
        // Additional protected routes (if needed)
        // Route::middleware('auth:sanctum')->group(function () {
        //     Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        //     Route::post('/refresh', [AuthController::class, 'refresh']);
        //     Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);
        //     Route::get('/email/verification-status', [AuthController::class, 'getVerificationStatus']);
        // });
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
        
        // Write operations - require authentication with rate limiting
        Route::post('/', [PropertyController::class, 'store'])->middleware(['auth:sanctum', 'validate.image', 'rate.limit:10:60']); // 10 properties per hour
        Route::put('/{property}', [PropertyController::class, 'update'])->middleware(['auth:sanctum', 'validate.image', 'rate.limit:30:60']); // 30 updates per hour
        Route::delete('/{property}', [PropertyController::class, 'destroy'])->middleware(['auth:sanctum', 'rate.limit:5:60']); // 5 deletions per hour

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

    // Property Types Routes
    Route::prefix('property-types')->group(function () {
        Route::get('/', [PropertyTypeController::class, 'index']);
        Route::get('/parents', [PropertyTypeController::class, 'parents']);
        Route::get('/children/{parentId?}', [PropertyTypeController::class, 'children']);
        Route::get('/options', [PropertyTypeController::class, 'options']);
        Route::get('/{propertyType}', [PropertyTypeController::class, 'show']);
    });

    // Building Types Routes
    Route::prefix('building-types')->group(function () {
        Route::get('/', [BuildingTypeController::class, 'index']);
        Route::get('/options', [BuildingTypeController::class, 'options']);
        Route::get('/with-counts', [BuildingTypeController::class, 'withCounts']);
        Route::get('/{buildingType}', [BuildingTypeController::class, 'show']);
    });

    // Window Types Routes
    Route::prefix('window-types')->group(function () {
        Route::get('/', [WindowTypeController::class, 'index']);
        Route::get('/options', [WindowTypeController::class, 'options']);
        Route::get('/with-counts', [WindowTypeController::class, 'withCounts']);
        Route::get('/{windowType}', [WindowTypeController::class, 'show']);
    });

    // Floor Types Routes
    Route::prefix('floor-types')->group(function () {
        Route::get('/', [FloorTypeController::class, 'index']);
        Route::get('/options', [FloorTypeController::class, 'options']);
        Route::get('/with-counts', [FloorTypeController::class, 'withCounts']);
        Route::get('/{floorType}', [FloorTypeController::class, 'show']);
    });

    // View Types Routes
    Route::prefix('view-types')->group(function () {
        Route::get('/', [ViewTypeController::class, 'index']);
        Route::get('/options', [ViewTypeController::class, 'options']);
        Route::get('/with-counts', [ViewTypeController::class, 'withCounts']);
        Route::get('/{viewType}', [ViewTypeController::class, 'show']);
    });

    // Directions Routes
    Route::prefix('directions')->group(function () {
        Route::get('/', [DirectionController::class, 'index']);
        Route::get('/options', [DirectionController::class, 'options']);
        Route::get('/with-counts', [DirectionController::class, 'withCounts']);
        Route::get('/{direction}', [DirectionController::class, 'show']);
    });

    // Property Details Routes - Combined endpoint for all types
    Route::prefix('property-details')->group(function () {
        Route::get('/building-types', [PropertyDetailController::class, 'getBuildingTypes']);
        Route::get('/window-types', [PropertyDetailController::class, 'getWindowTypes']);
        Route::get('/floor-types', [PropertyDetailController::class, 'getFloorTypes']);
        Route::get('/all-types', [PropertyDetailController::class, 'getAllTypes']);
    });

    // Advanced Details Routes - For property forms
    Route::prefix('advanced-details')->group(function () {
        Route::get('/building-types', [AdvancedDetailsController::class, 'getBuildingTypes']);
        Route::get('/window-types', [AdvancedDetailsController::class, 'getWindowTypes']);
        Route::get('/floor-types', [AdvancedDetailsController::class, 'getFloorTypes']);
        Route::get('/all', [AdvancedDetailsController::class, 'getAllAdvancedDetails']);
        Route::get('/statistics', [AdvancedDetailsController::class, 'getStatistics']);
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

    // Property Statistics Routes
    Route::prefix('property-statistics')->group(function () {
        // Public routes for tracking views
        Route::post('/{property}/track-view', [PropertyStatisticsController::class, 'trackView']);
        Route::get('/{property}/statistics', [PropertyStatisticsController::class, 'getPropertyStatistics']);
        Route::get('/popular', [PropertyStatisticsController::class, 'getPopularProperties']);
        
        // Protected routes for tracking inquiries and favorites
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/{property}/track-inquiry', [PropertyStatisticsController::class, 'trackInquiry']);
            Route::post('/{property}/track-favorite-add', [PropertyStatisticsController::class, 'trackFavoriteAdd']);
            Route::post('/{property}/track-favorite-remove', [PropertyStatisticsController::class, 'trackFavoriteRemove']);
            Route::get('/dashboard', [PropertyStatisticsController::class, 'getDashboardStatistics']);
        });
    });

    // Saved Searches Routes - All require authentication
    Route::prefix('saved-searches')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [SavedSearchController::class, 'index']);
        Route::post('/', [SavedSearchController::class, 'store']);
        Route::get('/{savedSearch}', [SavedSearchController::class, 'show']);
        Route::put('/{savedSearch}', [SavedSearchController::class, 'update']);
        Route::delete('/{savedSearch}', [SavedSearchController::class, 'destroy']);
        Route::post('/{savedSearch}/execute', [SavedSearchController::class, 'execute']);
        Route::get('/{savedSearch}/matching-count', [SavedSearchController::class, 'getMatchingCount']);
        Route::patch('/{savedSearch}/toggle-notification', [SavedSearchController::class, 'toggleNotification']);
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