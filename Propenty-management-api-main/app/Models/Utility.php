<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Utility extends Model
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
        'description_ar',
        'description_en',
        'description_ku',
        'slug',
        'icon',
        'category',
        'sort_order',
        'is_active',
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

        static::creating(function ($utility) {
            if (empty($utility->slug)) {
                $utility->slug = Str::slug($utility->name_en ?: $utility->name_ar);
            }
        });

        static::updating(function ($utility) {
            if (($utility->isDirty('name_en') || $utility->isDirty('name_ar')) && !$utility->isDirty('slug')) {
                $utility->slug = Str::slug($utility->name_en ?: $utility->name_ar);
            }
        });
    }

    /**
     * Available utility categories.
     */
    const CATEGORIES = [
        'basic' => 'Basic Utilities',
        'internet' => 'Internet & Communication',
        'entertainment' => 'Entertainment',
        'heating_cooling' => 'Heating & Cooling',
        'water' => 'Water Services',
        'electricity' => 'Electricity',
        'gas' => 'Gas Services',
        'waste' => 'Waste Management',
        'maintenance' => 'Maintenance Services',
    ];

    /**
     * Get the properties that have this utility.
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_utilities')
            ->withTimestamps();
    }

    /**
     * Scope for active utilities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for utilities by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get utilities grouped by category.
     */
    public static function getGroupedByCategory($language = 'ar')
    {
        return static::active()
            ->orderBy('sort_order')
            ->orderBy('name_' . $language)
            ->get()
            ->groupBy('category');
    }

    /**
     * Get localized name based on language.
     */
    public function getName($language = 'ar')
    {
        $nameField = 'name_' . $language;
        return $this->$nameField ?: $this->name_ar;
    }

    /**
     * Get localized description based on language.
     */
    public function getDescription($language = 'ar')
    {
        $descField = 'description_' . $language;
        return $this->$descField ?: $this->description_ar;
    }

    /**
     * Get formatted icon (FontAwesome or custom).
     */
    public function getIconHtmlAttribute(): string
    {
        if (!$this->icon) {
            return '<i class="fas fa-plug"></i>';
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

    /**
     * Convert to array with localized fields.
     */
    public function toLocalizedArray($language = 'ar')
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'name_ku' => $this->name_ku,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'description_ku' => $this->description_ku,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}