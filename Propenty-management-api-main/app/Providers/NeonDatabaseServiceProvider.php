<?php

namespace App\Providers;

use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\PostgresConnection;
use PDO;

class NeonDatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->app['db']->extend('neon-pgsql', function ($config, $name) {
            $connector = new class extends PostgresConnector {
                public function connect(array $config)
                {
                    // Build DSN with Neon endpoint
                    $host = $config['host'] ?? 'localhost';
                    $port = $config['port'] ?? 5432;
                    $database = $config['database'] ?? 'forge';
                    
                    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
                    
                    // Add Neon endpoint parameter for Neon hosts
                    if (str_contains($host, 'neon.tech')) {
                        // Extract endpoint ID from the host
                        $hostParts = explode('-', explode('.', $host)[0]);
                        if (count($hostParts) >= 4) {
                            $endpointId = implode('-', array_slice($hostParts, 0, 4));
                            $dsn .= ";options=endpoint={$endpointId}";
                        }
                    }
                    
                    $username = $config['username'] ?? 'forge';
                    $password = $config['password'] ?? '';
                    
                    $options = [
                        PDO::ATTR_CASE => PDO::CASE_NATURAL,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
                        PDO::ATTR_STRINGIFY_FETCHES => false,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ];
                    
                    // Merge with any additional options from config
                    if (isset($config['options'])) {
                        $options = array_merge($options, $config['options']);
                    }
                    
                    $pdo = new PDO($dsn, $username, $password, $options);
                    
                    return $pdo;
                }
            };

            $connection = $connector->connect($config);
            
            return new PostgresConnection($connection, $config['database'], $config['prefix'] ?? '', $config);
        });
    }
}