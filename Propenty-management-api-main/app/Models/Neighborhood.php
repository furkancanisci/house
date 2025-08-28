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
        'name',
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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($neighborhood) {
            if (empty($neighborhood->slug)) {
                $neighborhood->slug = Str::slug($neighborhood->name . '-' . $neighborhood->city->name);
            }
        });

        static::updating(function ($neighborhood) {
            if ($neighborhood->isDirty('name') && !$neighborhood->isDirty('slug')) {
                $neighborhood->slug = Str::slug($neighborhood->name . '-' . $neighborhood->city->name);
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
        return $this->name . ', ' . $this->city->name;
    }
}