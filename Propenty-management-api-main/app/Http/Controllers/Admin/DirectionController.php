<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DirectionController extends Controller
{
    /**
     * Display a listing of the directions.
     */
    public function index(): View
    {
        $directions = Direction::ordered()->paginate(15);

        return view('admin.directions.index', compact('directions'));
    }

    /**
     * Show the form for creating a new direction.
     */
    public function create(): View
    {
        return view('admin.directions.create');
    }

    /**
     * Store a newly created direction in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:directions,value',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Direction::create($validated);

        return redirect()->route('admin.directions.index')
            ->with('success', 'Direction created successfully.');
    }

    /**
     * Display the specified direction.
     */
    public function show(Direction $direction): View
    {
        return view('admin.directions.show', compact('direction'));
    }

    /**
     * Show the form for editing the specified direction.
     */
    public function edit(Direction $direction): View
    {
        return view('admin.directions.edit', compact('direction'));
    }

    /**
     * Update the specified direction in storage.
     */
    public function update(Request $request, Direction $direction): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:directions,value,' . $direction->id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $direction->update($validated);

        return redirect()->route('admin.directions.index')
            ->with('success', 'Direction updated successfully.');
    }

    /**
     * Remove the specified direction from storage.
     */
    public function destroy(Direction $direction): RedirectResponse
    {
        // Check if any properties are using this direction
        if ($direction->properties()->count() > 0) {
            return redirect()->route('admin.directions.index')
                ->with('error', 'Cannot delete direction that is being used by properties.');
        }

        $direction->delete();

        return redirect()->route('admin.directions.index')
            ->with('success', 'Direction deleted successfully.');
    }

    /**
     * Toggle the active status of the direction.
     */
    public function toggleStatus(Direction $direction): RedirectResponse
    {
        $direction->update([
            'is_active' => !$direction->is_active
        ]);

        $status = $direction->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.directions.index')
            ->with('success', "Direction {$status} successfully.");
    }

    /**
     * Handle bulk actions on directions.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $this->authorize('edit directions');

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'directions' => 'required|array',
            'directions.*' => 'exists:directions,id'
        ]);

        $directions = $request->directions;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                Direction::whereIn('id', $directions)->update(['is_active' => true]);
                $message = 'Selected directions activated successfully.';
                break;

            case 'deactivate':
                Direction::whereIn('id', $directions)->update(['is_active' => false]);
                $message = 'Selected directions deactivated successfully.';
                break;

            case 'delete':
                $canDelete = Direction::whereIn('id', $directions)->doesntHave('properties')->pluck('id');
                if ($canDelete->count() > 0) {
                    Direction::whereIn('id', $canDelete)->delete();
                    $message = $canDelete->count() . ' directions deleted successfully.';

                    // Check if some couldn't be deleted
                    $couldntDelete = count($directions) - $canDelete->count();
                    if ($couldntDelete > 0) {
                        $message .= " ({$couldntDelete} directions couldn't be deleted because they are associated with properties.)";
                    }
                } else {
                    return redirect()->route('admin.directions.index')
                        ->with('error', 'Cannot delete directions that are associated with properties.');
                }
                break;
        }

        return redirect()->route('admin.directions.index')->with('success', $message);
    }
}
