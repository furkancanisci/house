<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'country_ar',
        'country_en',
        'state_ar',
        'state_en',
        'latitude',
        'longitude',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    /**
     * Get the city name based on locale
     */
    public function getName($locale = 'ar')
    {
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Get the name attribute (default to Arabic name for backward compatibility)
     */
    public function getNameAttribute()
    {
        return $this->name_ar ?: $this->name_en;
    }

    /**
     * Get the country name based on locale
     */
    public function getCountry($locale = 'ar')
    {
        return $locale === 'ar' ? $this->country_ar : $this->country_en;
    }

    /**
     * Get the state name based on locale
     */
    public function getState($locale = 'ar')
    {
        return $locale === 'ar' ? $this->state_ar : $this->state_en;
    }

    /**
     * Scope for active cities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filtering by country
     */
    public function scopeByCountry($query, $country, $locale = 'ar')
    {
        $field = $locale === 'ar' ? 'country_ar' : 'country_en';
        return $query->where($field, $country);
    }

    /**
     * Scope for filtering by state
     */
    public function scopeByState($query, $state, $locale = 'ar')
    {
        $field = $locale === 'ar' ? 'state_ar' : 'state_en';
        return $query->where($field, $state);
    }

    /**
     * Properties in this city
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'city', 'name_ar');
    }

    /**
     * Neighborhoods in this city
     */
    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }
}