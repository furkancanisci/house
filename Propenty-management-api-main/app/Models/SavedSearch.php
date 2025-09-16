<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'search_criteria',
        'notification_enabled',
    ];

    protected $casts = [
        'search_criteria' => 'array',
        'notification_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the saved search.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the search criteria as a formatted string for display.
     */
    public function getFormattedCriteriaAttribute(): string
    {
        $criteria = $this->search_criteria;
        $formatted = [];

        if (isset($criteria['property_type'])) {
            $formatted[] = "Type: {$criteria['property_type']}";
        }

        if (isset($criteria['min_price']) || isset($criteria['max_price'])) {
            $priceRange = 'Price: ';
            if (isset($criteria['min_price'])) {
                $priceRange .= "${$criteria['min_price']}";
            }
            $priceRange .= ' - ';
            if (isset($criteria['max_price'])) {
                $priceRange .= "${$criteria['max_price']}";
            }
            $formatted[] = $priceRange;
        }

        if (isset($criteria['location'])) {
            $formatted[] = "Location: {$criteria['location']}";
        }

        if (isset($criteria['bedrooms'])) {
            $formatted[] = "Bedrooms: {$criteria['bedrooms']}";
        }

        if (isset($criteria['bathrooms'])) {
            $formatted[] = "Bathrooms: {$criteria['bathrooms']}";
        }

        if (isset($criteria['floor_number'])) {
            $formatted[] = "Floor: {$criteria['floor_number']}";
        }

        if (isset($criteria['orientation'])) {
            $formatted[] = "Orientation: {$criteria['orientation']}";
        }

        if (isset($criteria['view_type'])) {
            $formatted[] = "View: {$criteria['view_type']}";
        }

        return implode(', ', $formatted) ?: 'No criteria specified';
    }

    /**
     * Check if the saved search matches given property attributes.
     */
    public function matchesProperty(array $propertyAttributes): bool
    {
        $criteria = $this->search_criteria;

        // Check property type
        if (isset($criteria['property_type']) && 
            $criteria['property_type'] !== $propertyAttributes['property_type']) {
            return false;
        }

        // Check price range
        if (isset($criteria['min_price']) && 
            $propertyAttributes['price'] < $criteria['min_price']) {
            return false;
        }

        if (isset($criteria['max_price']) && 
            $propertyAttributes['price'] > $criteria['max_price']) {
            return false;
        }

        // Check bedrooms
        if (isset($criteria['bedrooms']) && 
            $propertyAttributes['bedrooms'] < $criteria['bedrooms']) {
            return false;
        }

        // Check bathrooms
        if (isset($criteria['bathrooms']) && 
            $propertyAttributes['bathrooms'] < $criteria['bathrooms']) {
            return false;
        }

        // Check floor number
        if (isset($criteria['floor_number']) && 
            $propertyAttributes['floor_number'] !== $criteria['floor_number']) {
            return false;
        }

        // Check orientation
        if (isset($criteria['orientation']) && 
            $propertyAttributes['orientation'] !== $criteria['orientation']) {
            return false;
        }

        // Check view type
        if (isset($criteria['view_type']) && 
            $propertyAttributes['view_type'] !== $criteria['view_type']) {
            return false;
        }

        return true;
    }

    /**
     * Scope to get searches with notifications enabled.
     */
    public function scopeWithNotifications($query)
    {
        return $query->where('notification_enabled', true);
    }

    /**
     * Scope to get searches for a specific user.
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}