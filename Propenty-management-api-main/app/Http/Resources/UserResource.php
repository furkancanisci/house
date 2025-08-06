<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'bio' => $this->bio,
            'user_type' => $this->user_type,
            'is_verified' => $this->is_verified,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'avatar' => [
                'url' => $this->avatar_url,
                'thumbnail' => $this->avatar_thumbnail_url,
            ],

            // Safe stats block to prevent infinite loops and N+1 queries
            'stats' => [
                'properties_count' => $this->whenLoaded('properties', fn () => $this->properties->count(), 0),
                'active_properties_count' => $this->whenLoaded('properties', fn () => $this->properties->where('status', 'active')->count(), 0),
                'favorites_count' => $this->whenLoaded('favoriteProperties', fn () => $this->favoriteProperties->count(), 0),
            ],

            'permissions' => [
                'can_create_property' => $this->isPropertyOwner(),
                'can_view_analytics' => $this->isPropertyOwner(),
                'can_manage_listings' => $this->isPropertyOwner(),
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
