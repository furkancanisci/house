<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\City;
use App\Models\PropertyType;
use App\Models\User;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties.
     */
    public function index(Request $request)
    {
        $this->authorize('view properties');

        $properties = QueryBuilder::for(Property::class)
            ->with(['user', 'media'])
            ->allowedFilters([
                'title',
                'status',
                'listing_type',
                'property_type',
                'city',
                'state',
                'is_featured',
                AllowedFilter::exact('user_id'),
                AllowedFilter::scope('price_range', 'inPriceRange'),
                AllowedFilter::scope('bedrooms'),
                AllowedFilter::scope('created_between', 'dateRange'),
            ])
            ->allowedSorts(['title', 'price', 'created_at', 'updated_at', 'status'])
            ->defaultSort('-created_at')
            ->paginate(20)
            ->appends($request->query());

        // Get filter options
        $cities = City::orderBy('name')->pluck('name', 'name');
        $propertyTypes = PropertyType::orderBy('name')->pluck('name', 'name');
        $users = User::select('id', 'first_name', 'last_name')->get()
            ->mapWithKeys(fn($user) => [$user->id => $user->full_name]);

        $stats = [
            'total' => Property::count(),
            'active' => Property::where('status', 'active')->count(),
            'pending' => Property::where('status', 'pending')->count(),
            'featured' => Property::where('is_featured', true)->count(),
        ];

        return view('admin.properties.index', compact(
            'properties',
            'cities',
            'propertyTypes',
            'users',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new property.
     */
    public function create()
    {
        $this->authorize('create properties');

        $cities = City::orderBy('name')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $amenities = Amenity::active()->orderBy('category')->orderBy('name')->get()->groupBy('category');
        $users = User::select('id', 'first_name', 'last_name')->get()
            ->mapWithKeys(fn($user) => [$user->id => $user->full_name]);

        return view('admin.properties.create', compact(
            'cities',
            'propertyTypes',
            'amenities',
            'users'
        ));
    }

    /**
     * Store a newly created property.
     */
    public function store(Request $request)
    {
        $this->authorize('create properties');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type' => 'required|string',
            'listing_type' => 'required|in:rent,sale',
            'price' => 'required|numeric|min:0',
            'price_type' => 'nullable|in:monthly,yearly',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'bedrooms' => 'nullable|integer|min:0|max:20',
            'bathrooms' => 'nullable|integer|min:0|max:20',
            'square_feet' => 'nullable|integer|min:1',
            'lot_size' => 'nullable|integer|min:1',
            'year_built' => 'nullable|integer|min:1800|max:' . (date('Y') + 2),
            'parking_type' => 'nullable|string',
            'parking_spaces' => 'nullable|integer|min:0|max:20',
            'status' => 'required|in:draft,pending,active,rejected,expired',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',
            'available_from' => 'nullable|date|after_or_equal:today',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'nearby_places' => 'nullable|array',
            'contact_name' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:100',
            'user_id' => 'required|exists:users,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ]);

        DB::beginTransaction();
        try {
            $property = Property::create($validated);

            // Handle amenities
            if ($request->has('amenities_list')) {
                $amenityIds = Amenity::whereIn('id', $request->amenities_list)->pluck('id');
                $property->amenities()->sync($amenityIds);
            }

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $property->addMediaFromRequest("images.{$index}")
                        ->toMediaCollection('images');
                }
            }

            // Set main image if specified
            if ($request->has('main_image_index') && $request->hasFile('images')) {
                $mainIndex = (int) $request->main_image_index;
                if (isset($request->file('images')[$mainIndex])) {
                    $property->addMediaFromRequest("images.{$mainIndex}")
                        ->toMediaCollection('main_image');
                }
            }

            DB::commit();

            return redirect()->route('admin.properties.show', $property)
                ->with('success', 'Property created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error creating property: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property)
    {
        $this->authorize('view properties');

        $property->load(['user', 'media', 'amenities', 'views', 'favoritedByUsers']);

        return view('admin.properties.show', compact('property'));
    }

    /**
     * Show the form for editing the property.
     */
    public function edit(Property $property)
    {
        $this->authorize('edit properties');

        $cities = City::orderBy('name')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $amenities = Amenity::active()->orderBy('category')->orderBy('name')->get()->groupBy('category');
        $users = User::select('id', 'first_name', 'last_name')->get()
            ->mapWithKeys(fn($user) => [$user->id => $user->full_name]);

        $property->load(['amenities', 'media']);

        return view('admin.properties.edit', compact(
            'property',
            'cities',
            'propertyTypes',
            'amenities',
            'users'
        ));
    }

    /**
     * Update the specified property.
     */
    public function update(Request $request, Property $property)
    {
        $this->authorize('edit properties');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type' => 'required|string',
            'listing_type' => 'required|in:rent,sale',
            'price' => 'required|numeric|min:0',
            'price_type' => 'nullable|in:monthly,yearly',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'bedrooms' => 'nullable|integer|min:0|max:20',
            'bathrooms' => 'nullable|integer|min:0|max:20',
            'square_feet' => 'nullable|integer|min:1',
            'lot_size' => 'nullable|integer|min:1',
            'year_built' => 'nullable|integer|min:1800|max:' . (date('Y') + 2),
            'parking_type' => 'nullable|string',
            'parking_spaces' => 'nullable|integer|min:0|max:20',
            'status' => 'required|in:draft,pending,active,rejected,expired',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',
            'available_from' => 'nullable|date|after_or_equal:today',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'nearby_places' => 'nullable|array',
            'contact_name' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:100',
            'user_id' => 'required|exists:users,id',
            'new_images' => 'nullable|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
        ]);

        DB::beginTransaction();
        try {
            $property->update($validated);

            // Handle amenities
            if ($request->has('amenities_list')) {
                $amenityIds = Amenity::whereIn('id', $request->amenities_list)->pluck('id');
                $property->amenities()->sync($amenityIds);
            } else {
                $property->amenities()->detach();
            }

            // Remove selected images
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $mediaId) {
                    $media = $property->media()->find($mediaId);
                    if ($media) {
                        $media->delete();
                    }
                }
            }

            // Add new images
            if ($request->hasFile('new_images')) {
                foreach ($request->file('new_images') as $index => $image) {
                    $property->addMediaFromRequest("new_images.{$index}")
                        ->toMediaCollection('images');
                }
            }

            DB::commit();

            return redirect()->route('admin.properties.show', $property)
                ->with('success', 'Property updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating property: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Property $property)
    {
        $this->authorize('delete properties');

        try {
            $property->delete();

            return redirect()->route('admin.properties.index')
                ->with('success', 'Property deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting property: ' . $e->getMessage());
        }
    }

    /**
     * Handle bulk actions on properties.
     */
    public function bulkAction(Request $request)
    {
        $this->authorize('bulk manage properties');

        $request->validate([
            'action' => 'required|in:approve,reject,feature,unfeature,publish,unpublish,delete',
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'exists:properties,id',
            'reject_reason' => 'required_if:action,reject|string|max:500',
        ]);

        $properties = Property::whereIn('id', $request->property_ids);
        $count = $properties->count();

        DB::beginTransaction();
        try {
            switch ($request->action) {
                case 'approve':
                    $properties->update(['status' => 'active']);
                    break;
                case 'reject':
                    $properties->update([
                        'status' => 'rejected',
                        'internal_notes' => $request->reject_reason
                    ]);
                    break;
                case 'feature':
                    $properties->update(['is_featured' => true]);
                    break;
                case 'unfeature':
                    $properties->update(['is_featured' => false]);
                    break;
                case 'publish':
                    $properties->update(['is_available' => true]);
                    break;
                case 'unpublish':
                    $properties->update(['is_available' => false]);
                    break;
                case 'delete':
                    $properties->delete();
                    break;
            }

            DB::commit();

            $action = ucfirst($request->action);
            return back()->with('success', "{$action} action applied to {$count} properties.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve a property.
     */
    public function approve(Property $property)
    {
        $this->authorize('approve properties');

        $property->update(['status' => 'active']);

        return back()->with('success', 'Property approved successfully.');
    }

    /**
     * Reject a property.
     */
    public function reject(Request $request, Property $property)
    {
        $this->authorize('reject properties');

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $property->update([
            'status' => 'rejected',
            'internal_notes' => $request->reason
        ]);

        return back()->with('success', 'Property rejected.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeature(Property $property)
    {
        $this->authorize('feature properties');

        $property->update(['is_featured' => !$property->is_featured]);

        $status = $property->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Property {$status} successfully.");
    }

    /**
     * Toggle publish status.
     */
    public function togglePublish(Property $property)
    {
        $this->authorize('publish properties');

        $property->update(['is_available' => !$property->is_available]);

        $status = $property->is_available ? 'published' : 'unpublished';
        return back()->with('success', "Property {$status} successfully.");
    }

    /**
     * Restore soft deleted property.
     */
    public function restore($id)
    {
        $this->authorize('create properties');

        $property = Property::withTrashed()->findOrFail($id);
        $property->restore();

        return back()->with('success', 'Property restored successfully.');
    }

    /**
     * Export properties to CSV.
     */
    public function exportCsv(Request $request)
    {
        $this->authorize('export properties');

        $query = QueryBuilder::for(Property::class)
            ->with(['user'])
            ->allowedFilters([
                'title',
                'status',
                'listing_type',
                'property_type',
                'city',
                'state',
                'is_featured',
                AllowedFilter::exact('user_id'),
            ]);

        $properties = $query->get();

        $filename = 'properties_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($properties) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID', 'Title', 'Type', 'Listing Type', 'Price', 'Status',
                'City', 'State', 'Bedrooms', 'Bathrooms', 'Square Feet',
                'Owner', 'Featured', 'Available', 'Created At'
            ]);

            foreach ($properties as $property) {
                fputcsv($file, [
                    $property->id,
                    $property->title,
                    $property->property_type,
                    $property->listing_type,
                    $property->price,
                    $property->status,
                    $property->city,
                    $property->state,
                    $property->bedrooms,
                    $property->bathrooms,
                    $property->square_feet,
                    $property->user?->full_name ?? 'Unknown',
                    $property->is_featured ? 'Yes' : 'No',
                    $property->is_available ? 'Yes' : 'No',
                    $property->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import properties from CSV.
     */
    public function importCsv(Request $request)
    {
        $this->authorize('import properties');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->path()));
            $headers = array_shift($csvData); // Remove header row

            $imported = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($csvData as $index => $row) {
                try {
                    $data = array_combine($headers, $row);
                    
                    // Basic validation and mapping
                    Property::create([
                        'title' => $data['Title'] ?? '',
                        'property_type' => $data['Type'] ?? 'house',
                        'listing_type' => strtolower($data['Listing Type'] ?? 'sale'),
                        'price' => (float) ($data['Price'] ?? 0),
                        'city' => $data['City'] ?? '',
                        'state' => $data['State'] ?? '',
                        'bedrooms' => (int) ($data['Bedrooms'] ?? 0),
                        'bathrooms' => (int) ($data['Bathrooms'] ?? 0),
                        'square_feet' => (int) ($data['Square Feet'] ?? 0),
                        'status' => 'pending',
                        'user_id' => auth()->id(),
                        'street_address' => $data['Address'] ?? '',
                        'description' => $data['Description'] ?? '',
                    ]);
                    
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully imported {$imported} properties.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " rows had errors.";
            }

            return back()->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}