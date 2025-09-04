<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Neighborhood extends Model
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
        'city_id',
        'description',
        'latitude',
        'longitude',
        'properties_count',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Get the neighborhood name based on locale
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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($neighborhood) {
            if (empty($neighborhood->slug)) {
                $name = $neighborhood->name_ar ?: $neighborhood->name_en;
                $cityName = $neighborhood->city ? $neighborhood->city->name_ar : '';
                $neighborhood->slug = Str::slug($name . '-' . $cityName);
            }
        });

        static::updating(function ($neighborhood) {
            if (($neighborhood->isDirty('name_ar') || $neighborhood->isDirty('name_en')) && !$neighborhood->isDirty('slug')) {
                $name = $neighborhood->name_ar ?: $neighborhood->name_en;
                $cityName = $neighborhood->city ? $neighborhood->city->name_ar : '';
                $neighborhood->slug = Str::slug($name . '-' . $cityName);
            }
        });
    }

    /**
     * Get the city that owns the neighborhood.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the properties in this neighborhood.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'neighborhood', 'name');
    }

    /**
     * Scope for active neighborhoods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for neighborhoods with properties.
     */
    public function scopeWithProperties($query)
    {
        return $query->where('properties_count', '>', 0);
    }

    /**
     * Update properties count.
     */
    public function updatePropertiesCount(): void
    {
        $this->update([
            'properties_count' => $this->properties()->count()
        ]);
    }

    /**
     * Get full location name.
     */
    public function getFullLocationAttribute(): string
    {
        $locale = app()->getLocale();
        $neighborhoodName = $this->getName($locale);
        $cityName = $this->city ? $this->city->getName($locale) : '';
        return $neighborhoodName . ', ' . $cityName;
    }
}