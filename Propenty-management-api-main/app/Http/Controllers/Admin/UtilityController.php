<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view utilities');

        $query = Utility::withCount('properties');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('bulk_action') && $request->filled('selected_utilities')) {
            $selectedUtilities = $request->selected_utilities;
            $action = $request->bulk_action;

            switch ($action) {
                case 'activate':
                    Utility::whereIn('id', $selectedUtilities)->update(['is_active' => true]);
                    return redirect()->back()->with('success', 'Selected utilities activated successfully.');
                    
                case 'deactivate':
                    Utility::whereIn('id', $selectedUtilities)->update(['is_active' => false]);
                    return redirect()->back()->with('success', 'Selected utilities deactivated successfully.');
                    
                case 'delete':
                    $canDelete = Utility::whereIn('id', $selectedUtilities)->doesntHave('properties')->pluck('id');
                    if ($canDelete->count() > 0) {
                        Utility::whereIn('id', $canDelete)->delete();
                        return redirect()->back()->with('success', $canDelete->count() . ' utilities deleted successfully.');
                    } else {
                        return redirect()->back()->with('error', 'Cannot delete utilities that are associated with properties.');
                    }
            }
        }

        $utilities = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.utilities.partials.table', compact('utilities'))->render(),
                'pagination' => $utilities->links()->render()
            ]);
        }

        return view('admin.utilities.index', compact('utilities'));
    }

    public function create()
    {
        Gate::authorize('create utilities');
        return view('admin.utilities.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create utilities');

        $request->validate([
            'name_en' => 'required|string|max:255|unique:utilities',
            'name_ar' => 'required|string|max:255|unique:utilities',
            'name_ku' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Generate slug from English name if not provided
        if (empty($request->slug)) {
            $request->merge(['slug' => \Str::slug($request->name_en)]);
        }

        Utility::create($request->all());

        return redirect()->route('admin.utilities.index')
                        ->with('success', 'Utility created successfully.');
    }

    public function show(Utility $utility)
    {
        Gate::authorize('view utilities');
        
        $utility->load(['properties' => function($query) {
            $query->with('city')->take(10);
        }]);
        
        return view('admin.utilities.show', compact('utility'));
    }

    public function edit(Utility $utility)
    {
        Gate::authorize('edit utilities');
        return view('admin.utilities.edit', compact('utility'));
    }

    public function update(Request $request, Utility $utility)
    {
        Gate::authorize('edit utilities');

        $request->validate([
            'name_en' => 'required|string|max:255|unique:utilities,name_en,' . $utility->id,
            'name_ar' => 'required|string|max:255|unique:utilities,name_ar,' . $utility->id,
            'name_ku' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Generate slug from English name if not provided
        if (empty($request->slug)) {
            $request->merge(['slug' => \Str::slug($request->name_en)]);
        }

        $utility->update($request->all());

        return redirect()->route('admin.utilities.index')
                        ->with('success', 'Utility updated successfully.');
    }

    public function destroy(Utility $utility)
    {
        Gate::authorize('delete utilities');

        if ($utility->properties()->count() > 0) {
            return redirect()->route('admin.utilities.index')
                            ->with('error', 'Cannot delete utility with associated properties.');
        }

        $utility->delete();

        return redirect()->route('admin.utilities.index')
                        ->with('success', 'Utility deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        Gate::authorize('edit utilities');

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'utilities' => 'required|array',
            'utilities.*' => 'exists:utilities,id'
        ]);

        $utilities = $request->utilities;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                Utility::whereIn('id', $utilities)->update(['is_active' => true]);
                $message = 'Selected utilities activated successfully.';
                break;
                
            case 'deactivate':
                Utility::whereIn('id', $utilities)->update(['is_active' => false]);
                $message = 'Selected utilities deactivated successfully.';
                break;
                
            case 'delete':
                $canDelete = Utility::whereIn('id', $utilities)->doesntHave('properties')->pluck('id');
                if ($canDelete->count() > 0) {
                    Utility::whereIn('id', $canDelete)->delete();
                    $message = $canDelete->count() . ' utilities deleted successfully.';
                } else {
                    return response()->json(['error' => 'Cannot delete utilities that are associated with properties.'], 400);
                }
                break;
        }

        return response()->json(['success' => $message]);
    }
}