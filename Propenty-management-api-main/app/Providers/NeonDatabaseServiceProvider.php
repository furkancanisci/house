<?php

namespace App\Providers;

use App\Database\NeonPostgresConnector;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\DatabaseManager;

class NeonDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Override the default PostgreSQL connector
        $this->app->bind('db.connector.pgsql', function () {
            return new NeonPostgresConnector();
        });
    }
}