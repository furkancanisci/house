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

// Redirect login to admin login
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Redirect to admin panel for authenticated users
Route::get('/', function () {
    if (auth()->check() && auth()->user()->can('view dashboard')) {
        return redirect()->route('admin.dashboard');
    }
    return view('welcome');
}); 
