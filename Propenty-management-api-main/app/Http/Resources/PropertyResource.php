<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'property_type' => $this->property_type,
            'listing_type' => $this->listing_type,
            
            // Pricing
            'price' => [
                'amount' => $this->price,
                'formatted' => $this->formatted_price,
                'type' => $this->price_type,
                'currency' => 'USD',
            ],
            
            // Location
            'location' => [
                'street_address' => $this->street_address,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'full_address' => $this->full_address,
                'neighborhood' => $this->neighborhood,
                'coordinates' => [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ],
            ],
            
            // Property Details
            'details' => [
                'bedrooms' => $this->bedrooms,
                'bathrooms' => $this->bathrooms,
                'square_feet' => $this->square_feet,
                'lot_size' => $this->lot_size,
                'year_built' => $this->year_built,
                'parking' => [
                    'type' => $this->parking_type,
                    'spaces' => $this->parking_spaces,
                ],
            ],
            
            // Features
            'amenities' => $this->amenities ?: [],
            'nearby_places' => $this->nearby_places ?: [],
            
            // Status
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_available' => $this->is_available,
            'available_from' => $this->available_from?->format('Y-m-d'),
            'published_at' => $this->published_at?->toISOString(),
            
            // Media
            'images' => [
                'main' => $this->main_image_url,
                'gallery' => $this->gallery_urls,
                'count' => $this->getMedia('images')->count(),
            ],
            
            // Statistics
            'stats' => [
                'views_count' => $this->views_count,
                'favorites_count' => $this->whenLoaded('favoritedByUsers', function () {
                    return $this->favoritedByUsers->count();
                }, 0),
                'is_favorited' => $this->when(auth()->check(), function () {
                    // Use loaded relationship if available, otherwise return false to avoid query
                    if ($this->relationLoaded('favoritedByUsers')) {
                        return $this->favoritedByUsers->contains('id', auth()->id());
                    }
                    return false;
                }),
            ],
            
            // Contact information (only for property owner or when viewing property details)
            'contact' => [
                'name' => $this->contact_name,
                'phone' => $this->contact_phone,
                'email' => $this->contact_email,
            ],
            
            // Owner information
            'owner' => $this->when($this->relationLoaded('user'), function () {
                return new UserResource($this->user);
            }),
            
            // Permissions for current user
            'permissions' => [
                'can_edit' => auth()->check() && auth()->id() === $this->user_id,
                'can_delete' => auth()->check() && auth()->id() === $this->user_id,
                'can_view_analytics' => auth()->check() && auth()->id() === $this->user_id,
                'can_favorite' => auth()->check() && auth()->id() !== $this->user_id,
            ],
            
            // SEO
            'seo' => [
                'meta_title' => $this->title . ' - ' . $this->city . ', ' . $this->state,
                'meta_description' => substr($this->description, 0, 160) . '...',
                'canonical_url' => url("/properties/{$this->slug}"),
            ],
            
            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
