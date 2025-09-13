<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of property types/categories.
     */
    public function index()
    {
        $this->authorize('view categories');

        $categories = PropertyType::withCount('properties')
            ->orderByRaw('COALESCE(NULLIF(name_ar, \'\'), name) ASC')
            ->paginate(20);

        $stats = [
            'total' => PropertyType::count(),
            'with_properties' => PropertyType::has('properties')->count(),
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $this->authorize('create categories');

        $parentCategories = PropertyType::whereNull('parent_id')
            ->orderByRaw('COALESCE(NULLIF(name_ar, \'\'), name) ASC')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $this->authorize('create categories');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_types,name',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:property_types,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:property_types,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set defaults
        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        PropertyType::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(PropertyType $category)
    {
        $this->authorize('view categories');

        $category->load(['parent', 'children', 'properties']);

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(PropertyType $category)
    {
        $this->authorize('edit categories');

        $parentCategories = PropertyType::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderByRaw('COALESCE(NULLIF(name_ar, \'\'), name) ASC')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, PropertyType $category)
    {
        $this->authorize('edit categories');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_types,name,' . $category->id,
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:property_types,slug,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:property_types,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set defaults
        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Prevent setting self as parent
        if ($validated['parent_id'] === $category->id) {
            return back()->withErrors(['parent_id' => 'A category cannot be its own parent.']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(PropertyType $category)
    {
        $this->authorize('delete categories');

        // Check if category has properties
        if ($category->properties()->count() > 0) {
            return back()->with('error', 
                'Cannot delete category that has properties assigned to it. 
                Please reassign the properties first.');
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return back()->with('error', 
                'Cannot delete category that has subcategories. 
                Please delete or reassign the subcategories first.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Restore soft deleted category.
     */
    public function restore($id)
    {
        $this->authorize('create categories');

        $category = PropertyType::withTrashed()->findOrFail($id);
        $category->restore();

        return back()->with('success', 'Category restored successfully.');
    }
}