<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'icon',
        'number',
        'label_ar',
        'label_en',
        'label_ku',
        'color',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getLocalizedLabelAttribute()
    {
        $locale = app()->getLocale();
        return $this->{"label_{$locale}"} ?? $this->label_en ?? $this->label_ar;
    }
}