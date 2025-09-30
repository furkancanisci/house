<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies.
     */
    public function index()
    {
        $currencies = Currency::orderBy('sort_order')->get();
        return view('admin.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create()
    {
        return view('admin.currencies.create');
    }

    /**
     * Store a newly created currency.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:3|unique:currencies,code',
            'name_en' => 'required|string|max:100',
            'name_ar' => 'required|string|max:100',
            'name_ku' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        if (!isset($data['sort_order']) || empty($data['sort_order'])) {
            $maxOrder = Currency::max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        Currency::create($data);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency created successfully!');
    }

    /**
     * Show the form for editing the currency.
     */
    public function edit($id)
    {
        $currency = Currency::findOrFail($id);
        return view('admin.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency.
     */
    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:3|unique:currencies,code,' . $id,
            'name_en' => 'required|string|max:100',
            'name_ar' => 'required|string|max:100',
            'name_ku' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $currency->update($data);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency updated successfully!');
    }

    /**
     * Remove the specified currency.
     */
    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->delete();

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency deleted successfully!');
    }

    /**
     * Toggle currency active status
     */
    public function toggleStatus($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->is_active = !$currency->is_active;
        $currency->save();

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency status updated successfully!');
    }
}