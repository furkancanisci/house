<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;
use PDO;
use Exception;

class NeonPostgresConnector extends PostgresConnector
{
    /**
     * Create a DSN string from a configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        // If DATABASE_URL is provided, parse it and handle Neon-specific options
        if (isset($config['url']) && !empty($config['url'])) {
            return $this->parseNeonUrl($config['url']);
        }

        // Extract the endpoint from the host for Neon databases
        $host = $config['host'] ?? '';
        $endpoint = '';
        
        if (str_contains($host, 'neon.tech')) {
            // Extract endpoint ID from hostname like "ep-holy-tooth-a2z7soba-pooler.eu-central-1.aws.neon.tech"
            $hostPart = explode('.', $host)[0];
            if (str_ends_with($hostPart, '-pooler')) {
                // For pooler endpoints, use the full hostname part as the endpoint
                $endpoint = $hostPart;
            } else {
                // For direct endpoints, extract the first 4 parts
                $parts = explode('-', $hostPart);
                if (count($parts) >= 4) {
                    $endpoint = implode('-', array_slice($parts, 0, 4));
                }
            }
        }

        $dsn = "pgsql:host={$host}";

        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        if (isset($config['database'])) {
            $dsn .= ";dbname='{$config['database']}'";
        }

        if (isset($config['sslmode'])) {
            $dsn .= ";sslmode={$config['sslmode']}";
        }

        if ($endpoint) {
            $dsn .= ";options=endpoint={$endpoint}";
        }

        return $dsn;
    }

    /**
     * Parse Neon DATABASE_URL and create proper DSN
     *
     * @param  string  $url
     * @return string
     */
    protected function parseNeonUrl($url)
    {
        $parsed = parse_url($url);
        
        $dsn = "pgsql:host={$parsed['host']}";
        
        if (isset($parsed['port'])) {
            $dsn .= ";port={$parsed['port']}";
        }
        
        if (isset($parsed['path'])) {
            $database = ltrim($parsed['path'], '/');
            $dsn .= ";dbname='{$database}'";
        }
        
        // Parse query parameters
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $params);
            
            if (isset($params['sslmode'])) {
                $dsn .= ";sslmode={$params['sslmode']}";
            }
            
            if (isset($params['options'])) {
                $dsn .= ";options={$params['options']}";
            }
        }
        
        return $dsn;
    }

    /**
     * Get the PDO options based on the configuration.
     *
     * @param  array  $config
     * @return array
     */
    public function getOptions(array $config)
    {
        $options = $config['options'] ?? [];
        
        // Ensure options is always an array
        if (!is_array($options)) {
            $options = [];
        }

        // Add Neon-specific PDO options for better connection handling
        $neonOptions = [
            PDO::ATTR_TIMEOUT => $config['timeout'] ?? 60,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false, // Disable persistent connections for Neon
        ];

        return array_merge($this->options, $neonOptions, $options);
    }

    /**
     * Create a new PDO connection.
     *
     * @param  string  $dsn
     * @param  array   $config
     * @param  array   $options
     * @return \PDO
     */
    public function createConnection($dsn, array $config, array $options)
    {
        [$username, $password] = [
            $config['username'] ?? null, $config['password'] ?? null,
        ];

        try {
            return $this->createPdoConnection(
                $dsn, $username, $password, $options
            );
        } catch (Exception $e) {
            return $this->tryAgainIfCausedByLostConnection(
                $e, $dsn, $username, $password, $options
            );
        }
    }
}