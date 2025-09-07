<?php

namespace App\Providers;

use App\Services\BunnyStorageService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;

class BunnyFilesystemServiceProvider extends ServiceProvider
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
        Storage::extend('bunny', function ($app, $config) {
            // Create BunnyStorageService instance
            $bunnyService = new BunnyStorageService(
                $config['storage_zone'],
                $config['api_key'],
                $config['pull_zone'],
                $config['region'] ?? 'de',
                $config['cdn_url']
            );

            // Use local adapter as base and override key methods
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $adapter = new class($bunnyService) implements \League\Flysystem\FilesystemAdapter {
                private $bunnyService;
                private $uploadedFiles = [];
                
                public function __construct($bunnyService) {
                    $this->bunnyService = $bunnyService;
                }

                public function fileExists(string $path): bool
                {
                    return $this->bunnyService->fileExists($path);
                }

                public function directoryExists(string $path): bool
                {
                    return true; // Bunny Storage doesn't have directories
                }
                
                public function url(string $path): string
                {
                    return $this->bunnyService->getCdnUrl($path);
                }

                /**
                 * Write file contents
                 */
                public function write(string $path, $contents, Config $config): void
                {
                    $result = $this->bunnyService->uploadContent($contents, $path, $config->get('mimetype', 'application/octet-stream'));
                    
                    if (!$result['success']) {
                        throw new UnableToWriteFile($result['error']);
                    }
                    
                    // Track uploaded files
                    $this->uploadedFiles[$path] = true;
                }

                /**
                 * Write file from stream
                 */
                public function writeStream(string $path, $resource, Config $config): void
                {
                    $contents = stream_get_contents($resource);
                    $this->write($path, $contents, $config);
                }

                public function delete(string $path): void
                {
                    $result = $this->bunnyService->deleteFile($path);
                    if (!$result['success']) {
                        throw new UnableToWriteFile('Failed to delete file from Bunny Storage: ' . ($result['error'] ?? 'Unknown error'));
                    }
                    unset($this->uploadedFiles[$path]);
                }

                public function deleteDirectory(string $path): void
                {
                    // Bunny Storage doesn't have directories, so this is a no-op
                }

                public function createDirectory(string $path, Config $config): void
                {
                    // Bunny Storage doesn't have directories, so this is a no-op
                }

                public function setVisibility(string $path, string $visibility): void
                {
                    // Bunny Storage files are always public
                }

                public function visibility(string $path): \League\Flysystem\FileAttributes
                {
                    return new \League\Flysystem\FileAttributes($path, null, 'public');
                }

                public function mimeType(string $path): \League\Flysystem\FileAttributes
                {
                    // Would need to implement file type detection
                    return new \League\Flysystem\FileAttributes($path, null, null, null, 'application/octet-stream');
                }

                public function lastModified(string $path): \League\Flysystem\FileAttributes
                {
                    // Would need to implement file info retrieval
                    return new \League\Flysystem\FileAttributes($path, null, null, time());
                }

                public function fileSize(string $path): \League\Flysystem\FileAttributes
                {
                    // Would need to implement file info retrieval
                    return new \League\Flysystem\FileAttributes($path, 0);
                }

                public function listContents(string $path, bool $deep): iterable
                {
                    // Would need to implement directory listing
                    return [];
                }

                public function move(string $source, string $destination, Config $config): void
                {
                    // Would need to implement copy + delete
                    throw new UnableToWriteFile('Move operation not implemented for Bunny Storage');
                }

                public function copy(string $source, string $destination, Config $config): void
                {
                    // Would need to implement file copying
                    throw new UnableToWriteFile('Copy operation not implemented for Bunny Storage');
                }

                /**
                 * Read file contents
                 */
                public function read(string $path): string
                {
                    throw new UnableToReadFile('Reading from Bunny Storage is not implemented');
                }

                /**
                 * Read file as stream
                 */
                public function readStream(string $path)
                {
                    throw new UnableToReadFile('Reading streams from Bunny Storage is not implemented');
                }
            };

            // Create filesystem with custom adapter
            $flysystem = new Filesystem($adapter);
            
            // Create custom FilesystemAdapter with URL generation
            $filesystem = new class($flysystem, $adapter, $config) extends FilesystemAdapter {
                public function url($path)
                {
                    return $this->adapter->url($path);
                }
            };
            
            return $filesystem;
        });
    }
}