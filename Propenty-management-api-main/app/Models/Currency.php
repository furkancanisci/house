<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'name_ku',
        'symbol',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get localized name based on current language
     */
    public function getLocalizedName(string $lang = 'ar'): string
    {
        return match ($lang) {
            'ar' => $this->name_ar,
            'en' => $this->name_en,
            'ku' => $this->name_ku ?? $this->name_ar,
            default => $this->name_ar,
        };
    }

    /**
     * Scope to get only active currencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
