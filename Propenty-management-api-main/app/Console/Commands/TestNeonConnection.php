<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class TestNeonConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:neon-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Neon database connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Neon database connection...');

        try {
            // Build DSN with endpoint parameter
            $host = env('DB_HOST');
            $port = env('DB_PORT', 5432);
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $sslmode = env('DB_SSLMODE', 'require');
            
            // Extract endpoint ID from host
            $endpointId = 'ep-dawn-hall-a9gz4on6';
            
            $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode={$sslmode};options=endpoint={$endpointId}";
            
            $this->info("DSN: {$dsn}");
            $this->info("Username: {$username}");
            
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);

            $this->info('✅ Connection successful!');
            
            // Test a simple query
            $stmt = $pdo->query('SELECT version()');
            $version = $stmt->fetchColumn();
            $this->info("PostgreSQL Version: {$version}");
            
            return 0;
            
        } catch (PDOException $e) {
            $this->error('❌ Connection failed: ' . $e->getMessage());
            return 1;
        }
    }
}
