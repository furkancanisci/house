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
        \Log::info('Search suggestions requested', ['query' => $query]);
        
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
            
        \Log::info('Cities SQL Query', ['sql' => $citiesQuery->toSql(), 'bindings' => $citiesQuery->getBindings()]);
            
        $cities = $citiesQuery->get()
            ->map(function ($item) {
                return [
                    'type' => 'city',
                    'value' => $item->city . ', ' . $item->state,
                    'label' => $item->city . ', ' . $item->state,
                    'count' => $item->total
                ];
            });
            
        \Log::info('Cities results', ['count' => $cities->count(), 'results' => $cities->toArray()]);

        // Search for matching properties (case-insensitive)
        $propertiesQuery = Property::select('title', 'slug', 'city', 'state')
            ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($query) . '%'])
            ->where('status', 'active')
            ->limit(5);
            
        \Log::info('Properties SQL Query', ['sql' => $propertiesQuery->toSql(), 'bindings' => $propertiesQuery->getBindings()]);
            
        $properties = $propertiesQuery->get()
            ->map(function ($item) {
                return [
                    'type' => 'property',
                    'value' => $item->title,
                    'label' => $item->title,
                    'location' => $item->city . ', ' . $item->state,
                    'slug' => $item->slug
                ];
            });
            
        \Log::info('Properties results', ['count' => $properties->count(), 'results' => $properties->toArray()]);

        // Combine and limit results
        $suggestions = $cities->concat($properties)->take(8)->values();

        $response = [
            'success' => true,
            'data' => $suggestions->toArray(),
            'message' => 'Search suggestions retrieved successfully.'
        ];
        
        \Log::info('Final response', ['response' => $response]);
        
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
}
