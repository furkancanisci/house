<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuildingType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BuildingTypeController extends Controller
{
    /**
     * Display a listing of the building types.
     */
    public function index(): View
    {
        $buildingTypes = BuildingType::ordered()->paginate(15);
        
        return view('admin.building-types.index', compact('buildingTypes'));
    }

    /**
     * Show the form for creating a new building type.
     */
    public function create(): View
    {
        return view('admin.building-types.create');
    }

    /**
     * Store a newly created building type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:building_types,value',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        BuildingType::create($validated);

        return redirect()->route('admin.building-types.index')
            ->with('success', 'Building type created successfully.');
    }

    /**
     * Display the specified building type.
     */
    public function show(BuildingType $buildingType): View
    {
        return view('admin.building-types.show', compact('buildingType'));
    }

    /**
     * Show the form for editing the specified building type.
     */
    public function edit(BuildingType $buildingType): View
    {
        return view('admin.building-types.edit', compact('buildingType'));
    }

    /**
     * Update the specified building type in storage.
     */
    public function update(Request $request, BuildingType $buildingType): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:building_types,value,' . $buildingType->id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $buildingType->update($validated);

        return redirect()->route('admin.building-types.index')
            ->with('success', 'Building type updated successfully.');
    }

    /**
     * Remove the specified building type from storage.
     */
    public function destroy(BuildingType $buildingType): RedirectResponse
    {
        // Check if any properties are using this building type
        if ($buildingType->properties()->count() > 0) {
            return redirect()->route('admin.building-types.index')
                ->with('error', 'Cannot delete building type that is being used by properties.');
        }

        $buildingType->delete();

        return redirect()->route('admin.building-types.index')
            ->with('success', 'Building type deleted successfully.');
    }

    /**
     * Toggle the active status of the building type.
     */
    public function toggleStatus(BuildingType $buildingType): RedirectResponse
    {
        $buildingType->update([
            'is_active' => !$buildingType->is_active
        ]);

        $status = $buildingType->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.building-types.index')
            ->with('success', "Building type {$status} successfully.");
    }

    /**
     * Handle bulk actions on building types.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $this->authorize('edit building types');

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'building_types' => 'required|array',
            'building_types.*' => 'exists:building_types,id'
        ]);

        $buildingTypes = $request->building_types;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                BuildingType::whereIn('id', $buildingTypes)->update(['is_active' => true]);
                $message = 'Selected building types activated successfully.';
                break;

            case 'deactivate':
                BuildingType::whereIn('id', $buildingTypes)->update(['is_active' => false]);
                $message = 'Selected building types deactivated successfully.';
                break;

            case 'delete':
                $canDelete = BuildingType::whereIn('id', $buildingTypes)->doesntHave('properties')->pluck('id');
                if ($canDelete->count() > 0) {
                    BuildingType::whereIn('id', $canDelete)->delete();
                    $message = $canDelete->count() . ' building types deleted successfully.';

                    // Check if some couldn't be deleted
                    $couldntDelete = count($buildingTypes) - $canDelete->count();
                    if ($couldntDelete > 0) {
                        $message .= " ({$couldntDelete} building types couldn't be deleted because they are associated with properties.)";
                    }
                } else {
                    return redirect()->route('admin.building-types.index')
                        ->with('error', 'Cannot delete building types that are associated with properties.');
                }
                break;
        }

        return redirect()->route('admin.building-types.index')->with('success', $message);
    }
}