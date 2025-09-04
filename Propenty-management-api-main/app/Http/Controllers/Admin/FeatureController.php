<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FeatureController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view features');

        $query = Feature::withCount('properties');

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

        if ($request->filled('bulk_action') && $request->filled('selected_features')) {
            $selectedFeatures = $request->selected_features;
            $action = $request->bulk_action;

            switch ($action) {
                case 'activate':
                    Feature::whereIn('id', $selectedFeatures)->update(['is_active' => true]);
                    return redirect()->back()->with('success', 'Selected features activated successfully.');
                    
                case 'deactivate':
                    Feature::whereIn('id', $selectedFeatures)->update(['is_active' => false]);
                    return redirect()->back()->with('success', 'Selected features deactivated successfully.');
                    
                case 'delete':
                    $canDelete = Feature::whereIn('id', $selectedFeatures)->doesntHave('properties')->pluck('id');
                    if ($canDelete->count() > 0) {
                        Feature::whereIn('id', $canDelete)->delete();
                        return redirect()->back()->with('success', $canDelete->count() . ' features deleted successfully.');
                    } else {
                        return redirect()->back()->with('error', 'Cannot delete features that are associated with properties.');
                    }
            }
        }

        $features = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.features.partials.table', compact('features'))->render(),
                'pagination' => $features->links()->render()
            ]);
        }

        return view('admin.features.index', compact('features'));
    }

    public function create()
    {
        Gate::authorize('create features');
        return view('admin.features.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create features');

        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
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
            $request->merge(['slug' => \Illuminate\Support\Str::slug($request->name_en)]);
        }

        Feature::create($request->all());

        return redirect()->route('admin.features.index')
                        ->with('success', 'Feature created successfully.');
    }

    public function show(Feature $feature)
    {
        Gate::authorize('view features');
        
        $feature->load(['properties' => function($query) {
            $query->with('city')->take(10);
        }]);
        
        return view('admin.features.show', compact('feature'));
    }

    public function edit(Feature $feature)
    {
        Gate::authorize('edit features');
        return view('admin.features.edit', compact('feature'));
    }

    public function update(Request $request, Feature $feature)
    {
        Gate::authorize('edit features');

        $request->validate([
            'name_en' => 'required|string|max:255|unique:features,name_en,' . $feature->id,
            'name_ar' => 'required|string|max:255|unique:features,name_ar,' . $feature->id,
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

        $feature->update($request->all());

        return redirect()->route('admin.features.index')
                        ->with('success', 'Feature updated successfully.');
    }

    public function destroy(Feature $feature)
    {
        Gate::authorize('delete features');

        if ($feature->properties()->count() > 0) {
            return redirect()->route('admin.features.index')
                            ->with('error', 'Cannot delete feature with associated properties.');
        }

        $feature->delete();

        return redirect()->route('admin.features.index')
                        ->with('success', 'Feature deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        Gate::authorize('edit features');

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'features' => 'required|array',
            'features.*' => 'exists:features,id'
        ]);

        $features = $request->features;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                Feature::whereIn('id', $features)->update(['is_active' => true]);
                $message = 'Selected features activated successfully.';
                break;
                
            case 'deactivate':
                Feature::whereIn('id', $features)->update(['is_active' => false]);
                $message = 'Selected features deactivated successfully.';
                break;
                
            case 'delete':
                $canDelete = Feature::whereIn('id', $features)->doesntHave('properties')->pluck('id');
                if ($canDelete->count() > 0) {
                    Feature::whereIn('id', $canDelete)->delete();
                    $message = $canDelete->count() . ' features deleted successfully.';
                } else {
                    return response()->json(['error' => 'Cannot delete features that are associated with properties.'], 400);
                }
                break;
        }

        return response()->json(['success' => $message]);
    }
}