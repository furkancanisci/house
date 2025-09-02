<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ForceMediaConversion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:force-conversion {mediaId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force conversion generation for a specific media item';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mediaId = $this->argument('mediaId');
        
        $media = Media::find($mediaId);
        
        if (!$media) {
            $this->error("Media with ID {$mediaId} not found.");
            return 1;
        }
        
        $this->info("Forcing conversion generation for media: {$media->name}");
        
        try {
            // Reset manipulations
            $media->manipulations = [];
            $media->save();
            
            $this->info("Media manipulations reset successfully.");
            
            // Check if conversions exist
            $conversions = ['small', 'thumbnail', 'medium', 'large', 'full'];
            
            foreach ($conversions as $conversion) {
                $hasConversion = $media->hasGeneratedConversion($conversion);
                $status = $hasConversion ? 'YES' : 'NO';
                $this->line("  {$conversion}: {$status}");
            }
        } catch (\Exception $e) {
            $this->error("Failed to force conversion generation: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}