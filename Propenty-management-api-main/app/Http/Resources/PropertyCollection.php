<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if this is a paginated result
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            // This is a paginated result - use items() to get the actual models
            $items = $this->resource->items();
            
            return [
                'data' => collect($items)->map(function ($property) {
                    // If this is already a PropertyResource, just return it
                    if ($property instanceof PropertyResource) {
                        return $property;
                    }
                    
                    // Create PropertyResource from Property model
                    return new PropertyResource($property);
                })->toArray(),
                'meta' => [
                    'total' => $this->resource->total(),
                    'count' => $this->resource->count(),
                    'per_page' => $this->resource->perPage(),
                    'current_page' => $this->resource->currentPage(),
                    'total_pages' => $this->resource->lastPage(),
                    'has_more_pages' => $this->resource->hasMorePages(),
                ],
                'links' => [
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl(),
                    'self' => $this->resource->url($this->resource->currentPage()),
                ]
            ];
        }
        
        // For simple collections, return a simpler structure
        return [
            'data' => $this->resource->map(function ($property) {
                return new PropertyResource($property);
            })->toArray(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Properties retrieved successfully.',
            'timestamp' => now()->toISOString(),
        ];
    }
    
    /**
     * Customize the pagination information.
     */
    public function paginationInformation($request, $paginated, $default)
    {
        // Use the default pagination information
        return $default;
    }
}