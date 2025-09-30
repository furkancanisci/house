<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Get all active currencies
     */
    public function index(Request $request)
    {
        $query = Currency::query();

        // Filter by active status (default to active only)
        if ($request->has('all') && $request->all == 'true') {
            // Return all currencies including inactive
        } else {
            $query->active();
        }

        $currencies = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $currencies->map(function ($currency) use ($request) {
                $lang = $request->get('lang', 'en');
                return [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'name' => $currency->getLocalizedName($lang),
                    'name_en' => $currency->name_en,
                    'name_ar' => $currency->name_ar,
                    'name_ku' => $currency->name_ku,
                    'is_active' => $currency->is_active,
                    'sort_order' => $currency->sort_order,
                ];
            })
        ]);
    }

    /**
     * Get a single currency by code or ID
     */
    public function show($identifier)
    {
        // Try to find by code first, then by ID
        $currency = Currency::where('code', strtoupper($identifier))
            ->orWhere('id', $identifier)
            ->first();

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $currency->id,
                'code' => $currency->code,
                'name_en' => $currency->name_en,
                'name_ar' => $currency->name_ar,
                'name_ku' => $currency->name_ku,
                'is_active' => $currency->is_active,
                'sort_order' => $currency->sort_order,
            ]
        ]);
    }
}