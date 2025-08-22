<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view amenities');

        $query = Amenity::withCount('properties');

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

        if ($request->filled('bulk_action') && $request->filled('selected_amenities')) {
            $selectedAmenities = $request->selected_amenities;
            $action = $request->bulk_action;

            switch ($action) {
                case 'activate':
                    Amenity::whereIn('id', $selectedAmenities)->update(['is_active' => true]);
                    return redirect()->back()->with('success', 'Selected amenities activated successfully.');
                    
                case 'deactivate':
                    Amenity::whereIn('id', $selectedAmenities)->update(['is_active' => false]);
                    return redirect()->back()->with('success', 'Selected amenities deactivated successfully.');
                    
                case 'delete':
                    $canDelete = Amenity::whereIn('id', $selectedAmenities)->doesntHave('properties')->pluck('id');
                    if ($canDelete->count() > 0) {
                        Amenity::whereIn('id', $canDelete)->delete();
                        return redirect()->back()->with('success', $canDelete->count() . ' amenities deleted successfully.');
                    } else {
                        return redirect()->back()->with('error', 'Cannot delete amenities that are associated with properties.');
                    }
            }
        }

        $amenities = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.amenities.partials.table', compact('amenities'))->render(),
                'pagination' => $amenities->links()->render()
            ]);
        }

        return view('admin.amenities.index', compact('amenities'));
    }

    public function create()
    {
        Gate::authorize('create amenities');
        return view('admin.amenities.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create amenities');

        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255|unique:amenities',
            'name_ar' => 'required|string|max:255|unique:amenities',
            'slug' => 'required|string|max:255|unique:amenities',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        Amenity::create($request->all());

        return redirect()->route('admin.amenities.index')
                        ->with('success', 'Amenity created successfully.');
    }

    public function show(Amenity $amenity)
    {
        Gate::authorize('view amenities');
        
        $amenity->load(['properties' => function($query) {
            $query->with('city')->take(10);
        }]);
        
        return view('admin.amenities.show', compact('amenity'));
    }

    public function edit(Amenity $amenity)
    {
        Gate::authorize('edit amenities');
        return view('admin.amenities.edit', compact('amenity'));
    }

    public function update(Request $request, Amenity $amenity)
    {
        Gate::authorize('edit amenities');

        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255|unique:amenities,name_en,' . $amenity->id,
            'name_ar' => 'required|string|max:255|unique:amenities,name_ar,' . $amenity->id,
            'slug' => 'required|string|max:255|unique:amenities,slug,' . $amenity->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $amenity->update($request->all());

        return redirect()->route('admin.amenities.index')
                        ->with('success', 'Amenity updated successfully.');
    }

    public function destroy(Amenity $amenity)
    {
        Gate::authorize('delete amenities');

        if ($amenity->properties()->count() > 0) {
            return redirect()->route('admin.amenities.index')
                            ->with('error', 'Cannot delete amenity with associated properties.');
        }

        $amenity->delete();

        return redirect()->route('admin.amenities.index')
                        ->with('success', 'Amenity deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        Gate::authorize('edit amenities');

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'amenities' => 'required|array',
            'amenities.*' => 'exists:amenities,id'
        ]);

        $amenities = $request->amenities;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                Amenity::whereIn('id', $amenities)->update(['is_active' => true]);
                $message = 'Selected amenities activated successfully.';
                break;
                
            case 'deactivate':
                Amenity::whereIn('id', $amenities)->update(['is_active' => false]);
                $message = 'Selected amenities deactivated successfully.';
                break;
                
            case 'delete':
                $canDelete = Amenity::whereIn('id', $amenities)->doesntHave('properties')->pluck('id');
                if ($canDelete->count() > 0) {
                    Amenity::whereIn('id', $canDelete)->delete();
                    $message = $canDelete->count() . ' amenities deleted successfully.';
                } else {
                    return response()->json(['error' => 'Cannot delete amenities that are associated with properties.'], 400);
                }
                break;
        }

        return response()->json(['success' => $message]);
    }
}