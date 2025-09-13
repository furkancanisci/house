<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'name_ku',
        'key',
        'listing_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get properties that use this price type
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'price_type_id');
    }

    /**
     * Scope to get only active price types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get price types by listing type
     */
    public function scopeForListingType($query, $listingType)
    {
        return $query->where('listing_type', $listingType)
                    ->orWhere('listing_type', 'both');
    }

    /**
     * Get localized name attribute
     */
    public function getLocalizedNameAttribute()
    {
        $locale = app()->getLocale();
        
        switch ($locale) {
            case 'ar':
                return $this->name_ar ?: $this->name_en;
            case 'ku':
                return $this->name_ku ?: $this->name_en;
            default:
                return $this->name_en;
        }
    }

    /**
     * Get translated price type by key
     * This method provides translation for price types stored as keys in properties
     */
    public static function getTranslatedPriceType($key)
    {
        // First try to find in database
        $priceType = static::where('key', $key)->first();
        if ($priceType) {
            return $priceType->localized_name;
        }

        // Fallback to translation keys for backward compatibility
        $translations = [
            'monthly' => __('admin.monthly'),
            'yearly' => __('admin.yearly'),
            'total' => __('admin.total_price'),
            'fixed' => __('admin.fixed_price'),
            'negotiable' => __('admin.negotiable'),
            'final_price' => __('admin.final_price'),
            'popular_saying' => __('admin.popular_saying'),
            'price_from_last' => __('admin.price_from_last'),
        ];

        return $translations[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get all active price types for dropdown
     */
    public static function getForDropdown($listingType = null)
    {
        $query = static::active();
        
        if ($listingType) {
            $query->where(function($q) use ($listingType) {
                $q->where('listing_type', $listingType)
                  ->orWhere('listing_type', 'both');
            });
        }
        
        return $query->get()->pluck('localized_name', 'key');
    }
}
