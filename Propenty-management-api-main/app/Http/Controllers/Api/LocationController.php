<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Get all unique states with property counts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStates()
    {
        $states = Property::select('state', DB::raw('count(*) as total'))
            ->groupBy('state')
            ->orderBy('state')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->state,
                    'total' => $item->total
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $states,
            'message' => 'States retrieved successfully.'
        ]);
    }

    /**
     * Get all unique cities with property counts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCities(Request $request)
    {
        $query = Property::select('city', 'state', DB::raw('count(*) as total'));
        
        if ($request->has('state')) {
            $query->where('state', $request->state);
        }
        
        $cities = $query->groupBy('city', 'state')
            ->orderBy('city')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->city,
                    'state' => $item->state,
                    'total' => $item->total
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $cities,
            'message' => 'Cities retrieved successfully.'
        ]);
    }

    /**
     * Get all unique neighborhoods with property counts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNeighborhoods(Request $request)
    {
        $query = Property::select(
            'neighborhood', 
            'city', 
            'state', 
            DB::raw('count(*) as total')
        );
        
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }
        
        if ($request->has('state')) {
            $query->where('state', $request->state);
        }
        
        $neighborhoods = $query->groupBy('neighborhood', 'city', 'state')
            ->orderBy('neighborhood')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->neighborhood,
                    'city' => $item->city,
                    'state' => $item->state,
                    'total' => $item->total
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $neighborhoods,
            'message' => 'Neighborhoods retrieved successfully.'
        ]);
    }
}
