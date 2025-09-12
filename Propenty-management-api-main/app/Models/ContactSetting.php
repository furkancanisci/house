<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'description',
        'is_required',
        'is_displayed',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_displayed' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scope to get settings ordered by sort_order
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Scope to get required settings
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    // Helper method to get a setting value by key
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    // Helper method to set a setting value by key
    public static function setValue($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    // Get all settings as key-value pairs
    public static function getAllAsArray()
    {
        return static::pluck('value', 'key')->toArray();
    }

    // Scope to get displayed settings
    public function scopeDisplayed($query)
    {
        return $query->where('is_displayed', true);
    }

    // Get public settings (for frontend)
    public static function getPublicSettings()
    {
        return static::whereIn('key', [
            'phone', 'email', 'address', 'business_hours',
            'whatsapp', 'website', 'facebook', 'twitter', 'linkedin'
        ])->displayed()->ordered()->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
    }
}
