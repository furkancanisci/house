<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyDocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PropertyDocumentTypeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view property document types');

        $query = PropertyDocumentType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ku', 'like', "%{$search}%")
                  ->orWhere('description_ar', 'like', "%{$search}%")
                  ->orWhere('description_en', 'like', "%{$search}%")
                  ->orWhere('description_ku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('bulk_action') && $request->filled('selected_document_types')) {
            $selectedDocumentTypes = $request->selected_document_types;
            $action = $request->bulk_action;

            switch ($action) {
                case 'activate':
                    PropertyDocumentType::whereIn('id', $selectedDocumentTypes)->update(['is_active' => true]);
                    return redirect()->back()->with('success', 'Selected document types activated successfully.');
                    
                case 'deactivate':
                    PropertyDocumentType::whereIn('id', $selectedDocumentTypes)->update(['is_active' => false]);
                    return redirect()->back()->with('success', 'Selected document types deactivated successfully.');
                    
                case 'delete':
                    $canDelete = PropertyDocumentType::whereIn('id', $selectedDocumentTypes)->doesntHave('properties')->pluck('id');
                    if ($canDelete->count() > 0) {
                        PropertyDocumentType::whereIn('id', $canDelete)->delete();
                        return redirect()->back()->with('success', $canDelete->count() . ' document types deleted successfully.');
                    } else {
                        return redirect()->back()->with('error', 'Cannot delete document types that are associated with properties.');
                    }
            }
        }

        $documentTypes = $query->orderBy('sort_order')->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.property-document-types.partials.table', compact('documentTypes'))->render(),
                'pagination' => $documentTypes->links()->render()
            ]);
        }

        return view('admin.property-document-types.index', compact('documentTypes'));
    }

    public function create()
    {
        Gate::authorize('create property document types');
        return view('admin.property-document-types.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create property document types');

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ku' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        PropertyDocumentType::create($request->all());

        return redirect()->route('admin.property-document-types.index')
                        ->with('success', 'Property document type created successfully.');
    }

    public function show(PropertyDocumentType $propertyDocumentType)
    {
        Gate::authorize('view property document types');
        
        $propertyDocumentType->load(['properties' => function($query) {
            $query->with('city')->take(10);
        }]);
        
        return view('admin.property-document-types.show', compact('propertyDocumentType'));
    }

    public function edit(PropertyDocumentType $propertyDocumentType)
    {
        Gate::authorize('edit property document types');
        return view('admin.property-document-types.edit', compact('propertyDocumentType'));
    }

    public function update(Request $request, PropertyDocumentType $propertyDocumentType)
    {
        Gate::authorize('edit property document types');

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ku' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $propertyDocumentType->update($request->all());

        return redirect()->route('admin.property-document-types.index')
                        ->with('success', 'Property document type updated successfully.');
    }

    public function destroy(PropertyDocumentType $propertyDocumentType)
    {
        Gate::authorize('delete property document types');

        if ($propertyDocumentType->properties()->count() > 0) {
            return redirect()->route('admin.property-document-types.index')
                            ->with('error', 'Cannot delete document type with associated properties.');
        }

        $propertyDocumentType->delete();

        return redirect()->route('admin.property-document-types.index')
                        ->with('success', 'Property document type deleted successfully.');
    }

    public function toggleStatus(PropertyDocumentType $propertyDocumentType)
    {
        Gate::authorize('edit property document types');

        $propertyDocumentType->update([
            'is_active' => !$propertyDocumentType->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document type status updated successfully',
            'data' => [
                'id' => $propertyDocumentType->id,
                'is_active' => $propertyDocumentType->is_active,
            ],
        ]);
    }

    public function bulkAction(Request $request)
    {
        Gate::authorize('edit property document types');

        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'document_types' => 'required|array',
            'document_types.*' => 'exists:property_document_types,id'
        ]);

        $documentTypes = $request->document_types;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                PropertyDocumentType::whereIn('id', $documentTypes)->update(['is_active' => true]);
                $message = 'Selected document types activated successfully.';
                break;
                
            case 'deactivate':
                PropertyDocumentType::whereIn('id', $documentTypes)->update(['is_active' => false]);
                $message = 'Selected document types deactivated successfully.';
                break;
                
            case 'delete':
                $canDelete = PropertyDocumentType::whereIn('id', $documentTypes)->doesntHave('properties')->pluck('id');
                if ($canDelete->count() > 0) {
                    PropertyDocumentType::whereIn('id', $canDelete)->delete();
                    $message = $canDelete->count() . ' document types deleted successfully.';
                } else {
                    return response()->json(['error' => 'Cannot delete document types that are associated with properties.'], 400);
                }
                break;
        }

        return response()->json(['success' => $message]);
    }
}