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

            // ✅ Sonsuz döngüyü engelleyen güvenli stats bloğu
            'stats' => isset($this->properties)
                ? [
                    'properties_count' => $this->properties->count(),
                    'active_properties_count' => $this->properties->where('status', 'active')->count(),
                    // 🛑 Dikkat: favoriteProperties() yerine önceden yüklenmişse geç
                    'favorites_count' => $this->whenLoaded('favoriteProperties', fn () => $this->favoriteProperties->count()),
                ]
                : null,

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
