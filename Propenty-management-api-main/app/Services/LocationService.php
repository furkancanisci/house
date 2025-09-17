<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class LocationService
{
    /**
     * Build location query with Arabic/English support
     *
     * @param Builder $query
     * @param string $location
     * @return Builder
     */
    public static function buildLocationQuery(Builder $query, string $location): Builder
    {
        if (empty($location)) {
            return $query;
        }

        // Clean and prepare the location string
        $location = trim($location);
        
        // Check if location contains comma (city, state format)
        if (strpos($location, ',') !== false) {
            $parts = array_map('trim', explode(',', $location));
            
            if (count($parts) >= 2) {
                $city = $parts[0];
                $state = $parts[1];
                
                // Get city translations for better matching
                $cityTranslations = self::getCityTranslations($city);
                $stateTranslations = self::getStateTranslations($state);
                
                // Use AND conditions for city and state with translation support
                $query->where(function ($q) use ($cityTranslations, $stateTranslations) {
                    $q->where(function ($cityQuery) use ($cityTranslations) {
                        foreach ($cityTranslations as $cityName) {
                            $cityQuery->orWhere('city', 'ILIKE', "%{$cityName}%");
                        }
                    })->where(function ($stateQuery) use ($stateTranslations) {
                        foreach ($stateTranslations as $stateName) {
                            $stateQuery->orWhere('state', 'ILIKE', "%{$stateName}%");
                        }
                    });
                });
                
                return $query;
            }
        }
        
        // For single location term, search in multiple fields with OR conditions
        $query->where(function ($q) use ($location) {
            // Get translations for the location term
            $locationTranslations = array_merge(
                self::getCityTranslations($location),
                self::getStateTranslations($location)
            );
            
            // Search in city (both Arabic and English)
            foreach ($locationTranslations as $term) {
                $q->orWhere('city', 'ILIKE', "%{$term}%")
                  ->orWhere('state', 'ILIKE', "%{$term}%");
            }
            
            // Also search in other fields with original term
            $q->orWhere('neighborhood', 'ILIKE', "%{$location}%")
              ->orWhere('street_address', 'ILIKE', "%{$location}%");
        });

        return $query;
    }

    /**
     * Normalize location string for better matching
     *
     * @param string $location
     * @return string
     */
    public static function normalizeLocation(string $location): string
    {
        // Remove extra spaces and convert to lowercase for better matching
        return trim(strtolower($location));
    }

    /**
     * Get city translations (Arabic/English)
     *
     * @param string $city
     * @return array
     */
    private static function getCityTranslations(string $city): array
    {
        $cityMappings = [
            'عفرين' => ['عفرين', 'Afrin'],
            'Afrin' => ['عفرين', 'Afrin'],
            'حلب' => ['حلب', 'Aleppo'],
            'Aleppo' => ['حلب', 'Aleppo'],
            'دمشق' => ['دمشق', 'Damascus'],
            'Damascus' => ['دمشق', 'Damascus'],
            'حمص' => ['حمص', 'Homs'],
            'Homs' => ['حمص', 'Homs'],
            'حماة' => ['حماة', 'Hama'],
            'Hama' => ['حماة', 'Hama'],
            'اللاذقية' => ['اللاذقية', 'Latakia'],
            'Latakia' => ['اللاذقية', 'Latakia'],
            'طرطوس' => ['طرطوس', 'Tartus'],
            'Tartus' => ['طرطوس', 'Tartus'],
            'درعا' => ['درعا', 'Daraa'],
            'Daraa' => ['درعا', 'Daraa'],
        ];
        
        return $cityMappings[$city] ?? [$city];
    }
    
    /**
     * Get state translations (Arabic/English)
     *
     * @param string $state
     * @return array
     */
    private static function getStateTranslations(string $state): array
    {
        $stateMappings = [
            'حلب' => ['حلب', 'Aleppo'],
            'Aleppo' => ['حلب', 'Aleppo'],
            'دمشق' => ['دمشق', 'Damascus'],
            'Damascus' => ['دمشق', 'Damascus'],
            'حمص' => ['حمص', 'Homs'],
            'Homs' => ['حمص', 'Homs'],
            'حماة' => ['حماة', 'Hama'],
            'Hama' => ['حماة', 'Hama'],
            'اللاذقية' => ['اللاذقية', 'Latakia'],
            'Latakia' => ['اللاذقية', 'Latakia'],
            'طرطوس' => ['طرطوس', 'Tartus'],
            'Tartus' => ['طرطوس', 'Tartus'],
            'درعا' => ['درعا', 'Daraa'],
            'Daraa' => ['درعا', 'Daraa'],
        ];
        
        return $stateMappings[$state] ?? [$state];
    }

    /**
     * Get location suggestions based on existing data
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public static function getLocationSuggestions(string $query, int $limit = 10): array
    {
        if (empty($query) || strlen($query) < 2) {
            return [];
        }

        $suggestions = [];
        
        // Get unique cities
        $cities = \App\Models\Property::where('city', 'ILIKE', "%{$query}%")
            ->distinct()
            ->pluck('city')
            ->filter()
            ->take($limit)
            ->toArray();
        
        foreach ($cities as $city) {
            $suggestions[] = [
                'type' => 'city',
                'value' => $city,
                'label' => $city
            ];
        }

        // Get unique neighborhoods if we have space for more
        if (count($suggestions) < $limit) {
            $neighborhoods = \App\Models\Property::where('neighborhood', 'ILIKE', "%{$query}%")
                ->distinct()
                ->pluck('neighborhood')
                ->filter()
                ->take($limit - count($suggestions))
                ->toArray();
            
            foreach ($neighborhoods as $neighborhood) {
                $suggestions[] = [
                    'type' => 'neighborhood',
                    'value' => $neighborhood,
                    'label' => $neighborhood
                ];
            }
        }

        return $suggestions;
    }
}