<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = new PDO(
        "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetch();
    echo "PostgreSQL version: " . $version['version'] . "\n";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}