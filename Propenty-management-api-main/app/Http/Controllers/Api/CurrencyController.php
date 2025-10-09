<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    /**
     * Get all active currencies
     */
    public function index(Request $request): JsonResponse
    {
        $lang = $request->get('lang', 'ar');

        $currencies = Currency::active()
            ->ordered()
            ->get()
            ->map(function ($currency) use ($lang) {
                return [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'name' => $currency->getLocalizedName($lang),
                    'name_ar' => $currency->name_ar,
                    'name_en' => $currency->name_en,
                    'name_ku' => $currency->name_ku,
                    'symbol' => $currency->symbol,
                    'is_active' => $currency->is_active,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $currencies,
        ]);
    }

    /**
     * Get currencies as simple options for dropdown
     */
    public function options(Request $request): JsonResponse
    {
        $lang = $request->get('lang', 'ar');

        $currencies = Currency::active()
            ->ordered()
            ->get()
            ->map(function ($currency) use ($lang) {
                return [
                    'value' => $currency->code,
                    'label' => $currency->getLocalizedName($lang) . ' (' . $currency->symbol . ')',
                    'symbol' => $currency->symbol,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $currencies,
        ]);
    }
}
