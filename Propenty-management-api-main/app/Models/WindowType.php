<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WindowType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_en',
        'name_ar',
        'value',
        'description_en',
        'description_ar',
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
        'sort_order' => 'integer',
    ];

    /**
     * Scope a query to only include active window types.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name_en');
    }

    /**
     * Get the display name based on locale.
     */
    public function getDisplayName(string $locale = 'en'): string
    {
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Get the description based on locale.
     */
    public function getDescription(string $locale = 'en'): ?string
    {
        return $locale === 'ar' ? $this->description_ar : $this->description_en;
    }

    /**
     * Get properties that use this window type.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'window_type', 'value');
    }
}