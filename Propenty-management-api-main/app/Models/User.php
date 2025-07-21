<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'bio',
        'user_type',
        'is_verified',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Define media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Define media conversions
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->performOnCollections('avatar');

        $this->addMediaConversion('medium')
            ->width(300)
            ->height(300)
            ->performOnCollections('avatar');
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');
        return $media ? $media->getUrl() : null;
    }

    /**
     * Get the user's avatar thumbnail URL.
     */
    public function getAvatarThumbnailUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('avatar');
        return $media ? $media->getUrl('thumb') : null;
    }

    /**
     * Check if user is a property owner.
     */
    public function isPropertyOwner(): bool
    {
        return $this->user_type === 'property_owner';
    }

    /**
     * Check if user is a general user.
     */
    public function isGeneralUser(): bool
    {
        return $this->user_type === 'general_user';
    }

    /**
     * Properties owned by this user.
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Properties favorited by this user.
     */
    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'property_favorites')
            ->withTimestamps();
    }

    /**
     * Property views by this user.
     */
    public function propertyViews()
    {
        return $this->hasMany(PropertyView::class);
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified users.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter property owners.
     */
    public function scopePropertyOwners($query)
    {
        return $query->where('user_type', 'property_owner');
    }

    /**
     * Scope to filter general users.
     */
    public function scopeGeneralUsers($query)
    {
        return $query->where('user_type', 'general_user');
    }

    /**
     * Get user statistics.
     */
    public function getStatsAttribute(): array
    {
        return [
            'properties_count' => $this->properties()->count(),
            'active_properties_count' => $this->properties()->where('status', 'active')->count(),
            'favorites_count' => $this->favoriteProperties()->count(),
            'views_count' => $this->propertyViews()->count(),
        ];
    }
}
