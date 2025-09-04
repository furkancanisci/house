<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
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
        'name_ku',
        'slug',
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
     * Get the governorate name based on locale
     */
    public function getName($locale = 'ar')
    {
        switch ($locale) {
            case 'ku':
                return $this->name_ku ?: $this->name_ar;
            case 'en':
                return $this->name_en;
            case 'ar':
            default:
                return $this->name_ar;
        }
    }

    /**
     * Get the name attribute (default to Arabic name for backward compatibility)
     */
    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        switch ($locale) {
            case 'ku':
                return $this->attributes['name_ku'] ?: $this->attributes['name_ar'] ?: $this->attributes['name_en'];
            case 'en':
                return $this->attributes['name_en'] ?: $this->attributes['name_ar'];
            case 'ar':
            default:
                return $this->attributes['name_ar'] ?: $this->attributes['name_en'];
        }
    }

    /**
     * Scope for active governorates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cities in this governorate
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Active cities in this governorate
     */
    public function activeCities()
    {
        return $this->hasMany(City::class)->where('is_active', true);
    }

    /**
     * Properties in this governorate
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get cities count for this governorate
     */
    public function getCitiesCountAttribute()
    {
        return $this->cities()->count();
    }

    /**
     * Get active cities count for this governorate
     */
    public function getActiveCitiesCountAttribute()
    {
        return $this->activeCities()->count();
    }

    /**
     * Get properties count for this governorate
     */
    public function getPropertiesCountAttribute()
    {
        return $this->properties()->count();
    }
}