<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WindowType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WindowTypeController extends Controller
{
    /**
     * Display a listing of the window types.
     */
    public function index(): View
    {
        $windowTypes = WindowType::ordered()->paginate(15);
        
        return view('admin.window-types.index', compact('windowTypes'));
    }

    /**
     * Show the form for creating a new window type.
     */
    public function create(): View
    {
        return view('admin.window-types.create');
    }

    /**
     * Store a newly created window type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:window_types,value',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        WindowType::create($validated);

        return redirect()->route('admin.window-types.index')
            ->with('success', 'Window type created successfully.');
    }

    /**
     * Display the specified window type.
     */
    public function show(WindowType $windowType): View
    {
        return view('admin.window-types.show', compact('windowType'));
    }

    /**
     * Show the form for editing the specified window type.
     */
    public function edit(WindowType $windowType): View
    {
        return view('admin.window-types.edit', compact('windowType'));
    }

    /**
     * Update the specified window type in storage.
     */
    public function update(Request $request, WindowType $windowType): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:window_types,value,' . $windowType->id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $windowType->update($validated);

        return redirect()->route('admin.window-types.index')
            ->with('success', 'Window type updated successfully.');
    }

    /**
     * Remove the specified window type from storage.
     */
    public function destroy(WindowType $windowType): RedirectResponse
    {
        // Check if any properties are using this window type
        if ($windowType->properties()->count() > 0) {
            return redirect()->route('admin.window-types.index')
                ->with('error', 'Cannot delete window type that is being used by properties.');
        }

        $windowType->delete();

        return redirect()->route('admin.window-types.index')
            ->with('success', 'Window type deleted successfully.');
    }

    /**
     * Toggle the active status of the window type.
     */
    public function toggleStatus(WindowType $windowType): RedirectResponse
    {
        $windowType->update([
            'is_active' => !$windowType->is_active
        ]);

        $status = $windowType->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.window-types.index')
            ->with('success', "Window type {$status} successfully.");
    }

    /**
     * Handle bulk actions on window types.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $this->authorize('edit window types');

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'window_types' => 'required|array',
            'window_types.*' => 'exists:window_types,id'
        ]);

        $windowTypes = $request->window_types;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                WindowType::whereIn('id', $windowTypes)->update(['is_active' => true]);
                $message = 'Selected window types activated successfully.';
                break;

            case 'deactivate':
                WindowType::whereIn('id', $windowTypes)->update(['is_active' => false]);
                $message = 'Selected window types deactivated successfully.';
                break;

            case 'delete':
                $canDelete = WindowType::whereIn('id', $windowTypes)->doesntHave('properties')->pluck('id');
                if ($canDelete->count() > 0) {
                    WindowType::whereIn('id', $canDelete)->delete();
                    $message = $canDelete->count() . ' window types deleted successfully.';

                    // Check if some couldn't be deleted
                    $couldntDelete = count($windowTypes) - $canDelete->count();
                    if ($couldntDelete > 0) {
                        $message .= " ({$couldntDelete} window types couldn't be deleted because they are associated with properties.)";
                    }
                } else {
                    return redirect()->route('admin.window-types.index')
                        ->with('error', 'Cannot delete window types that are associated with properties.');
                }
                break;
        }

        return redirect()->route('admin.window-types.index')->with('success', $message);
    }
}