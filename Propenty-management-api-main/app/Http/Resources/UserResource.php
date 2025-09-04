<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
            'email_verified_at' => $this->getEmailVerifiedAt(),
            'last_login_at' => $this->getLastLoginAt(),
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
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
        ];
    }

    /**
     * Get email verified at timestamp
     */
    private function getEmailVerifiedAt()
    {
        if (!$this->email_verified_at) {
            return null;
        }
        
        // If it's already a Carbon/DateTime object
        if (is_object($this->email_verified_at) && method_exists($this->email_verified_at, 'toISOString')) {
            return $this->email_verified_at->toISOString();
        }
        
        // If it's a string, try to parse it
        if (is_string($this->email_verified_at)) {
            try {
                return Carbon::parse($this->email_verified_at)->toISOString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    /**
     * Get last login at timestamp
     */
    private function getLastLoginAt()
    {
        if (!$this->last_login_at) {
            return null;
        }
        
        // If it's already a Carbon/DateTime object
        if (is_object($this->last_login_at) && method_exists($this->last_login_at, 'toISOString')) {
            return $this->last_login_at->toISOString();
        }
        
        // If it's a string, try to parse it
        if (is_string($this->last_login_at)) {
            try {
                return Carbon::parse($this->last_login_at)->toISOString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    /**
     * Get created at timestamp
     */
    private function getCreatedAt()
    {
        // If it's already a Carbon/DateTime object
        if (is_object($this->created_at) && method_exists($this->created_at, 'toISOString')) {
            return $this->created_at->toISOString();
        }
        
        // If it's a string, try to parse it
        if (is_string($this->created_at)) {
            try {
                return Carbon::parse($this->created_at)->toISOString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    /**
     * Get updated at timestamp
     */
    private function getUpdatedAt()
    {
        // If it's already a Carbon/DateTime object
        if (is_object($this->updated_at) && method_exists($this->updated_at, 'toISOString')) {
            return $this->updated_at->toISOString();
        }
        
        // If it's a string, try to parse it
        if (is_string($this->updated_at)) {
            try {
                return Carbon::parse($this->updated_at)->toISOString();
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
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