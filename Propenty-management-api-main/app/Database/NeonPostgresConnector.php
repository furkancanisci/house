<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;
use PDO;

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
            // Extract endpoint ID from hostname like "ep-dawn-hall-a9gz4on6-pooler.gwc.azure.neon.tech"
            $parts = explode('-', explode('.', $host)[0]);
            if (count($parts) >= 4) {
                $endpoint = implode('-', array_slice($parts, 0, 4));
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

        return array_diff_key($this->options, $options) + $options;
    }
}