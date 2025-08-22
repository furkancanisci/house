<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Amenity extends Model
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
        'icon',
        'category',
        'description',
        'sort_order',
        'is_active',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($amenity) {
            if (empty($amenity->slug)) {
                $amenity->slug = Str::slug($amenity->name);
            }
        });

        static::updating(function ($amenity) {
            if ($amenity->isDirty('name') && !$amenity->isDirty('slug')) {
                $amenity->slug = Str::slug($amenity->name);
            }
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Available amenity categories.
     */
    const CATEGORIES = [
        'indoor' => 'Indoor Features',
        'outdoor' => 'Outdoor Features',
        'building' => 'Building Amenities',
        'community' => 'Community Features',
        'utilities' => 'Utilities',
        'security' => 'Security',
        'parking' => 'Parking',
        'accessibility' => 'Accessibility',
    ];



    /**
     * Get the properties that have this amenity.
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_amenities')
            ->withTimestamps();
    }

    /**
     * Scope for active amenities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for amenities by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get amenities grouped by category.
     */
    public static function getGroupedByCategory()
    {
        return static::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
    }

    /**
     * Get formatted icon (FontAwesome or custom).
     */
    public function getIconHtmlAttribute(): string
    {
        if (!$this->icon) {
            return '<i class="fas fa-check-circle"></i>';
        }

        // Check if it's a FontAwesome icon
        if (str_starts_with($this->icon, 'fa-') || str_starts_with($this->icon, 'fas ') || str_starts_with($this->icon, 'far ')) {
            return '<i class="' . $this->icon . '"></i>';
        }

        // Otherwise return as-is (could be SVG or other HTML)
        return $this->icon;
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}