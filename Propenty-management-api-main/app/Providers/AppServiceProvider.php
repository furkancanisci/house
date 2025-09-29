<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\ImageProcessingService::class, function ($app) {
            return new \App\Services\ImageProcessingService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Disable Sanctum's auto-migration to prevent conflicts
        \Laravel\Sanctum\Sanctum::ignoreMigrations();
        
        // Force HTTPS URLs in production
        if (config('app.env') === 'production' || request()->isSecure()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
