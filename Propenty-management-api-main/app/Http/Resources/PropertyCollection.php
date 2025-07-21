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
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
                'self' => $this->url($this->currentPage()),
            ],
            'filters' => [
                'available_property_types' => [
                    'apartment',
                    'house', 
                    'condo',
                    'townhouse',
                    'studio',
                    'loft',
                    'villa',
                    'commercial',
                    'land'
                ],
                'available_listing_types' => [
                    'rent',
                    'sale'
                ],
                'bedroom_options' => [
                    0, 1, 2, 3, '4+'
                ],
                'bathroom_options' => [
                    1, 2, '3+'
                ],
                'price_ranges' => [
                    'rent' => [
                        ['min' => 0, 'max' => 1000, 'label' => 'Under $1,000'],
                        ['min' => 1000, 'max' => 2000, 'label' => '$1,000 - $2,000'],
                        ['min' => 2000, 'max' => 3000, 'label' => '$2,000 - $3,000'],
                        ['min' => 3000, 'max' => 5000, 'label' => '$3,000 - $5,000'],
                        ['min' => 5000, 'max' => null, 'label' => 'Over $5,000'],
                    ],
                    'sale' => [
                        ['min' => 0, 'max' => 100000, 'label' => 'Under $100K'],
                        ['min' => 100000, 'max' => 300000, 'label' => '$100K - $300K'],
                        ['min' => 300000, 'max' => 500000, 'label' => '$300K - $500K'],
                        ['min' => 500000, 'max' => 1000000, 'label' => '$500K - $1M'],
                        ['min' => 1000000, 'max' => null, 'label' => 'Over $1M'],
                    ],
                ],
            ],
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
}
