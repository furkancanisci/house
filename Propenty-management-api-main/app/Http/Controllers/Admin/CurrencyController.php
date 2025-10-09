<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the currencies.
     */
    public function index(): View
    {
        $currencies = Currency::ordered()->paginate(15);

        return view('admin.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create(): View
    {
        return view('admin.currencies.create');
    }

    /**
     * Store a newly created currency in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'symbol' => 'required|string|max:10',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Currency::create($validated);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency created successfully.');
    }

    /**
     * Display the specified currency.
     */
    public function show(Currency $currency): View
    {
        return view('admin.currencies.show', compact('currency'));
    }

    /**
     * Show the form for editing the specified currency.
     */
    public function edit(Currency $currency): View
    {
        return view('admin.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency in storage.
     */
    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code,' . $currency->id,
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'symbol' => 'required|string|max:10',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $currency->update($validated);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency updated successfully.');
    }

    /**
     * Remove the specified currency from storage.
     */
    public function destroy(Currency $currency): RedirectResponse
    {
        $currency->delete();

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency deleted successfully.');
    }

    /**
     * Toggle the active status of the specified currency.
     */
    public function toggleStatus(Currency $currency): RedirectResponse
    {
        $currency->update(['is_active' => !$currency->is_active]);

        return redirect()->route('admin.currencies.index')
            ->with('success', 'Currency status updated successfully.');
    }
}
