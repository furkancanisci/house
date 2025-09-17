<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ViewType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ViewTypeController extends Controller
{
    /**
     * Display a listing of the view types.
     */
    public function index(): View
    {
        $viewTypes = ViewType::ordered()->paginate(15);

        return view('admin.view-types.index', compact('viewTypes'));
    }

    /**
     * Show the form for creating a new view type.
     */
    public function create(): View
    {
        return view('admin.view-types.create');
    }

    /**
     * Store a newly created view type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:view_types,value',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ViewType::create($validated);

        return redirect()->route('admin.view-types.index')
            ->with('success', 'View type created successfully.');
    }

    /**
     * Display the specified view type.
     */
    public function show(ViewType $viewType): View
    {
        return view('admin.view-types.show', compact('viewType'));
    }

    /**
     * Show the form for editing the specified view type.
     */
    public function edit(ViewType $viewType): View
    {
        return view('admin.view-types.edit', compact('viewType'));
    }

    /**
     * Update the specified view type in storage.
     */
    public function update(Request $request, ViewType $viewType): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:view_types,value,' . $viewType->id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $viewType->update($validated);

        return redirect()->route('admin.view-types.index')
            ->with('success', 'View type updated successfully.');
    }

    /**
     * Remove the specified view type from storage.
     */
    public function destroy(ViewType $viewType): RedirectResponse
    {
        // Check if any properties are using this view type
        if ($viewType->properties()->count() > 0) {
            return redirect()->route('admin.view-types.index')
                ->with('error', 'Cannot delete view type that is being used by properties.');
        }

        $viewType->delete();

        return redirect()->route('admin.view-types.index')
            ->with('success', 'View type deleted successfully.');
    }

    /**
     * Toggle the active status of the view type.
     */
    public function toggleStatus(ViewType $viewType): RedirectResponse
    {
        $viewType->update([
            'is_active' => !$viewType->is_active
        ]);

        $status = $viewType->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.view-types.index')
            ->with('success', "View type {$status} successfully.");
    }

    /**
     * Handle bulk actions on view types.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $this->authorize('edit view types');

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'view_types' => 'required|array',
            'view_types.*' => 'exists:view_types,id'
        ]);

        $viewTypes = $request->view_types;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                ViewType::whereIn('id', $viewTypes)->update(['is_active' => true]);
                $message = 'Selected view types activated successfully.';
                break;

            case 'deactivate':
                ViewType::whereIn('id', $viewTypes)->update(['is_active' => false]);
                $message = 'Selected view types deactivated successfully.';
                break;

            case 'delete':
                $canDelete = ViewType::whereIn('id', $viewTypes)->doesntHave('properties')->pluck('id');
                if ($canDelete->count() > 0) {
                    ViewType::whereIn('id', $canDelete)->delete();
                    $message = $canDelete->count() . ' view types deleted successfully.';

                    // Check if some couldn't be deleted
                    $couldntDelete = count($viewTypes) - $canDelete->count();
                    if ($couldntDelete > 0) {
                        $message .= " ({$couldntDelete} view types couldn't be deleted because they are associated with properties.)";
                    }
                } else {
                    return redirect()->route('admin.view-types.index')
                        ->with('error', 'Cannot delete view types that are associated with properties.');
                }
                break;
        }

        return redirect()->route('admin.view-types.index')->with('success', $message);
    }
}
