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
        // 'country', // Removed - Syria-only application
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
        'amenities_json',
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
        'amenities_json' => 'array',
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
        'listing_type',
        // Temporarily removing all accessors to debug the issue
        // 'full_address',
        // 'formatted_price',
        // 'main_image_url',
        // 'gallery_urls',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            $property->slug = $property->generateSlug();
        });

        static::updating(function ($property) {
            if ($property->isDirty('title')) {
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
     * Define media conversions with optimized settings for property photos
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Small thumbnail - 300x200 (3:2 ratio)
        $this->addMediaConversion('small')
            ->width(300)
            ->height(200)
            ->quality(70)
            ->format('webp')
            ->performOnCollections('images', 'main_image');

        // Standard thumbnail - 400x300 (4:3 ratio)
        $this->addMediaConversion('thumbnail')
            ->width(400)
            ->height(300)
            ->quality(70)
            ->format('webp')
            ->performOnCollections('images', 'main_image');

        // Medium size - 600x400 (3:2 ratio)
        $this->addMediaConversion('medium')
            ->width(600)
            ->height(400)
            ->quality(75)
            ->format('webp')
            ->performOnCollections('images', 'main_image');

        // Large size - 800x533 (3:2 ratio)
        $this->addMediaConversion('large')
            ->width(800)
            ->height(533)
            ->quality(80)
            ->format('webp')
            ->performOnCollections('images', 'main_image');

        // Full size - 1200x800 (3:2 ratio) - optimal for property photos
        $this->addMediaConversion('full')
            ->width(1200)
            ->height(800)
            ->quality(85)
            ->format('webp')
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
     * The city relationship.
     */
    public function city()
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    /**
     * The property type relationship.
     */
    public function propertyType()
    {
        return $this->belongsTo(\App\Models\PropertyType::class);
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
     * The amenities associated with this property.
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities')
            ->withTimestamps();
    }

    /**
     * Get the property's full address.
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->ensureUtf8($this->street_address);
        if ($this->city) $address .= ', ' . $this->ensureUtf8($this->city);
        if ($this->state) $address .= ', ' . $this->ensureUtf8($this->state);
        if ($this->postal_code) $address .= ' ' . $this->postal_code;
        // Country field removed - Syria-only application

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
     * Get gallery image URLs with all size variants.
     */
    public function getGalleryUrlsAttribute(): array
    {
        return $this->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'original' => $media->getUrl(),
                'full' => $media->getUrl('full'),
                'large' => $media->getUrl('large'),
                'medium' => $media->getUrl('medium'),
                'thumbnail' => $media->getUrl('thumbnail'),
                'small' => $media->getUrl('small'),
                'alt_text' => $media->name ?? 'Property Image',
                'file_size' => $media->size,
                'mime_type' => $media->mime_type,
            ];
        })->toArray();
    }

    /**
     * Get listing type from property type relationship.
     */
    public function getListingTypeAttribute(): string
    {
        if ($this->property_type_id) {
            $propertyType = \App\Models\PropertyType::find($this->property_type_id);
            if ($propertyType) {
                if ($propertyType->slug === 'for-sale') {
                    return 'sale';
                } elseif ($propertyType->slug === 'for-rent') {
                    return 'rent';
                }
            }
        }
        return 'sale'; // default
    }

    /**
     * Set listing type by updating property_type_id.
     */
    public function setListingTypeAttribute($value)
    {
        $propertyTypeSlug = $value === 'sale' ? 'for-sale' : 'for-rent';
        $propertyType = \App\Models\PropertyType::where('slug', $propertyTypeSlug)->first();
        if ($propertyType) {
            $this->property_type_id = $propertyType->id;
        }
    }

    /**
     * Get city name from relationship.
     */
    public function getCityAttribute()
    {
        if ($this->relationLoaded('city') && $this->getRelation('city')) {
            $city = $this->getRelation('city');
            return $city->name ?? $city->name_en ?? $city->name_ar;
        }
        if ($this->city_id) {
            $city = \App\Models\City::find($this->city_id);
            return $city ? ($city->name ?? $city->name_en ?? $city->name_ar) : null;
        }
        return null;
    }

    /**
     * Get state from city relationship.
     */
    public function getStateAttribute()
    {
        if ($this->relationLoaded('city') && $this->getRelation('city')) {
            return $this->getRelation('city')->state ?? 'Syria';
        }
        if ($this->city_id) {
            return \App\Models\City::find($this->city_id)?->state ?? 'Syria';
        }
        return 'Syria';
    }

    /**
     * Get property type name from relationship.
     */
    public function getPropertyTypeAttribute()
    {
        if ($this->relationLoaded('propertyType') && $this->getRelation('propertyType')) {
            return $this->getRelation('propertyType')->name;
        }
        if ($this->property_type_id) {
            return \App\Models\PropertyType::find($this->property_type_id)?->name;
        }
        return null;
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
        // Find property type by name or slug
        $propertyType = \App\Models\PropertyType::where('name', $type)
            ->orWhere('slug', $type)
            ->first();
        
        if ($propertyType) {
            return $query->where('property_type_id', $propertyType->id);
        }
        
        return $query;
    }

    /**
     * Scope for filtering by listing type.
     */
    public function scopeForListing(Builder $query, $listingType): Builder
    {
        // Map listing type to property type
        $propertyTypeSlug = $listingType === 'sale' ? 'for-sale' : 'for-rent';
        $propertyType = \App\Models\PropertyType::where('slug', $propertyTypeSlug)->first();
        
        if ($propertyType) {
            return $query->where('property_type_id', $propertyType->id);
        }
        
        return $query;
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
