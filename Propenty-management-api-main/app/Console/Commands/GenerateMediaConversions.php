<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Property;

class GenerateMediaConversions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:generate-conversions {mediaId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate conversions for media items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mediaId = $this->argument('mediaId');
        
        if ($mediaId) {
            $mediaItems = Media::where('id', $mediaId)->get();
        } else {
            // Get all media for properties
            $properties = Property::all();
            $mediaItems = collect();
            
            foreach ($properties as $property) {
                $mediaItems = $mediaItems->concat($property->getMedia('images'))->concat($property->getMedia('main_image'));
            }
        }
        
        if ($mediaItems->isEmpty()) {
            $this->info("No media items found.");
            return 0;
        }
        
        $this->info("Generating conversions for {$mediaItems->count()} media items...");
        
        $progressBar = $this->output->createProgressBar($mediaItems->count());
        $progressBar->start();
        
        $successCount = 0;
        
        foreach ($mediaItems as $media) {
            try {
                // Get the model that owns this media
                if ($media->model) {
                    // Register media conversions for this model
                    $media->model->registerMediaConversions($media);
                    
                    // Try to get URLs for each conversion to trigger generation
                    $conversions = ['small', 'thumbnail', 'medium', 'large', 'full'];
                    
                    foreach ($conversions as $conversion) {
                        try {
                            // This should trigger the conversion generation
                            $url = $media->getUrl($conversion);
                        } catch (\Exception $e) {
                            // Ignore conversion errors for now
                        }
                    }
                    
                    $successCount++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to generate conversions for media ID {$media->id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        $this->info("Successfully processed {$successCount} of {$mediaItems->count()} media items.");
        
        return 0;
    }
}