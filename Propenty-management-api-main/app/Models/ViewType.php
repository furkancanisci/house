<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViewType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar',
        'value',
        'description_en',
        'description_ar',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Scope for active view types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered view types
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name_en');
    }

    /**
     * Get the name attribute based on current locale
     */
    public function getNameAttribute()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Get the description attribute based on current locale
     */
    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? $this->description_ar : $this->description_en;
    }

    /**
     * Properties that use this view type
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'view_type', 'value');
    }

    /**
     * Get view types as options for dropdowns
     */
    public static function getOptions()
    {
        return static::active()->ordered()->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'value' => $type->value,
                'label' => $type->name,
                'name_en' => $type->name_en,
                'name_ar' => $type->name_ar,
            ];
        });
    }

    /**
     * Get localized name attribute
     */
    public function getLocalizedNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get localized description attribute
     */
    public function getLocalizedDescriptionAttribute()
    {
        return $this->description;
    }
}
