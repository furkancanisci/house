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
            'first_name' => $this->ensureUtf8($this->first_name),
            'last_name' => $this->ensureUtf8($this->last_name),
            'full_name' => $this->ensureUtf8($this->full_name),
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'bio' => $this->ensureUtf8($this->bio),
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

    /**
     * Ensure proper UTF-8 encoding for text fields
     */
    private function ensureUtf8($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        if (!is_string($value)) {
            return $value;
        }
        
        // Check if the string is already valid UTF-8
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }
        
        // Try to convert from common encodings to UTF-8
        $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'];
        
        foreach ($encodings as $encoding) {
            $converted = mb_convert_encoding($value, 'UTF-8', $encoding);
            if (mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }
        
        // If all else fails, remove invalid characters
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
