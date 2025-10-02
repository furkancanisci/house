<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BunnyStorageService
{
    private string $storageZone;
    private string $apiKey;
    private string $pullZone;
    private string $region;
    private string $cdnUrl;
    private string $baseUrl;

    public function __construct()
    {
        $this->storageZone = config('filesystems.disks.bunny.storage_zone');
        $this->apiKey = config('filesystems.disks.bunny.api_key');
        $this->pullZone = config('filesystems.disks.bunny.pull_zone');
        $this->region = config('filesystems.disks.bunny.region', 'de');
        $this->cdnUrl = config('filesystems.disks.bunny.cdn_url');
        
        // Set base URL based on region
        $this->baseUrl = $this->getStorageUrl();
    }

    /**
     * Get HTTP client with SSL configuration
     */
    private function getHttpClient()
    {
        $options = [];
        
        // Handle SSL certificate issues for local development
        if (app()->environment('local') || config('app.debug')) {
            $options['verify'] = false; // Disable SSL verification for local development
        }
        
        // Set timeout options
        $options['timeout'] = 30;
        $options['connect_timeout'] = 10;
        
        return Http::withOptions($options);
    }

    /**
     * Get Guzzle HTTP client for binary uploads
     */
    private function getGuzzleClient()
    {
        $options = [];
        
        // Handle SSL certificate issues for local development
        if (app()->environment('local') || config('app.debug')) {
            $options['verify'] = false; // Disable SSL verification for local development
        }
        
        // Set timeout options
        $options['timeout'] = 30;
        $options['connect_timeout'] = 10;
        
        return new \GuzzleHttp\Client($options);
    }

    /**
     * Get the appropriate storage URL based on region
     */
    private function getStorageUrl(): string
    {
        $regionUrls = [
            'de' => 'https://storage.bunnycdn.com',
            'ny' => 'https://ny.storage.bunnycdn.com',
            'la' => 'https://la.storage.bunnycdn.com',
            'sg' => 'https://sg.storage.bunnycdn.com',
            'syd' => 'https://syd.storage.bunnycdn.com',
            'uk' => 'https://uk.storage.bunnycdn.com'
        ];

        return $regionUrls[$this->region] ?? $regionUrls['de'];
    }

    /**
     * Upload file to Bunny Storage
     */
    public function uploadFile(UploadedFile $file, string $path): array
    {
        try {
            $url = $this->baseUrl . '/' . $this->storageZone . '/' . ltrim($path, '/');
            
            // Use Guzzle directly for binary file uploads to avoid JSON encoding issues
            $guzzle = $this->getGuzzleClient();
            $response = $guzzle->put($url, [
                'headers' => [
                    'AccessKey' => $this->apiKey,
                    'Content-Type' => $file->getMimeType(),
                ],
                'body' => $file->getContent()
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return [
                    'success' => true,
                    'path' => $path,
                    'cdn_url' => $this->getCdnUrl($path),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }

            Log::error('Bunny Storage upload failed', [
                'status' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
                'path' => $path
            ]);

            return [
                'success' => false,
                'error' => 'Upload failed: ' . $response->getBody()->getContents()
            ];

        } catch (\Exception $e) {
            Log::error('Bunny Storage upload exception', [
                'message' => $e->getMessage(),
                'path' => $path
            ]);

            return [
                'success' => false,
                'error' => 'Upload exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload raw content to Bunny Storage
     */
    public function uploadContent(string $content, string $path, string $mimeType = 'application/octet-stream'): array
    {
        try {
            $url = $this->baseUrl . '/' . $this->storageZone . '/' . ltrim($path, '/');
            
            // Use Guzzle directly for binary content uploads to avoid JSON encoding issues
            $guzzle = $this->getGuzzleClient();
            $response = $guzzle->put($url, [
                'headers' => [
                    'AccessKey' => $this->apiKey,
                    'Content-Type' => $mimeType,
                ],
                'body' => $content
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return [
                    'success' => true,
                    'path' => $path,
                    'cdn_url' => $this->getCdnUrl($path),
                    'size' => strlen($content),
                    'mime_type' => $mimeType
                ];
            }

            Log::error('Bunny Storage content upload failed', [
                'status' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
                'path' => $path
            ]);

            return [
                'success' => false,
                'error' => 'Upload failed: ' . $response->getBody()->getContents()
            ];

        } catch (\Exception $e) {
            Log::error('Bunny Storage content upload exception', [
                'message' => $e->getMessage(),
                'path' => $path
            ]);

            return [
                'success' => false,
                'error' => 'Upload exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete file from Bunny Storage
     */
    public function deleteFile(string $path): array
    {
        try {
            $url = $this->baseUrl . '/' . $this->storageZone . '/' . ltrim($path, '/');
            
            $response = $this->getHttpClient()->withHeaders([
                'AccessKey' => $this->apiKey,
            ])->delete($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'path' => $path
                ];
            }

            Log::error('Bunny Storage delete failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'path' => $path
            ]);

            return [
                'success' => false,
                'error' => 'Delete failed: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Bunny Storage delete exception', [
                'message' => $e->getMessage(),
                'path' => $path
            ]);

            return [
                'success' => false,
                'error' => 'Delete exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get CDN URL for a file
     */
    public function getCdnUrl(string $path): string
    {
        return rtrim($this->cdnUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Generate a unique filename with timestamp
     */
    public function generateUniqueFilename(string $originalName, string $prefix = ''): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Create a slug from the filename
        $slug = Str::slug($filename);
        
        // Add timestamp, microseconds and random string for uniqueness
        $timestamp = now()->format('YmdHis');
        $microseconds = substr(microtime(), 2, 6);
        $random = Str::random(8);
        
        $uniqueName = $prefix ? "{$prefix}_{$slug}_{$timestamp}_{$microseconds}_{$random}" : "{$slug}_{$timestamp}_{$microseconds}_{$random}";
        
        return $extension ? "{$uniqueName}.{$extension}" : $uniqueName;
    }

    /**
     * Check if file exists in Bunny Storage
     */
    public function fileExists(string $path): bool
    {
        try {
            $url = $this->baseUrl . '/' . $this->storageZone . '/' . ltrim($path, '/');
            
            $response = $this->getHttpClient()->withHeaders([
                'AccessKey' => $this->apiKey,
            ])->head($url);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Bunny Storage file exists check failed', [
                'message' => $e->getMessage(),
                'path' => $path
            ]);
            
            return false;
        }
    }

    /**
     * Get file info from Bunny Storage
     */
    public function getFileInfo(string $path): array
    {
        try {
            $url = $this->baseUrl . '/' . $this->storageZone . '/' . ltrim($path, '/');
            
            $response = $this->getHttpClient()->withHeaders([
                'AccessKey' => $this->apiKey,
            ])->head($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'path' => $path,
                    'cdn_url' => $this->getCdnUrl($path),
                    'size' => $response->header('Content-Length'),
                    'mime_type' => $response->header('Content-Type'),
                    'last_modified' => $response->header('Last-Modified')
                ];
            }

            return [
                'success' => false,
                'error' => 'File not found or access denied'
            ];

        } catch (\Exception $e) {
            Log::error('Bunny Storage file info failed', [
                'message' => $e->getMessage(),
                'path' => $path
            ]);
            
            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate configuration
     */
    public function validateConfig(): array
    {
        $errors = [];
        
        if (empty($this->storageZone)) {
            $errors[] = 'BUNNY_STORAGE_ZONE is not configured';
        }
        
        if (empty($this->apiKey)) {
            $errors[] = 'BUNNY_API_KEY is not configured';
        }
        
        if (empty($this->cdnUrl)) {
            $errors[] = 'BUNNY_CDN_URL is not configured';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}