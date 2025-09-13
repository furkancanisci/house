<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PropertyType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'name_ar',
        'name_ku',
        'slug',
        'description',
        'icon',
        'parent_id',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($propertyType) {
            if (empty($propertyType->slug)) {
                $propertyType->slug = Str::slug($propertyType->name);
            }
        });

        static::updating(function ($propertyType) {
            if ($propertyType->isDirty('name') && !$propertyType->isDirty('slug')) {
                $propertyType->slug = Str::slug($propertyType->name);
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(PropertyType::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(PropertyType::class, 'parent_id');
    }

    /**
     * Get properties of this type.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'property_type', 'name');
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for parent categories (no parent).
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for child categories (has parent).
     */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Get formatted icon (FontAwesome or custom).
     */
    public function getIconHtmlAttribute(): string
    {
        if (!$this->icon) {
            return '<i class="fas fa-home"></i>';
        }

        // Check if it's a FontAwesome icon
        if (str_starts_with($this->icon, 'fa-') || str_starts_with($this->icon, 'fas ') || str_starts_with($this->icon, 'far ')) {
            return '<i class="' . $this->icon . '"></i>';
        }

        // Otherwise return as-is (could be SVG or other HTML)
        return $this->icon;
    }

    /**
     * Get preferred name (Arabic first, then English fallback).
     */
    public function getPreferredName(): string
    {
        return $this->name_ar ?: $this->name;
    }

    /**
     * Get full category path with preferred names.
     */
    public function getFullPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->getPreferredName() . ' > ' . $this->getPreferredName();
        }

        return $this->getPreferredName();
    }
}
