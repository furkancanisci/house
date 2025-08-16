<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * Static data for Syrian cities
     */
    private function getSyrianCitiesData(): array
    {
        return [
            [
                'id' => 1,
                'name_ar' => 'دمشق',
                'name_en' => 'Damascus',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'دمشق',
                'state_en' => 'Damascus',
                'latitude' => 33.5138,
                'longitude' => 36.2765
            ],
            [
                'id' => 2,
                'name_ar' => 'حلب',
                'name_en' => 'Aleppo',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'حلب',
                'state_en' => 'Aleppo',
                'latitude' => 36.2021,
                'longitude' => 37.1343
            ],
            [
                'id' => 3,
                'name_ar' => 'عفرين',
                'name_en' => 'Afrin',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'حلب',
                'state_en' => 'Aleppo',
                'latitude' => 36.5117,
                'longitude' => 36.8784
            ],
            [
                'id' => 4,
                'name_ar' => 'حمص',
                'name_en' => 'Homs',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'حمص',
                'state_en' => 'Homs',
                'latitude' => 34.7394,
                'longitude' => 36.7163
            ],
            [
                'id' => 5,
                'name_ar' => 'حماة',
                'name_en' => 'Hama',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'حماة',
                'state_en' => 'Hama',
                'latitude' => 35.1320,
                'longitude' => 36.7500
            ],
            [
                'id' => 6,
                'name_ar' => 'اللاذقية',
                'name_en' => 'Latakia',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'اللاذقية',
                'state_en' => 'Latakia',
                'latitude' => 35.5138,
                'longitude' => 35.7833
            ],
            [
                'id' => 7,
                'name_ar' => 'طرطوس',
                'name_en' => 'Tartus',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'طرطوس',
                'state_en' => 'Tartus',
                'latitude' => 34.8833,
                'longitude' => 35.8833
            ],
            [
                'id' => 8,
                'name_ar' => 'درعا',
                'name_en' => 'Daraa',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'درعا',
                'state_en' => 'Daraa',
                'latitude' => 32.6189,
                'longitude' => 36.1021
            ],
            [
                'id' => 9,
                'name_ar' => 'السويداء',
                'name_en' => 'As-Suwayda',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'السويداء',
                'state_en' => 'As-Suwayda',
                'latitude' => 32.7094,
                'longitude' => 36.5694
            ],
            [
                'id' => 10,
                'name_ar' => 'القنيطرة',
                'name_en' => 'Quneitra',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'القنيطرة',
                'state_en' => 'Quneitra',
                'latitude' => 33.1264,
                'longitude' => 35.8244
            ],
            [
                'id' => 11,
                'name_ar' => 'دير الزور',
                'name_en' => 'Deir ez-Zor',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'دير الزور',
                'state_en' => 'Deir ez-Zor',
                'latitude' => 35.3394,
                'longitude' => 40.1467
            ],
            [
                'id' => 12,
                'name_ar' => 'الحسكة',
                'name_en' => 'Al-Hasakah',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'الحسكة',
                'state_en' => 'Al-Hasakah',
                'latitude' => 36.5000,
                'longitude' => 40.7500
            ],
            [
                'id' => 13,
                'name_ar' => 'الرقة',
                'name_en' => 'Raqqa',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'الرقة',
                'state_en' => 'Raqqa',
                'latitude' => 35.9500,
                'longitude' => 39.0167
            ],
            [
                'id' => 14,
                'name_ar' => 'إدلب',
                'name_en' => 'Idlib',
                'country_ar' => 'سوريا',
                'country_en' => 'Syria',
                'state_ar' => 'إدلب',
                'state_en' => 'Idlib',
                'latitude' => 35.9333,
                'longitude' => 36.6333
            ]
        ];
    }

    /**
     * Get localized name based on current locale
     */
    private function getLocalizedName($item, $type = 'name'): string
    {
        $locale = app()->getLocale();
        $value = $locale === 'ar' ? $item[$type . '_ar'] : $item[$type . '_en'];
        return $this->ensureUtf8($value);
    }

    /**
     * Get all cities with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $cities = $this->getSyrianCitiesData();
        
        // Filter by country
        if ($request->has('country')) {
            $country = $request->country;
            $cities = array_filter($cities, function ($city) use ($country) {
                return $city['country_ar'] === $country || $city['country_en'] === $country;
            });
        }
        
        // Filter by state
        if ($request->has('state')) {
            $state = $request->state;
            $cities = array_filter($cities, function ($city) use ($state) {
                return $city['state_ar'] === $state || $city['state_en'] === $state;
            });
        }
        
        return response()->json([
            'success' => true,
            'data' => array_map(function ($city) {
                return [
                    'id' => $city['id'],
                    'name' => $this->getLocalizedName($city),
                    'name_ar' => $this->ensureUtf8($city['name_ar']),
                    'name_en' => $this->ensureUtf8($city['name_en']),
                    'country' => $this->getLocalizedName($city, 'country'),
                    'state' => $this->getLocalizedName($city, 'state'),
                    'latitude' => $city['latitude'],
                    'longitude' => $city['longitude'],
                ];
            }, array_values($cities))
        ]);
    }
    
    /**
     * Get all countries
     */
    public function getCountries(): JsonResponse
    {
        $countries = [
            [
                'name' => $this->getLocalizedName(['country_ar' => 'سوريا', 'country_en' => 'Syria'], 'country'),
                'name_ar' => $this->ensureUtf8('سوريا'),
                'name_en' => $this->ensureUtf8('Syria'),
            ]
        ];
            
        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }
    
    /**
     * Get states for a specific country
     */
    public function getStates(Request $request): JsonResponse
    {
        $cities = $this->getSyrianCitiesData();
        
        // Get unique states
        $states = [];
        $seenStates = [];
        
        foreach ($cities as $city) {
            $stateKey = $city['state_ar'] . '|' . $city['state_en'];
            if (!in_array($stateKey, $seenStates)) {
                $states[] = [
                    'name' => $this->getLocalizedName($city, 'state'),
                    'name_ar' => $this->ensureUtf8($city['state_ar']),
                    'name_en' => $this->ensureUtf8($city['state_en']),
                ];
                $seenStates[] = $stateKey;
            }
        }
            
        return response()->json([
            'success' => true,
            'data' => $states
        ]);
    }
    
    /**
     * Get cities by state
     */
    public function getCitiesByState(Request $request): JsonResponse
    {
        $cities = $this->getSyrianCitiesData();
        
        if ($request->has('state')) {
            $state = $request->state;
            $cities = array_filter($cities, function ($city) use ($state) {
                return $city['state_ar'] === $state || $city['state_en'] === $state;
            });
        }
        
        return response()->json([
            'success' => true,
            'data' => array_map(function ($city) {
                return [
                    'id' => $city['id'],
                    'name' => $this->getLocalizedName($city),
                    'name_ar' => $this->ensureUtf8($city['name_ar']),
                    'name_en' => $this->ensureUtf8($city['name_en']),
                ];
            }, array_values($cities))
        ]);
    }
    
    /**
     * Search cities by name
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->get('q', '');
        
        if (empty($searchTerm)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        $cities = $this->getSyrianCitiesData();
        $filteredCities = array_filter($cities, function ($city) use ($searchTerm) {
            return stripos($city['name_ar'], $searchTerm) !== false || 
                   stripos($city['name_en'], $searchTerm) !== false;
        });
        
        // Limit to 10 results
        $filteredCities = array_slice($filteredCities, 0, 10);
            
        return response()->json([
            'success' => true,
            'data' => array_map(function ($city) {
                return [
                    'id' => $city['id'],
                    'name' => $this->getLocalizedName($city),
                    'name_ar' => $this->ensureUtf8($city['name_ar']),
                    'name_en' => $this->ensureUtf8($city['name_en']),
                ];
            }, array_values($filteredCities))
        ]);
    }

    /**
     * Ensure proper UTF-8 encoding for text fields
     */
    private function ensureUtf8($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        if (!is_string($value)) {
            return $value;
        }
        
        // Check if the string is already valid UTF-8
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }
        
        // Try to convert from common encodings to UTF-8
        $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'];
        
        foreach ($encodings as $encoding) {
            $converted = mb_convert_encoding($value, 'UTF-8', $encoding);
            if (mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }
        
        // If all else fails, remove invalid characters
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}