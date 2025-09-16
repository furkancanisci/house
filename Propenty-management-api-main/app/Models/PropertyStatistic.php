<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'views_count',
        'inquiries_count',
        'favorites_count',
        'last_viewed_at',
    ];

    protected $casts = [
        'last_viewed_at' => 'datetime',
        'views_count' => 'integer',
        'inquiries_count' => 'integer',
        'favorites_count' => 'integer',
    ];

    /**
     * Get the property that owns the statistics.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Increment the views count for a property.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
        $this->update(['last_viewed_at' => now()]);
    }

    /**
     * Increment the inquiries count for a property.
     */
    public function incrementInquiries(): void
    {
        $this->increment('inquiries_count');
    }

    /**
     * Increment the favorites count for a property.
     */
    public function incrementFavorites(): void
    {
        $this->increment('favorites_count');
    }

    /**
     * Decrement the favorites count for a property.
     */
    public function decrementFavorites(): void
    {
        $this->decrement('favorites_count');
    }

    /**
     * Get or create statistics for a property.
     */
    public static function getOrCreateForProperty(string $propertyId): self
    {
        return self::firstOrCreate(
            ['property_id' => $propertyId],
            [
                'views_count' => 0,
                'inquiries_count' => 0,
                'favorites_count' => 0,
            ]
        );
    }

    /**
     * Get the total engagement score (views + inquiries + favorites).
     */
    public function getEngagementScoreAttribute(): int
    {
        return $this->views_count + ($this->inquiries_count * 5) + ($this->favorites_count * 3);
    }

    /**
     * Scope to get most viewed properties.
     */
    public function scopeMostViewed($query, int $limit = 10)
    {
        return $query->orderBy('views_count', 'desc')->limit($limit);
    }

    /**
     * Scope to get most inquired properties.
     */
    public function scopeMostInquired($query, int $limit = 10)
    {
        return $query->orderBy('inquiries_count', 'desc')->limit($limit);
    }

    /**
     * Scope to get most favorited properties.
     */
    public function scopeMostFavorited($query, int $limit = 10)
    {
        return $query->orderBy('favorites_count', 'desc')->limit($limit);
    }
}