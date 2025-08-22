<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Get overview statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function overview()
    {
        try {
            $stats = [
                'total_properties' => Property::where('status', 'active')->count(),
                'total_for_sale' => Property::where('status', 'active')
                    ->forListing('sale')
                    ->count(),
                'total_for_rent' => Property::where('status', 'active')
                    ->forListing('rent')
                    ->count(),
                'total_agents' => Property::where('status', 'active')
                    ->distinct('user_id')
                    ->count('user_id'),
                'total_views' => Property::sum('views') ?? 0,
                'avg_price' => [
                    'sale' => (float)Property::where('status', 'active')
                        ->forListing('sale')
                        ->avg('price'),
                    'rent' => (float)Property::where('status', 'active')
                        ->forListing('rent')
                        ->avg('price')
                ],
                'property_types' => Property::select('property_types.name as type', DB::raw('count(properties.id) as count'))
                    ->join('property_types', 'properties.property_type_id', '=', 'property_types.id')
                    ->where('properties.status', 'active')
                    ->groupBy('property_types.id', 'property_types.name')
                    ->orderBy('count', 'desc')
                    ->get(),
                'recently_added' => Property::where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(['id', 'title', 'price', 'listing_type', 'property_type', 'created_at']),
                'featured_properties' => Property::where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(['id', 'title', 'price', 'listing_type', 'property_type', 'is_featured'])
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get price ranges and statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function priceRanges(Request $request)
    {
        try {
            $propertyType = $request->query('property_type');
            $listingType = $request->query('listing_type');
            
            $query = Property::where('status', 'active');
            
            if ($propertyType) {
                // Find property type by name or slug
                $type = \App\Models\PropertyType::where('name', $propertyType)
                    ->orWhere('slug', $propertyType)
                    ->first();
                if ($type) {
                    $query->where('property_type_id', $type->id);
                }
            }
            
            if ($listingType) {
                $query->forListing($listingType);
            }
            
            $prices = $query->pluck('price');
            
            $ranges = [
                '0-100000' => 0,
                '100001-250000' => 0,
                '250001-500000' => 0,
                '500001-1000000' => 0,
                '1000001-5000000' => 0,
                '5000001-10000000' => 0,
                '10000001+' => 0
            ];
            
            foreach ($prices as $price) {
                $price = (float)$price;
                if ($price <= 100000) {
                    $ranges['0-100000']++;
                } elseif ($price <= 250000) {
                    $ranges['100001-250000']++;
                } elseif ($price <= 500000) {
                    $ranges['250001-500000']++;
                } elseif ($price <= 1000000) {
                    $ranges['500001-1000000']++;
                } elseif ($price <= 5000000) {
                    $ranges['1000001-5000000']++;
                } elseif ($price <= 10000000) {
                    $ranges['5000001-10000000']++;
                } else {
                    $ranges['10000001+']++;
                }
            }
            
            $stats = [
                'min' => (float)$prices->min(),
                'max' => (float)$prices->max(),
                'average' => (float)$prices->avg(),
                'median' => (float)($prices->sort()->values())[(int)($prices->count() / 2)] ?? 0,
                'ranges' => $ranges
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Price statistics retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve price statistics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
