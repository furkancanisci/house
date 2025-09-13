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
            'property_type' => $this->ensureUtf8($this->getAttributeValue('property_type')),
            'listing_type' => $this->ensureUtf8($this->listing_type),
            
            // Property details - flat structure for frontend compatibility
            'propertyType' => $this->getAttributeValue('property_type'), // Frontend expects camelCase
            'listingType' => $this->listing_type, // Frontend expects camelCase
            'bedrooms' => (int) ($this->bedrooms ?? 0),
            'bathrooms' => (float) ($this->bathrooms ?? 0),
            'square_feet' => (int) ($this->square_feet ?? 0),
            'squareFootage' => (int) ($this->square_feet ?? 0), // Frontend expects camelCase
            'year_built' => $this->year_built,
            'yearBuilt' => $this->year_built, // Frontend expects camelCase
            
            // Pricing - both nested and flat for compatibility
            'price' => $this->price, // Flat price for frontend
            'priceType' => $this->when($this->relationLoaded('priceType'), function () {
                return $this->priceType ? [
                    'key' => $this->priceType->key,
                    'name_ar' => $this->priceType->name_ar,
                    'name_en' => $this->priceType->name_en,
                    'name_ku' => $this->priceType->name_ku,
                    'localized_name' => $this->priceType->localized_name,
                ] : null;
            }),
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
            'city' => $this->ensureUtf8($this->getAttributeValue('city')),
            'state' => $this->ensureUtf8($this->getAttributeValue('state')),
            'postal_code' => $this->postal_code,
            'zip_code' => $this->postal_code, // Frontend expects zip_code
            'country' => $this->ensureUtf8('Syria'), // Hardcoded since country field was removed
            'neighborhood' => $this->ensureUtf8($this->neighborhood),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location' => [
                'street_address' => $this->ensureUtf8($this->street_address),
                'city' => $this->ensureUtf8($this->getAttributeValue('city')),
                'state' => $this->ensureUtf8($this->getAttributeValue('state')),
                'postal_code' => $this->ensureUtf8($this->postal_code),
                'country' => $this->ensureUtf8('Syria'), // Hardcoded since country field was removed
                'full_address' => $this->buildFullAddress(),
                'neighborhood' => $this->ensureUtf8($this->neighborhood),
                'coordinates' => [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ],
            ],
            
            // Features - ensure always returns array
            'features' => $this->when($this->relationLoaded('features'), function () {
                return $this->features->map(function ($feature) {
                    return [
                        'id' => $feature->id,
                        'name_ar' => $feature->name_ar,
                        'name_en' => $feature->name_en,
                        'name_ku' => $feature->name_ku,
                        'icon' => $feature->icon,
                        'category' => $feature->category,
                    ];
                })->toArray();
            }),
            'utilities' => $this->when($this->relationLoaded('utilities'), function () {
                return $this->utilities->map(function ($utility) {
                    return [
                        'id' => $utility->id,
                        'name_ar' => $utility->name_ar,
                        'name_en' => $utility->name_en,
                        'name_ku' => $utility->name_ku,
                        'icon' => $utility->icon,
                        'category' => $utility->category,
                    ];
                })->toArray();
            }),
            'nearby_places' => is_array($this->nearby_places) ? $this->nearby_places : 
                            (is_string($this->nearby_places) ? json_decode($this->nearby_places, true) : []),
            
            // Status
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available,
            'available_from' => $this->available_from?->format('Y-m-d'),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Media - optimized to prevent memory issues
            'images' => [
                'main' => $this->whenLoaded('media', function() {
                    $mainImage = $this->getFirstMedia('main_image');
                    if ($mainImage) {
                        return $mainImage->getUrl();
                    }
                    
                    $firstImage = $this->getFirstMedia('images');
                    return $firstImage ? $firstImage->getUrl() : null;
                }),
                'gallery' => $this->whenLoaded('media', function() {
                    return $this->getMedia('images')->map(function($media) {
                        return $media->getUrl();
                    })->toArray();
                }),
                'count' => $this->whenLoaded('media', function() {
                    return $this->getMedia('images')->count();
                }),
            ],
            'mainImage' => $this->whenLoaded('media', function() {
                $mainImage = $this->getFirstMedia('main_image');
                if ($mainImage) {
                    return $mainImage->getUrl();
                }
                
                $firstImage = $this->getFirstMedia('images');
                return $firstImage ? $firstImage->getUrl() : '/images/placeholder-property.svg';
            }),
            
            // Statistics - simplified
            'stats' => [
                'views_count' => $this->views_count ?? 0,
                'favorites_count' => 0, // Will be populated separately if needed
                'is_favorited' => false,
            ],
            'views_count' => $this->views_count ?? 0, // Frontend expects flat views_count
            
            // Contact information
            'contact' => [
                'name' => $this->ensureUtf8($this->contact_name ?? ''),
                'phone' => $this->contact_phone ?? '',
                'email' => $this->contact_email ?? '',
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
            
            // Document type information
            'document_type_id' => $this->document_type_id,
            'document_type' => $this->when($this->relationLoaded('documentType'), function () {
                return $this->documentType ? [
                    'id' => $this->documentType->id,
                    'name' => $this->documentType->getLocalizedName(request()->get('lang', 'ar')),
                    'description' => $this->documentType->getLocalizedDescription(request()->get('lang', 'ar')),
                    'sort_order' => $this->documentType->sort_order,
                ] : null;
            }),
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
        $city = $this->getAttributeValue('city');
        $state = $this->getAttributeValue('state');
        
        if ($city) $address .= ', ' . $this->ensureUtf8($city);
        if ($state) $address .= ', ' . $this->ensureUtf8($state);
        if ($this->postal_code) $address .= ' ' . $this->postal_code;
        $address .= ', Syria'; // Hardcoded since country field was removed

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