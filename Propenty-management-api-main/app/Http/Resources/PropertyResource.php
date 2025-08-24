<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    /**
     * Ensure proper UTF-8 encoding for a string
     */
    protected function ensureUtf8($value)
    {
        if (is_string($value)) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        if (is_array($value)) {
            return array_map([$this, 'ensureUtf8'], $value);
        }
        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->ensureUtf8($value->toArray());
        }
        return $value;
    }

    public function toArray(Request $request): array
    {
        // Ensure all string values are properly UTF-8 encoded
        $data = [
            'id' => $this->id,
            'title' => $this->ensureUtf8($this->title),
            'description' => $this->ensureUtf8($this->description),
            'slug' => $this->ensureUtf8($this->slug),
            'property_type' => $this->ensureUtf8($this->property_type),
            'listing_type' => $this->ensureUtf8($this->listing_type),
            
            // Property details - flat structure for frontend compatibility
            'propertyType' => $this->property_type, // Frontend expects camelCase
            'listingType' => $this->listing_type, // Frontend expects camelCase
            'bedrooms' => (int) ($this->bedrooms ?? 0),
            'bathrooms' => (float) ($this->bathrooms ?? 0),
            'square_feet' => (int) ($this->square_feet ?? 0),
            'squareFootage' => (int) ($this->square_feet ?? 0), // Frontend expects camelCase
            'year_built' => $this->year_built,
            'yearBuilt' => $this->year_built, // Frontend expects camelCase
            
            // Pricing - both nested and flat for compatibility
            'price' => $this->price, // Flat price for frontend
            'pricing' => [
                'amount' => $this->price,
                'formatted' => $this->buildFormattedPrice(),
                'type' => $this->ensureUtf8($this->price_type ?? 'total'),
                'currency' => 'USD',
            ],
            
            // Location - both nested and flat for compatibility
            'address' => $this->buildFullAddress(), // Frontend expects flat address
            'full_address' => $this->buildFullAddress(), // Alternative field name
            'street_address' => $this->ensureUtf8($this->street_address),
            'city' => $this->ensureUtf8($this->city),
            'state' => $this->ensureUtf8($this->state),
            'postal_code' => $this->postal_code,
            'zip_code' => $this->postal_code, // Frontend expects zip_code
            'country' => $this->ensureUtf8($this->country ?? 'Syria'),
            'neighborhood' => $this->ensureUtf8($this->neighborhood),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location' => [
                'street_address' => $this->ensureUtf8($this->street_address),
                'city' => $this->ensureUtf8($this->city),
                'state' => $this->ensureUtf8($this->state),
                'postal_code' => $this->ensureUtf8($this->postal_code),
                'country' => $this->ensureUtf8($this->country ?? 'Syria'),
                'full_address' => $this->buildFullAddress(),
                'neighborhood' => $this->ensureUtf8($this->neighborhood),
                'coordinates' => [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ],
            ],
            
            // Features
            'amenities' => $this->amenities ?: [],
            'features' => $this->amenities ?: [], // Frontend expects features
            'nearby_places' => $this->nearby_places ?: [],
            
            // Status
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available,
            'available_from' => $this->available_from?->format('Y-m-d'),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Media - simplified to avoid memory issues
            'images' => [
                'main' => $this->getFirstMedia('main_image')?->getUrl() ?: 
                         ($this->getFirstMedia('images')?->getUrl() ?: null),
                'gallery' => $this->getMedia('images')->map(function($media) {
                    return $media->getUrl();
                })->toArray(),
                'count' => $this->getMedia('images')->count(),
            ],
            'mainImage' => $this->getFirstMedia('main_image')?->getUrl() ?: 
                          ($this->getFirstMedia('images')?->getUrl() ?: '/placeholder-property.jpg'),
            
            // Statistics - simplified
            'stats' => [
                'views_count' => $this->views_count ?? 0,
                'favorites_count' => 0, // Will be populated separately if needed
                'is_favorited' => false,
            ],
            'views_count' => $this->views_count ?? 0, // Frontend expects flat views_count
            
            // Contact information
            'contact' => [
                'name' => $this->ensureUtf8($this->contact_name),
                'phone' => $this->contact_phone,
                'email' => $this->contact_email,
            ],
            
            // Owner information
            'owner' => $this->when($this->relationLoaded('user'), function () use ($request) {
                return (new UserResource($this->user))->toArray($request);
            }),
            
            // Permissions for current user
            'permissions' => [
                'can_edit' => auth()->check() && auth()->id() === $this->user_id,
                'can_delete' => auth()->check() && auth()->id() === $this->user_id,
            ],
            
            // User ID for ownership checks
            'user_id' => $this->user_id,
        ];

        // Ensure all string values in the response are properly UTF-8 encoded
        return $this->ensureUtf8($data);
    }

    /**
     * Build full address without calling accessor
     */
    private function buildFullAddress()
    {
        $address = $this->ensureUtf8($this->street_address);
        if ($this->city) $address .= ', ' . $this->ensureUtf8($this->city);
        if ($this->state) $address .= ', ' . $this->ensureUtf8($this->state);
        if ($this->postal_code) $address .= ' ' . $this->postal_code;
        if ($this->country && $this->country !== 'US') $address .= ', ' . $this->ensureUtf8($this->country);

        return $address;
    }

    /**
     * Build formatted price without calling accessor
     */
    private function buildFormattedPrice()
    {
        $price = '$' . number_format($this->price);
        
        if ($this->listing_type === 'rent') {
            switch ($this->price_type) {
                case 'monthly':
                    return $price . '/month';
                case 'yearly':
                    return $price . '/year';
                default:
                    return $price;
            }
        }

        return $price;
    }

}
