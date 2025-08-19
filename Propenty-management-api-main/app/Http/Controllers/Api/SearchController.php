<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Get search suggestions based on query
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q');
        \Illuminate\Support\Facades\Log::info('Search suggestions requested', ['query' => $query]);
        
        if (!$query || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Query is too short. Please enter at least 2 characters.'
            ]);
        }

        // Search for matching cities (case-insensitive)
        $citiesQuery = Property::select('city', 'state', DB::raw('count(*) as total'))
            ->whereRaw('LOWER(city) LIKE ?', ['%' . strtolower($query) . '%'])
            ->where('status', 'active')
            ->groupBy('city', 'state')
            ->orderBy('total', 'desc')
            ->limit(5);
            
        \Illuminate\Support\Facades\Log::info('Cities SQL Query', ['sql' => $citiesQuery->toSql(), 'bindings' => $citiesQuery->getBindings()]);
            
        $cities = $citiesQuery->get()
            ->map(function ($item) {
                $cityState = $this->ensureUtf8($item->city) . ', ' . $this->ensureUtf8($item->state);
                return [
                    'type' => 'city',
                    'value' => $cityState,
                    'label' => $cityState,
                    'count' => $item->total
                ];
            });
            
        \Illuminate\Support\Facades\Log::info('Cities results', ['count' => $cities->count(), 'results' => $cities->toArray()]);

        // Search for matching properties (case-insensitive)
        $propertiesQuery = Property::select('title', 'slug', 'city', 'state')
            ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($query) . '%'])
            ->where('status', 'active')
            ->limit(5);
            
        \Illuminate\Support\Facades\Log::info('Properties SQL Query', ['sql' => $propertiesQuery->toSql(), 'bindings' => $propertiesQuery->getBindings()]);
            
        $properties = $propertiesQuery->get()
            ->map(function ($item) {
                return [
                    'type' => 'property',
                    'value' => $this->ensureUtf8($item->title),
                    'label' => $this->ensureUtf8($item->title),
                    'location' => $this->ensureUtf8($item->city) . ', ' . $this->ensureUtf8($item->state),
                    'slug' => $item->slug
                ];
            });
            
        \Illuminate\Support\Facades\Log::info('Properties results', ['count' => $properties->count(), 'results' => $properties->toArray()]);

        // Combine and limit results
        $suggestions = $cities->concat($properties)->take(8)->values();

        $response = [
            'success' => true,
            'data' => $suggestions->toArray(),
            'message' => 'Search suggestions retrieved successfully.'
        ];
        
        \Illuminate\Support\Facades\Log::info('Final response', ['response' => $response]);
        
        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
