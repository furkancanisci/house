<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Property extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'property_type',
        'listing_type',
        'price',
        'price_type',
        'street_address',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'neighborhood',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'lot_size',
        'year_built',
        'parking_type',
        'parking_spaces',
        'status',
        'is_featured',
        'is_available',
        'available_from',
        'slug',
        'amenities',
        'nearby_places',
        'contact_name',
        'contact_phone',
        'contact_email',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amenities' => 'array',
        'nearby_places' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'available_from' => 'date',
        'published_at' => 'datetime',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'full_address',
        'formatted_price',
        'main_image_url',
        'gallery_urls',
        'is_favorited_by_auth_user',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = $property->generateSlug();
            }
        });

        static::updating(function ($property) {
            if ($property->isDirty('title') && empty($property->slug)) {
                $property->slug = $property->generateSlug();
            }
        });
    }

    /**
     * Generate a unique slug for the property.
     */
    public function generateSlug(): string
    {
        $slug = Str::slug($this->title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Define media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('main_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * Define media conversions
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->performOnCollections('images', 'main_image');

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(400)
            ->performOnCollections('images', 'main_image');

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(800)
            ->performOnCollections('images', 'main_image');
    }

    /**
     * The property owner.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Users who favorited this property.
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'property_favorites')
            ->withTimestamps();
    }

    /**
     * Property views.
     */
    public function views()
    {
        return $this->hasMany(PropertyView::class);
    }

    /**
     * Get the property's full address.
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->street_address;
        if ($this->city) $address .= ', ' . $this->city;
        if ($this->state) $address .= ', ' . $this->state;
        if ($this->postal_code) $address .= ' ' . $this->postal_code;
        if ($this->country && $this->country !== 'US') $address .= ', ' . $this->country;

        return $address;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
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

    /**
     * Get the main image URL.
     */
    public function getMainImageUrlAttribute(): ?string
    {
        $mainImage = $this->getFirstMedia('main_image');
        if ($mainImage) {
            return $mainImage->getUrl();
        }

        $firstImage = $this->getFirstMedia('images');
        return $firstImage ? $firstImage->getUrl() : null;
    }

    /**
     * Get gallery image URLs.
     */
    public function getGalleryUrlsAttribute(): array
    {
        return $this->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
                'large' => $media->getUrl('large'),
            ];
        })->toArray();
    }

    /**
     * Check if property is favorited by authenticated user.
     */
    public function getIsFavoritedByAuthUserAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->favoritedByUsers()->where('user_id', auth()->id())->exists();
    }

    /**
     * Available amenities list.
     */
    public static function getAvailableAmenities(): array
    {
        return [
            'Air Conditioning',
            'Heating',
            'Dishwasher',
            'Laundry in Unit',
            'Laundry in Building',
            'Balcony',
            'Patio',
            'Garden',
            'Roof Deck',
            'Terrace',
            'Fireplace',
            'Hardwood Floors',
            'Carpet',
            'Tile Floors',
            'High Ceilings',
            'Walk-in Closet',
            'Storage',
            'Basement',
            'Attic',
            'Garage',
            'Parking',
            'Elevator',
            'Doorman',
            'Concierge',
            'Security System',
            'Intercom',
            'Video Security',
            'Gym',
            'Pool',
            'Hot Tub',
            'Sauna',
            'Tennis Court',
            'Basketball Court',
            'Playground',
            'Dog Park',
            'Pet Friendly',
            'No Pets',
            'Furnished',
            'Unfurnished',
            'Internet',
            'Cable TV',
            'Utilities Included',
            'Recently Renovated',
            'New Construction',
            'Outdoor Kitchen',
            'Master Suite',
            'Updated Kitchen',
            'Updated Bathroom',
            'Close to Transit',
            'Ocean View',
            'City View',
            'Private Elevator',
            'Spa',
            'Wine Cellar',
            'Smart Home',
            'Historic Details',
            'Bay Windows',
            'Crown Molding',
            'Community Pool',
            'Playground',
            'Washer/Dryer',
            'In-Unit Laundry',
            'Rooftop Deck',
            'Fitness Center',
            'Single Story',
            'Large Backyard',
            'Desert Landscaping',
        ];
    }

    /**
     * Scope for filtering by property type.
     */
    public function scopeOfType(Builder $query, $type): Builder
    {
        return $query->where('property_type', $type);
    }

    /**
     * Scope for filtering by listing type.
     */
    public function scopeForListing(Builder $query, $listingType): Builder
    {
        return $query->where('listing_type', $listingType);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeWithStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for active properties.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('is_available', true);
    }

    /**
     * Scope for featured properties.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for filtering by price range.
     */
    public function scopeInPriceRange(Builder $query, $minPrice = null, $maxPrice = null): Builder
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Scope for filtering by location.
     */
    public function scopeInLocation(Builder $query, $city = null, $state = null): Builder
    {
        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($state) {
            $query->where('state', 'like', "%{$state}%");
        }

        return $query;
    }

    /**
     * Scope for filtering by amenities.
     */
    public function scopeWithAmenities(Builder $query, array $amenities): Builder
    {
        foreach ($amenities as $amenity) {
            $query->whereJsonContains('amenities', $amenity);
        }

        return $query;
    }

    /**
     * Scope for filtering by bedrooms.
     */
    public function scopeWithBedrooms(Builder $query, $bedrooms): Builder
    {
        if ($bedrooms === '4+') {
            return $query->where('bedrooms', '>=', 4);
        }

        return $query->where('bedrooms', $bedrooms);
    }

    /**
     * Scope for filtering by bathrooms.
     */
    public function scopeWithBathrooms(Builder $query, $bathrooms): Builder
    {
        if ($bathrooms === '3+') {
            return $query->where('bathrooms', '>=', 3);
        }

        return $query->where('bathrooms', $bathrooms);
    }

    /**
     * Scope for searching by text.
     */
    public function scopeSearch(Builder $query, $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('street_address', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%")
              ->orWhere('state', 'like', "%{$search}%")
              ->orWhere('neighborhood', 'like', "%{$search}%");
        });
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get property stats.
     */
    public function getStatsAttribute(): array
    {
        return [
            'views_count' => $this->views_count,
            'favorites_count' => $this->favoritedByUsers()->count(),
            'images_count' => $this->getMedia('images')->count(),
        ];
    }
}
