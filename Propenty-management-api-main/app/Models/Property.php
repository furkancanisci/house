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
        'property_type_id',
        'listing_type',
        'price',
        'price_type',
        'street_address',
        'city',
        'state',
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

        'nearby_places',
        'contact_name',
        'contact_phone',
        'contact_email',
        'published_at',
        'document_type_id',

        // Foreign key fields
        'governorate_id',
        'city_id',
        'neighborhood_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [

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
        // Temporarily removing to debug the issue
        // 'listing_type',
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
     * Define media conversions - DISABLED to avoid read operations on Bunny Storage
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Conversions disabled because Bunny Storage adapter doesn't support reading files
        // This prevents the media library from trying to process images
    }

    /**
     * The property owner.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The governorate relationship.
     */
    public function governorate()
    {
        return $this->belongsTo(\App\Models\Governorate::class);
    }

    /**
     * The city relationship.
     */
    public function city()
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    /**
     * The neighborhood relationship.
     */
    public function neighborhood()
    {
        return $this->belongsTo(\App\Models\Neighborhood::class);
    }

    /**
     * The property type relationship.
     */
    public function propertyType()
    {
        return $this->belongsTo(\App\Models\PropertyType::class);
    }

    /**
     * The price type relationship.
     */
    public function priceType()
    {
        return $this->belongsTo(\App\Models\PriceType::class, 'price_type', 'key');
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
     * The features associated with this property.
     */
    public function features()
    {
        return $this->belongsToMany(Feature::class, 'property_features')
            ->withTimestamps();
    }

    /**
     * The utilities associated with this property.
     */
    public function utilities()
    {
        return $this->belongsToMany(Utility::class, 'property_utilities')
            ->withTimestamps();
    }

    /**
     * Property document type.
     */
    public function documentType()
    {
        return $this->belongsTo(PropertyDocumentType::class, 'document_type_id');
    }

    /**
     * Get the property's full address.
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->ensureUtf8($this->street_address);
        if ($this->city) $address .= ', ' . $this->ensureUtf8($this->city);
        if ($this->state) $address .= ', ' . $this->ensureUtf8($this->state);
        // Postal code field removed from database
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
                'full' => $media->hasGeneratedConversion('full') ? $media->getUrl('full') : $media->getUrl(),
                'large' => $media->hasGeneratedConversion('large') ? $media->getUrl('large') : $media->getUrl(),
                'medium' => $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : $media->getUrl(),
                'thumbnail' => $media->hasGeneratedConversion('thumbnail') ? $media->getUrl('thumbnail') : $media->getUrl(),
                'small' => $media->hasGeneratedConversion('small') ? $media->getUrl('small') : $media->getUrl(),
                'alt_text' => $media->name ?? 'Property Image',
                'file_size' => $media->size,
                'mime_type' => $media->mime_type,
            ];
        })->toArray();
    }

    /**
     * Get listing type from property type relationship.
     * TEMPORARILY DISABLED - Using direct listing_type field instead
     */
    /* 
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
    */

    /**
     * Set listing type by updating property_type_id.
     * TEMPORARILY DISABLED - Using direct listing_type field instead
     */
    /*
    public function setListingTypeAttribute($value)
    {
        $propertyTypeSlug = $value === 'sale' ? 'for-sale' : 'for-rent';
        $propertyType = \App\Models\PropertyType::where('slug', $propertyTypeSlug)->first();
        if ($propertyType) {
            $this->property_type_id = $propertyType->id;
        }
    }
    */

    /**
     * Get the city name from the related city model
     * Fixed to prevent infinite recursion
     */
    public function getCityAttribute()
    {
        // Check if we're already in the middle of accessing this attribute
        if (isset($this->accessingCity)) {
            return $this->attributes['city'] ?? null;
        }
        
        $this->accessingCity = true;
        
        try {
            if ($this->relationLoaded('city') && $this->getRelation('city')) {
                $result = $this->getRelation('city')->getLocalizedName(request()->get('lang', 'ar'));
                unset($this->accessingCity);
                return $result;
            }
            
            // Fallback to database value if relation not loaded
            $result = $this->attributes['city'] ?? null;
            unset($this->accessingCity);
            return $result;
        } catch (\Exception $e) {
            unset($this->accessingCity);
            return $this->attributes['city'] ?? null;
        }
    }

    /**
     * Get the state name from the related governorate model
     * Fixed to prevent infinite recursion
     */
    public function getStateAttribute()
    {
        // Check if we're already in the middle of accessing this attribute
        if (isset($this->accessingState)) {
            return $this->attributes['state'] ?? null;
        }
        
        $this->accessingState = true;
        
        try {
            if ($this->relationLoaded('governorate') && $this->getRelation('governorate')) {
                $result = $this->getRelation('governorate')->getLocalizedName(request()->get('lang', 'ar'));
                unset($this->accessingState);
                return $result;
            }
            
            // Fallback to database value if relation not loaded
            $result = $this->attributes['state'] ?? null;
            unset($this->accessingState);
            return $result;
        } catch (\Exception $e) {
            unset($this->accessingState);
            return $this->attributes['state'] ?? null;
        }
    }

    /**
     * Get the property type name from the related property type model
     * Fixed to prevent infinite recursion
     */
    public function getPropertyTypeAttribute()
    {
        // Check if we're already in the middle of accessing this attribute
        if (isset($this->accessingPropertyType)) {
            return $this->attributes['property_type'] ?? null;
        }
        
        $this->accessingPropertyType = true;
        
        try {
            if ($this->relationLoaded('propertyType') && $this->getRelation('propertyType')) {
                $result = $this->getRelation('propertyType')->getLocalizedName(request()->get('lang', 'ar'));
                unset($this->accessingPropertyType);
                return $result;
            }
            
            // Fallback to database value if relation not loaded
            $result = $this->attributes['property_type'] ?? null;
            unset($this->accessingPropertyType);
            return $result;
        } catch (\Exception $e) {
            unset($this->accessingPropertyType);
            return $this->attributes['property_type'] ?? null;
        }
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
     * Ensure proper UTF-8 encoding for a string
     */
    protected function ensureUtf8($value)
    {
        if (is_string($value)) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        return $value;
    }
}
