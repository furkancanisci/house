<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PriceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $priceTypes = PriceType::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.price-types.index', compact('priceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.price-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ku' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:price_types,key',
            'listing_type' => 'required|in:rent,sale,both',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        
        // Generate key from English name if not provided
        if (empty($data['key'])) {
            $data['key'] = Str::slug($data['name_en'], '_');
        }

        PriceType::create($data);

        return redirect()->route('admin.price-types.index')
            ->with('success', __('admin.price_type_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceType $priceType)
    {
        return view('admin.price-types.show', compact('priceType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PriceType $priceType)
    {
        return view('admin.price-types.edit', compact('priceType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PriceType $priceType)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ku' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:price_types,key,' . $priceType->id,
            'listing_type' => 'required|in:rent,sale,both',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $priceType->update($data);

        return redirect()->route('admin.price-types.index')
            ->with('success', __('admin.price_type_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceType $priceType)
    {
        // Check if price type is being used by any properties
        if ($priceType->properties()->count() > 0) {
            return redirect()->route('admin.price-types.index')
                ->with('error', __('admin.cannot_delete_price_type_in_use'));
        }

        $priceType->delete();

        return redirect()->route('admin.price-types.index')
            ->with('success', __('admin.price_type_deleted_successfully'));
    }
}
