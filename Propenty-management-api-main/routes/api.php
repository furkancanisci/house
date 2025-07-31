<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Auth\AuthController;
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

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Property Management API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});

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
        
        // User info route - made public for now
        Route::get('/me', [AuthController::class, 'me']);
        
        // Protected authentication routes (only logout operations)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);
        });
    });

    // Property Routes - All made public for now
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::get('/featured', [PropertyController::class, 'featured']);
        Route::get('/amenities', [PropertyController::class, 'amenities']);
        Route::get('/{property:slug}', [PropertyController::class, 'show']);
        Route::get('/{property:slug}/similar', [PropertyController::class, 'similar']);
        
        // Write operations - keeping these for future reference but making them public for now
        Route::post('/', [PropertyController::class, 'store']);
        Route::put('/{property}', [PropertyController::class, 'update']);
        Route::delete('/{property}', [PropertyController::class, 'destroy']);
        Route::post('/{property}/favorite', [PropertyController::class, 'toggleFavorite']);
        Route::get('/{property}/analytics', [PropertyController::class, 'analytics']);
    });

    // Dashboard Routes - Made public for now
    Route::prefix('dashboard')->group(function () {
        Route::get('/overview', [DashboardController::class, 'overview']);
        Route::get('/properties', [DashboardController::class, 'properties']);
        Route::get('/favorites', [DashboardController::class, 'favorites']);
        Route::get('/analytics', [DashboardController::class, 'analytics']);
        
        // Keeping profile and notifications as protected since they're user-specific
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/profile', [DashboardController::class, 'updateProfile']);
            Route::get('/notifications', [DashboardController::class, 'notifications']);
        });
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

    // Statistics and Analytics Routes
    Route::prefix('stats')->group(function () {
        Route::get('/overview', [StatsController::class, 'overview']);
        Route::get('/price-ranges', [StatsController::class, 'priceRanges']);
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
