<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeStat;
use App\Models\Property;
use Illuminate\Http\Request;

class HomeStatController extends Controller
{
    /**
     * Display a listing of home statistics.
     */
    public function index()
    {
        try {
            $stats = HomeStat::active()
                ->ordered()
                ->get()
                ->map(function ($stat) {
                    $number = $stat->number;

                    // For properties_listed, get dynamic count of active properties
                    if ($stat->key === 'properties_listed') {
                        $activePropertiesCount = Property::where('status', 'active')->count();
                        // Format the number with commas for better readability
                        if ($activePropertiesCount > 0) {
                            $formattedCount = number_format($activePropertiesCount);
                            $number = $formattedCount . '+';
                        } else {
                            $number = '0';
                        }
                    }

                    return [
                        'id' => $stat->id,
                        'key' => $stat->key,
                        'icon' => $stat->icon,
                        'number' => $number,
                        'label_ar' => $stat->label_ar,
                        'label_en' => $stat->label_en,
                        'label_ku' => $stat->label_ku,
                        'color' => $stat->color,
                        'is_active' => $stat->is_active,
                        'order' => $stat->order,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch home statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified home statistic.
     */
    public function show($id)
    {
        try {
            $stat = HomeStat::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $stat->id,
                    'key' => $stat->key,
                    'icon' => $stat->icon,
                    'number' => $stat->number,
                    'label_ar' => $stat->label_ar,
                    'label_en' => $stat->label_en,
                    'label_ku' => $stat->label_ku,
                    'color' => $stat->color,
                    'is_active' => $stat->is_active,
                    'order' => $stat->order,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Home statistic not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}