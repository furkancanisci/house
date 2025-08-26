<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyDocumentType extends Model
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
     * Scope to get only active document types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Get the localized name based on the given language.
     *
     * @param string $language
     * @return string
     */
    public function getLocalizedName($language = 'ar')
    {
        switch ($language) {
            case 'en':
                return $this->name_en ?? $this->name_ar;
            case 'ku':
                return $this->name_ku ?? $this->name_ar;
            default:
                return $this->name_ar;
        }
    }

    /**
     * Get the localized description based on the given language.
     *
     * @param string $language
     * @return string|null
     */
    public function getLocalizedDescription($language = 'ar')
    {
        switch ($language) {
            case 'en':
                return $this->description_en ?? $this->description_ar;
            case 'ku':
                return $this->description_ku ?? $this->description_ar;
            default:
                return $this->description_ar;
        }
    }

    /**
     * Get properties that have this document type.
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'document_type_id');
    }
}