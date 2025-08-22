<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Cache key prefix.
     */
    const CACHE_PREFIX = 'settings:';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are updated
        static::saved(function ($setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
            Cache::forget(self::CACHE_PREFIX . 'all');
            Cache::forget(self::CACHE_PREFIX . 'group:' . $setting->group);
        });

        static::deleted(function ($setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
            Cache::forget(self::CACHE_PREFIX . 'all');
            Cache::forget(self::CACHE_PREFIX . 'group:' . $setting->group);
        });
    }

    /**
     * Get a setting value by key.
     */
    public static function get($key, $default = null)
    {
        return Cache::remember(self::CACHE_PREFIX . $key, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value.
     */
    public static function set($key, $value, $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::prepareValue($value),
                'group' => $group,
                'type' => self::detectType($value),
            ]
        );

        return $setting;
    }

    /**
     * Get all settings by group.
     */
    public static function getByGroup($group)
    {
        return Cache::remember(self::CACHE_PREFIX . 'group:' . $group, 3600, function () use ($group) {
            return self::where('group', $group)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => self::castValue($setting->value, $setting->type)];
                })
                ->toArray();
        });
    }

    /**
     * Get all public settings.
     */
    public static function getPublic()
    {
        return Cache::remember(self::CACHE_PREFIX . 'public', 3600, function () {
            return self::where('is_public', true)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => self::castValue($setting->value, $setting->type)];
                })
                ->toArray();
        });
    }

    /**
     * Cast value to appropriate type.
     */
    protected static function castValue($value, $type)
    {
        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Prepare value for storage.
     */
    protected static function prepareValue($value)
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Detect value type.
     */
    protected static function detectType($value)
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_array($value) || is_object($value)) {
            return 'json';
        }

        return 'string';
    }

    /**
     * Get all settings.
     */
    public static function getAll()
    {
        return Cache::remember(self::CACHE_PREFIX . 'all', 3600, function () {
            return self::all()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => self::castValue($setting->value, $setting->type)];
                })
                ->toArray();
        });
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache()
    {
        Cache::flush();
    }
}