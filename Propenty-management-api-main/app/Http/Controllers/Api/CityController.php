<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\City;

class CityController extends Controller
{
    /**
     * NOTE: Previously this controller used a static dataset. Replace with DB-backed queries
     * to return real cities and states from the `cities` table.
     */

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
        // Query DB for cities (active only)
        $query = City::query()->select(['id','name_ar','name_en','state_ar','state_en','country_ar','country_en','latitude','longitude'])->where('is_active', true);

        if ($request->has('country')) {
            $country = $request->get('country');
            // Try matching either Arabic or English country
            $query->where(function($q) use ($country) {
                $q->where('country_ar', $country)->orWhere('country_en', $country);
            });
        }

        if ($request->has('state')) {
            $state = $request->get('state');
            $query->where(function($q) use ($state) {
                $q->where('state_ar', $state)->orWhere('state_en', $state);
            });
        }

        $cities = $query->orderBy('name_en')->get();

        return response()->json([
            'success' => true,
            'data' => $cities->map(function($city) {
                return [
                    'id' => $city->id,
                    'name' => $this->getLocalizedName($city->toArray()),
                    'name_ar' => $this->ensureUtf8($city->name_ar),
                    'name_en' => $this->ensureUtf8($city->name_en),
                    'country' => $this->getLocalizedName($city->toArray(), 'country'),
                    'state' => $this->getLocalizedName($city->toArray(), 'state'),
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude,
                ];
            })->toArray()
        ]);
    }
    
    /**
     * Get cities by state using URL parameter
     */
    public function getCitiesByStateParam(string $state): JsonResponse
    {
        $decodedState = urldecode($state);

        $cities = City::where('is_active', true)
            ->where(function($q) use ($decodedState) {
                $q->where('state_ar', $decodedState)->orWhere('state_en', $decodedState);
            })->orderBy('name_en')->get();

        return response()->json([
            'success' => true,
            'data' => $cities->map(function($city) {
                return [
                    'id' => $city->id,
                    'name' => $this->getLocalizedName($city->toArray()),
                    'name_ar' => $this->ensureUtf8($city->name_ar),
                    'name_en' => $this->ensureUtf8($city->name_en),
                    'state' => $this->getLocalizedName($city->toArray(), 'state'),
                    'country' => $this->getLocalizedName($city->toArray(), 'country'),
                ];
            })->toArray()
        ]);
    }
    
    /**
     * Get states for Syria
     */
    public function getStates(Request $request): JsonResponse
    {
        $locale = $request->get('lang', app()->getLocale());

        $states = City::select(['state_ar','state_en'])
            ->where('is_active', true)
            ->groupBy('state_ar','state_en')
            ->orderBy('state_en')
            ->get()
            ->map(function($row) use ($locale) {
                return [
                    'name' => $locale === 'ar' ? $row->state_ar : $row->state_en,
                    'name_ar' => $this->ensureUtf8($row->state_ar),
                    'name_en' => $this->ensureUtf8($row->state_en),
                ];
            })->toArray();

        // Fallback to a known list of Syrian states when DB is empty or not seeded
        if (empty($states)) {
            $fallback = [
                ['name' => $locale === 'ar' ? 'دمشق' : 'Damascus', 'name_ar' => 'دمشق', 'name_en' => 'Damascus'],
                ['name' => $locale === 'ar' ? 'حلب' : 'Aleppo', 'name_ar' => 'حلب', 'name_en' => 'Aleppo'],
                ['name' => $locale === 'ar' ? 'حمص' : 'Homs', 'name_ar' => 'حمص', 'name_en' => 'Homs'],
                ['name' => $locale === 'ar' ? 'حماة' : 'Hama', 'name_ar' => 'حماة', 'name_en' => 'Hama'],
                ['name' => $locale === 'ar' ? 'اللاذقية' : 'Latakia', 'name_ar' => 'اللاذقية', 'name_en' => 'Latakia'],
                ['name' => $locale === 'ar' ? 'طرطوس' : 'Tartus', 'name_ar' => 'طرطوس', 'name_en' => 'Tartus'],
                ['name' => $locale === 'ar' ? 'درعا' : 'Daraa', 'name_ar' => 'درعا', 'name_en' => 'Daraa'],
                ['name' => $locale === 'ar' ? 'دير الزور' : 'Deir ez-Zor', 'name_ar' => 'دير الزور', 'name_en' => 'Deir ez-Zor'],
                ['name' => $locale === 'ar' ? 'الرقة' : 'Raqqa', 'name_ar' => 'الرقة', 'name_en' => 'Raqqa'],
                ['name' => $locale === 'ar' ? 'إدلب' : 'Idlib', 'name_ar' => 'إدلب', 'name_en' => 'Idlib'],
                ['name' => $locale === 'ar' ? 'الحسكة' : 'Al-Hasakah', 'name_ar' => 'الحسكة', 'name_en' => 'Al-Hasakah'],
                ['name' => $locale === 'ar' ? 'السويداء' : 'As-Suwayda', 'name_ar' => 'السويداء', 'name_en' => 'As-Suwayda'],
                ['name' => $locale === 'ar' ? 'القنيطرة' : 'Quneitra', 'name_ar' => 'القنيطرة', 'name_en' => 'Quneitra']
            ];
            $states = $fallback;
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
        $state = $request->get('state');
        if (!$state) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $cities = City::where('is_active', true)
            ->where(function($q) use ($state) {
                $q->where('state_ar', $state)->orWhere('state_en', $state);
            })->orderBy('name_en')->get();

        return response()->json([
            'success' => true,
            'data' => $cities->map(function($city) {
                return [
                    'id' => $city->id,
                    'name' => $this->getLocalizedName($city->toArray()),
                    'name_ar' => $this->ensureUtf8($city->name_ar),
                    'name_en' => $this->ensureUtf8($city->name_en),
                ];
            })->toArray()
        ]);
    }
    
    /**
     * Search cities by name
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->get('q', '');
        if (empty($searchTerm)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $cities = City::where('is_active', true)
            ->where(function($q) use ($searchTerm) {
                $q->where('name_ar', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('name_en', 'ILIKE', "%{$searchTerm}%");
            })
            ->orderBy('name_en')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities->map(function($city) {
                return [
                    'id' => $city->id,
                    'name' => $this->getLocalizedName($city->toArray()),
                    'name_ar' => $this->ensureUtf8($city->name_ar),
                    'name_en' => $this->ensureUtf8($city->name_en),
                ];
            })->toArray()
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