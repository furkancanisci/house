<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;

class RegeneratePropertyMediaConversions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property:regenerate-media-conversions {--property= : Regenerate conversions for a specific property ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate media conversions for property images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $propertyId = $this->option('property');
        
        if ($propertyId) {
            $properties = Property::where('id', $propertyId)->get();
            if ($properties->isEmpty()) {
                $this->error("Property with ID {$propertyId} not found.");
                return 1;
            }
        } else {
            $properties = Property::all();
        }
        
        $this->info("Regenerating media conversions for " . $properties->count() . " properties...");
        
        $progressBar = $this->output->createProgressBar($properties->count());
        $progressBar->start();
        
        $totalProperties = $properties->count();
        $processedCount = 0;
        
        foreach ($properties as $property) {
            try {
                // Get all media for this property
                $mediaItems = $property->getMedia('images')->concat($property->getMedia('main_image'));
                
                // For each media item, trigger conversion regeneration
                foreach ($mediaItems as $media) {
                    // Call the model's registerMediaConversions method to ensure conversions are defined
                    if ($media->model) {
                        $media->model->registerMediaConversions($media);
                    }
                }
                
                $processedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to process property ID {$property->id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        $this->info("Successfully processed {$processedCount} of {$totalProperties} properties.");
        
        return 0;
    }
}