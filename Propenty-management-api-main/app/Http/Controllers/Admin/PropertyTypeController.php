<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view property types');

        $propertyTypes = PropertyType::with('parent', 'children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.property-types.index', compact('propertyTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create property types');

        $parentTypes = PropertyType::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.property-types.create', compact('parentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create property types');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_types,name',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:property_types,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        PropertyType::create($validated);

        return redirect()->route('admin.property-types.index')
            ->with('success', 'Property type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PropertyType $propertyType)
    {
        $this->authorize('view property types');

        $propertyType->load('parent', 'children', 'properties');

        return view('admin.property-types.show', compact('propertyType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PropertyType $propertyType)
    {
        $this->authorize('edit property types');

        $parentTypes = PropertyType::whereNull('parent_id')
            ->where('is_active', true)
            ->where('id', '!=', $propertyType->id)
            ->orderBy('name')
            ->get();

        return view('admin.property-types.edit', compact('propertyType', 'parentTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertyType $propertyType)
    {
        $this->authorize('edit property types');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_types,name,' . $propertyType->id,
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:property_types,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? $propertyType->sort_order ?? 0;

        // Prevent setting parent to self or its descendant
        if ($validated['parent_id'] == $propertyType->id) {
            return back()->withErrors(['parent_id' => 'A property type cannot be its own parent.']);
        }

        $propertyType->update($validated);

        return redirect()->route('admin.property-types.index')
            ->with('success', 'Property type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertyType $propertyType)
    {
        $this->authorize('delete property types');

        // Check if there are properties using this type
        if ($propertyType->properties()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete property type that is being used by properties.']);
        }

        // Check if there are child property types
        if ($propertyType->children()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete property type that has child categories.']);
        }

        $propertyType->delete();

        return redirect()->route('admin.property-types.index')
            ->with('success', 'Property type deleted successfully.');
    }
}
