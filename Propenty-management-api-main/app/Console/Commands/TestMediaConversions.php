<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;

class TestMediaConversions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:media-conversions {propertyId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test if media conversions are working for a property';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $propertyId = $this->argument('propertyId');
        
        $property = Property::find($propertyId);
        
        if (!$property) {
            $this->error("Property with ID {$propertyId} not found.");
            return 1;
        }
        
        $this->info("Testing media conversions for property: {$property->title}");
        
        // Get all media for this property
        $mediaItems = $property->getMedia('images')->concat($property->getMedia('main_image'));
        
        if ($mediaItems->isEmpty()) {
            $this->info("No media found for this property.");
            return 0;
        }
        
        foreach ($mediaItems as $media) {
            $this->info("Media ID: {$media->id}, Name: {$media->name}");
            
            // Check if conversions exist
            $conversions = [
                'small', 'thumbnail', 'medium', 'large', 'full'
            ];
            
            foreach ($conversions as $conversion) {
                $hasConversion = $media->hasGeneratedConversion($conversion);
                $status = $hasConversion ? 'YES' : 'NO';
                $this->line("  {$conversion}: {$status}");
                
                if ($hasConversion) {
                    $url = $media->getUrl($conversion);
                    $this->line("    URL: {$url}");
                }
            }
            
            $this->line("");
        }
        
        return 0;
    }
}