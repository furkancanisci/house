<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class PropertyPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        // Get the property ID from the model
        $propertyId = $media->model_id;
        
        // Create a folder structure: properties/{property_id}/
        return "properties/{$propertyId}/";
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        // Use the same path for conversions
        return $this->getPath($media) . 'conversions/';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        // Use the same path for responsive images
        return $this->getPath($media) . 'responsive/';
    }
}