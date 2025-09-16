<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FloorType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FloorTypeController extends Controller
{
    /**
     * Display a listing of the floor types.
     */
    public function index(): View
    {
        $floorTypes = FloorType::ordered()->paginate(15);
        
        return view('admin.floor-types.index', compact('floorTypes'));
    }

    /**
     * Show the form for creating a new floor type.
     */
    public function create(): View
    {
        return view('admin.floor-types.create');
    }

    /**
     * Store a newly created floor type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:floor_types,value',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        FloorType::create($validated);

        return redirect()->route('admin.floor-types.index')
            ->with('success', 'Floor type created successfully.');
    }

    /**
     * Display the specified floor type.
     */
    public function show(FloorType $floorType): View
    {
        return view('admin.floor-types.show', compact('floorType'));
    }

    /**
     * Show the form for editing the specified floor type.
     */
    public function edit(FloorType $floorType): View
    {
        return view('admin.floor-types.edit', compact('floorType'));
    }

    /**
     * Update the specified floor type in storage.
     */
    public function update(Request $request, FloorType $floorType): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:floor_types,value,' . $floorType->id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $floorType->update($validated);

        return redirect()->route('admin.floor-types.index')
            ->with('success', 'Floor type updated successfully.');
    }

    /**
     * Remove the specified floor type from storage.
     */
    public function destroy(FloorType $floorType): RedirectResponse
    {
        // Check if any properties are using this floor type
        if ($floorType->properties()->count() > 0) {
            return redirect()->route('admin.floor-types.index')
                ->with('error', 'Cannot delete floor type that is being used by properties.');
        }

        $floorType->delete();

        return redirect()->route('admin.floor-types.index')
            ->with('success', 'Floor type deleted successfully.');
    }

    /**
     * Toggle the active status of the floor type.
     */
    public function toggleStatus(FloorType $floorType): RedirectResponse
    {
        $floorType->update([
            'is_active' => !$floorType->is_active
        ]);

        $status = $floorType->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.floor-types.index')
            ->with('success', "Floor type {$status} successfully.");
    }
}