<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Neighborhood;
use App\Models\PropertyType;
use App\Models\PriceType;
use App\Models\User;
use App\Models\Amenity;
use App\Models\ViewType;
use App\Models\Direction;
use App\Models\PropertyDocumentType;
use App\Models\BuildingType;
use App\Models\FloorType;
use App\Models\WindowType;
use App\Models\Feature;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\PropertyApprovedMail;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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
            ->allowedSorts(['title', 'price', 'id', 'status'])
            ->defaultSort('-id') // Sort by ID descending (newest first)
            ->paginate(20)
            ->appends($request->query());

        // Get filter options using correct column names
        $cities = City::orderBy('name_en')->pluck('name_en', 'name_en');
        $propertyTypes = PropertyType::orderBy('name')->pluck('name', 'name');
        $users = User::select('id', 'first_name', 'last_name')->get()
            ->mapWithKeys(fn($user) => [$user->id => $user->full_name]);

        $stats = [
            'total' => Property::count(),
            'active' => Property::where('status', 'active')->count(),
            'pending' => Property::where('status', 'pending')->count(),
            'featured' => Property::where('is_featured', true)->count(),
            'new' => Property::where('created_at', '>=', now()->subHours(2))->count(),
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

        $governorates = Governorate::orderBy('name_ar')->get();
        $cities = City::active()->orderBy('name_en')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $users = User::select('id', 'first_name', 'last_name', 'email')->get()
            ->mapWithKeys(fn($user) => [$user->id => $user->full_name . ' (' . $user->email . ')']);

        // States data removed - using governorates relationship instead
        
        // Get all neighborhoods (will be filtered by city via AJAX)
        $neighborhoods = collect();
        
        // Get price types from database
        $priceTypes = PriceType::active()->get();

        // Get view types and directions
        $viewTypes = ViewType::active()->ordered()->get();
        $directions = Direction::active()->ordered()->get();

        // Get additional required data
        $documentTypes = PropertyDocumentType::active()->ordered()->get();
        $buildingTypes = BuildingType::active()->ordered()->get();
        $floorTypes = FloorType::active()->ordered()->get();
        $windowTypes = WindowType::active()->ordered()->get();
        $features = Feature::active()->orderBy('sort_order')->orderBy('name_ar')->get();
        $utilities = Utility::active()->orderBy('sort_order')->orderBy('name_ar')->get();

        return view('admin.properties.create', compact(
            'governorates',
            'cities',
            'neighborhoods',
            'propertyTypes',
            'priceTypes',
            'users',
            'viewTypes',
            'directions',
            'documentTypes',
            'buildingTypes',
            'floorTypes',
            'windowTypes',
            'features',
            'utilities'
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
            'price_type' => 'required|in:negotiable,final_price,popular_saying,price_from_last,monthly,yearly,total,fixed',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'governorate_id' => 'required|integer|exists:governorates,id',
            'city_id' => 'required|integer|exists:cities,id',
            'neighborhood_id' => 'nullable|integer|exists:neighborhoods,id',
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
            'nearby_places' => 'nullable|array',
            'contact_name' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:100',
            'user_id' => 'required|exists:users,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max

            // Advanced fields with camelCase from frontend
            'floorNumber' => 'nullable|integer|min:0|max:200',
            'totalFloors' => 'nullable|integer|min:1|max:200',
            'balconyCount' => 'nullable|integer|min:0|max:20',
            'orientation' => 'nullable|string|max:50',
            'viewType' => 'nullable|string|max:50',
            'buildingAge' => 'nullable|integer|min:0|max:200',
            'buildingType' => 'nullable|string|max:50',
            'floorType' => 'nullable|string|max:50',
            'windowType' => 'nullable|string|max:50',
            'maintenanceFee' => 'nullable|numeric|min:0|max:99999',
            'depositAmount' => 'nullable|numeric|min:0|max:9999999',
            'annualTax' => 'nullable|numeric|min:0|max:999999',
            'buildingTypeId' => 'nullable|integer|exists:building_types,id',
            'windowTypeId' => 'nullable|integer|exists:window_types,id',
            'floorTypeId' => 'nullable|integer|exists:floor_types,id',

            // Admin form fields (snake_case)
            'document_type_id' => 'required|integer|exists:property_document_types,id',
            'pet_policy' => 'nullable|string|max:255',
            'building_age' => 'nullable|integer|min:0|max:200',
            'building_type_id' => 'nullable|integer|exists:building_types,id',
            'floor_type_id' => 'nullable|integer|exists:floor_types,id',
            'window_type_id' => 'nullable|integer|exists:window_types,id',
            'maintenance_fee' => 'nullable|numeric|min:0|max:99999',
            'hoa_fees' => 'nullable|string|max:100',
            'deposit_amount' => 'nullable|numeric|min:0|max:9999999',
            'annual_tax' => 'nullable|numeric|min:0|max:999999',
            'floor_number' => 'nullable|integer|min:0|max:200',
            'total_floors' => 'nullable|integer|min:1|max:200',
            'balcony_count' => 'nullable|integer|min:0|max:20',
            'view_type' => 'nullable|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'integer|exists:features,id',
            'utilities' => 'nullable|array',
            'utilities.*' => 'integer|exists:utilities,id'
        ]);

        // Set price_type based on listing_type
        if ($validated['listing_type'] === 'rent') {
            // For rent listings, allow monthly, yearly, negotiable, final_price, popular_saying, price_from_last
            $allowedRentTypes = ['monthly', 'yearly', 'negotiable', 'final_price', 'popular_saying', 'price_from_last'];
            if (empty($validated['price_type']) || !in_array($validated['price_type'], $allowedRentTypes)) {
                $validated['price_type'] = 'monthly';
            }
        } else {
            // For sale listings, allow total, fixed, negotiable, final_price, popular_saying, price_from_last
            $allowedSaleTypes = ['total', 'fixed', 'negotiable', 'final_price', 'popular_saying', 'price_from_last'];
            if (empty($validated['price_type']) || !in_array($validated['price_type'], $allowedSaleTypes)) {
                $validated['price_type'] = 'total';
            }
        }

        // Convert camelCase field names to snake_case for database
        $fieldMappings = [
            'floorNumber' => 'floor_number',
            'totalFloors' => 'total_floors',
            'balconyCount' => 'balcony_count',
            'viewType' => 'view_type',
            'buildingAge' => 'building_age',
            'buildingType' => 'building_type',
            'floorType' => 'floor_type',
            'windowType' => 'window_type',
            'maintenanceFee' => 'maintenance_fee',
            'depositAmount' => 'deposit_amount',
            'annualTax' => 'annual_tax',
            'buildingTypeId' => 'building_type_id',
            'windowTypeId' => 'window_type_id',
            'floorTypeId' => 'floor_type_id'
        ];

        // Transform camelCase to snake_case
        foreach ($fieldMappings as $camelCase => $snakeCase) {
            if (isset($validated[$camelCase])) {
                $validated[$snakeCase] = $validated[$camelCase];
                unset($validated[$camelCase]);
            }
        }

        // Set types to null if they don't match constraints (to avoid constraint violations)
        // The real values should come from the type IDs
        if (isset($validated['building_type'])) {
            $allowedBuildingTypes = ['new', 'resale', 'under_construction', 'project'];
            if (!in_array($validated['building_type'], $allowedBuildingTypes)) {
                $validated['building_type'] = null;
            }
        }

        if (isset($validated['floor_type'])) {
            $allowedFloorTypes = ['laminate', 'hardwood', 'tile', 'marble', 'carpet', 'vinyl', 'concrete', 'parquet'];
            if (!in_array($validated['floor_type'], $allowedFloorTypes)) {
                $validated['floor_type'] = null;
            }
        }

        if (isset($validated['window_type'])) {
            $allowedWindowTypes = ['aluminum', 'pvc', 'wood', 'steel'];
            if (!in_array($validated['window_type'], $allowedWindowTypes)) {
                $validated['window_type'] = null;
            }
        }

        DB::beginTransaction();
        try {
            $property = Property::create($validated);

            // Sync features and utilities
            if ($request->has('features')) {
                $property->features()->sync($request->features);
            }
            if ($request->has('utilities')) {
                $property->utilities()->sync($request->utilities);
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

        $property->load(['user', 'media', 'views', 'favoritedByUsers']);

        return view('admin.properties.show', compact('property'));
    }

    /**
     * Show the form for editing the property.
     */
    public function edit(Property $property)
    {
        $this->authorize('edit properties');

        // Load governorates, cities, and property types
        $governorates = Governorate::orderBy('name_ar')->get();
        $cities = City::orderBy('name_en')->get();
        $propertyTypes = PropertyType::orderBy('name')->get();
        $users = User::select('id', 'first_name', 'last_name', 'email')->get()
            ->mapWithKeys(fn($user) => [$user->id => $user->full_name . ' (' . $user->email . ')']);

        // States data removed - using governorates relationship instead
        
        // Get neighborhoods for the current city
        $neighborhoods = collect();
        if ($property->city_id) {
            $neighborhoods = Neighborhood::where('city_id', $property->city_id)
                                                    ->orderBy('name_ar')
                                                    ->get();
        } elseif ($property->city) {
            // Fallback for old data using text field
            $city = City::where('name_en', $property->city)
                       ->orWhere('name_ar', $property->city)
                       ->first();
            if ($city) {
                $neighborhoods = $city->neighborhoods;
            }
        }

        // Load relationships including the new ones
        $property->load(['user', 'media', 'city', 'governorate', 'neighborhood', 'propertyType', 'features', 'utilities']);
        
        // Get price types from database
        $priceTypes = PriceType::active()->get();

        // Get view types and directions
        $viewTypes = ViewType::active()->ordered()->get();
        $directions = Direction::active()->ordered()->get();

        // Get additional required data
        $documentTypes = PropertyDocumentType::active()->ordered()->get();
        $buildingTypes = BuildingType::active()->ordered()->get();
        $floorTypes = FloorType::active()->ordered()->get();
        $windowTypes = WindowType::active()->ordered()->get();
        $features = Feature::active()->orderBy('sort_order')->orderBy('name_ar')->get();
        $utilities = Utility::active()->orderBy('sort_order')->orderBy('name_ar')->get();

        return view('admin.properties.edit', compact(
            'property',
            'governorates',
            'cities',
            'neighborhoods',
            'propertyTypes',
            'priceTypes',
            'users',
            'viewTypes',
            'directions',
            'documentTypes',
            'buildingTypes',
            'floorTypes',
            'windowTypes',
            'features',
            'utilities'
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
            'price_type' => 'required|in:monthly,yearly,total,fixed,negotiable,final_price,popular_saying,price_from_last',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'governorate_id' => 'required|integer|exists:governorates,id',
            'city_id' => 'required|integer|exists:cities,id',
            'neighborhood_id' => 'nullable|integer|exists:neighborhoods,id',
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
            'nearby_places' => 'nullable|array',
            'contact_name' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:100',
            'user_id' => 'required|exists:users,id',
            'new_images' => 'nullable|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',

            // Advanced fields with camelCase from frontend
            'floorNumber' => 'nullable|integer|min:0|max:200',
            'totalFloors' => 'nullable|integer|min:1|max:200',
            'balconyCount' => 'nullable|integer|min:0|max:20',
            'orientation' => 'nullable|string|max:50',
            'viewType' => 'nullable|string|max:50',
            'buildingAge' => 'nullable|integer|min:0|max:200',
            'buildingType' => 'nullable|string|max:50',
            'floorType' => 'nullable|string|max:50',
            'windowType' => 'nullable|string|max:50',
            'maintenanceFee' => 'nullable|numeric|min:0|max:99999',
            'depositAmount' => 'nullable|numeric|min:0|max:9999999',
            'annualTax' => 'nullable|numeric|min:0|max:999999',
            'buildingTypeId' => 'nullable|integer|exists:building_types,id',
            'windowTypeId' => 'nullable|integer|exists:window_types,id',
            'floorTypeId' => 'nullable|integer|exists:floor_types,id',

            // Admin form fields (snake_case)
            'document_type_id' => 'required|integer|exists:property_document_types,id',
            'pet_policy' => 'nullable|string|max:255',
            'building_age' => 'nullable|integer|min:0|max:200',
            'building_type_id' => 'nullable|integer|exists:building_types,id',
            'floor_type_id' => 'nullable|integer|exists:floor_types,id',
            'window_type_id' => 'nullable|integer|exists:window_types,id',
            'maintenance_fee' => 'nullable|numeric|min:0|max:99999',
            'hoa_fees' => 'nullable|string|max:100',
            'deposit_amount' => 'nullable|numeric|min:0|max:9999999',
            'annual_tax' => 'nullable|numeric|min:0|max:999999',
            'floor_number' => 'nullable|integer|min:0|max:200',
            'total_floors' => 'nullable|integer|min:1|max:200',
            'balcony_count' => 'nullable|integer|min:0|max:20',
            'view_type' => 'nullable|string|max:50',
            'features' => 'nullable|array',
            'features.*' => 'integer|exists:features,id',
            'utilities' => 'nullable|array',
            'utilities.*' => 'integer|exists:utilities,id'
        ]);

        // Set price_type based on listing_type
        if ($validated['listing_type'] === 'rent') {
            // For rent listings, allow monthly, yearly, negotiable, final_price, popular_saying, price_from_last
            $allowedRentTypes = ['monthly', 'yearly', 'negotiable', 'final_price', 'popular_saying', 'price_from_last'];
            if (!in_array($validated['price_type'], $allowedRentTypes)) {
                $validated['price_type'] = 'monthly';
            }
        } else {
            // For sale listings, allow total, fixed, negotiable, final_price, popular_saying, price_from_last
            $allowedSaleTypes = ['total', 'fixed', 'negotiable', 'final_price', 'popular_saying', 'price_from_last'];
            if (!in_array($validated['price_type'], $allowedSaleTypes)) {
                $validated['price_type'] = 'total';
            }
        }

        // Convert camelCase field names to snake_case for database
        $fieldMappings = [
            'floorNumber' => 'floor_number',
            'totalFloors' => 'total_floors',
            'balconyCount' => 'balcony_count',
            'viewType' => 'view_type',
            'buildingAge' => 'building_age',
            'buildingType' => 'building_type',
            'floorType' => 'floor_type',
            'windowType' => 'window_type',
            'maintenanceFee' => 'maintenance_fee',
            'depositAmount' => 'deposit_amount',
            'annualTax' => 'annual_tax',
            'buildingTypeId' => 'building_type_id',
            'windowTypeId' => 'window_type_id',
            'floorTypeId' => 'floor_type_id'
        ];

        // Transform camelCase to snake_case
        foreach ($fieldMappings as $camelCase => $snakeCase) {
            if (isset($validated[$camelCase])) {
                $validated[$snakeCase] = $validated[$camelCase];
                unset($validated[$camelCase]);
            }
        }

        // Set types to null if they don't match constraints (to avoid constraint violations)
        // The real values should come from the type IDs
        if (isset($validated['building_type'])) {
            $allowedBuildingTypes = ['new', 'resale', 'under_construction', 'project'];
            if (!in_array($validated['building_type'], $allowedBuildingTypes)) {
                $validated['building_type'] = null;
            }
        }

        if (isset($validated['floor_type'])) {
            $allowedFloorTypes = ['laminate', 'hardwood', 'tile', 'marble', 'carpet', 'vinyl', 'concrete', 'parquet'];
            if (!in_array($validated['floor_type'], $allowedFloorTypes)) {
                $validated['floor_type'] = null;
            }
        }

        if (isset($validated['window_type'])) {
            $allowedWindowTypes = ['aluminum', 'pvc', 'wood', 'steel'];
            if (!in_array($validated['window_type'], $allowedWindowTypes)) {
                $validated['window_type'] = null;
            }
        }

        DB::beginTransaction();
        try {
            $property->update($validated);

            // Sync features and utilities
            if ($request->has('features')) {
                $property->features()->sync($request->features);
            } else {
                $property->features()->detach();
            }
            if ($request->has('utilities')) {
                $property->utilities()->sync($request->utilities);
            } else {
                $property->utilities()->detach();
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
                    $propertiesToApprove = $properties->get();
                    $properties->update(['status' => 'active']);

                    // Send approval emails for bulk approval
                    foreach ($propertiesToApprove as $property) {
                        try {
                            if ($property->user && $property->user->email) {
                                Mail::to($property->user->email)->send(new PropertyApprovedMail($property));
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to send bulk approval email', [
                                'property_id' => $property->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
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

        // Send approval email notification
        try {
            if ($property->user && $property->user->email) {
                Mail::to($property->user->email)->send(new PropertyApprovedMail($property));
                Log::info('Property approval email sent', [
                    'property_id' => $property->id,
                    'user_email' => $property->user->email
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send property approval email', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }

        return back()->with('success', 'Property approved successfully and notification sent to owner.');
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
    
    /**
     * Get cities by state via AJAX
     */
    public function getCitiesByState(Request $request)
    {
        $state = $request->get('state');
        $locale = app()->getLocale();
        
        $cities = City::active()
            ->when($state, function($query) use ($state, $locale) {
                if ($locale === 'ar') {
                    return $query->where('state_ar', $state);
                }
                return $query->where('state_en', $state);
            })
            ->orderBy('name_en')
            ->get()
            ->map(function($city) use ($locale) {
                return [
                    'id' => $city->id,
                    'name' => $locale === 'ar' ? $city->name_ar : $city->name_en,
                    'value' => $locale === 'ar' ? $city->name_ar : $city->name_en
                ];
            });
        
        // If no cities found in database for this state, provide fallback data
        if ($cities->isEmpty() && $state) {
            $fallbackCities = $this->getFallbackCitiesForState($state, $locale);
            return response()->json($fallbackCities);
        }
        
        return response()->json($cities);
    }
    
    /**
     * Get fallback cities for a given state
     */
    private function getFallbackCitiesForState($state, $locale = 'en')
    {
        $syrianCities = [
            'Damascus' => [
                ['name' => 'Damascus', 'name_ar' => 'دمشق'],
                ['name' => 'Douma', 'name_ar' => 'دوما'],
                ['name' => 'Daraya', 'name_ar' => 'داريا']
            ],
            'دمشق' => [
                ['name' => 'Damascus', 'name_ar' => 'دمشق'],
                ['name' => 'Douma', 'name_ar' => 'دوما'],
                ['name' => 'Daraya', 'name_ar' => 'داريا']
            ],
            'Aleppo' => [
                ['name' => 'Aleppo', 'name_ar' => 'حلب'],
                ['name' => 'Afrin', 'name_ar' => 'عفرين'],
                ['name' => 'Azaz', 'name_ar' => 'أعزاز']
            ],
            'حلب' => [
                ['name' => 'Aleppo', 'name_ar' => 'حلب'],
                ['name' => 'Afrin', 'name_ar' => 'عفرين'],
                ['name' => 'Azaz', 'name_ar' => 'أعزاز']
            ],
            'Homs' => [
                ['name' => 'Homs', 'name_ar' => 'حمص'],
                ['name' => 'Palmyra', 'name_ar' => 'تدمر']
            ],
            'حمص' => [
                ['name' => 'Homs', 'name_ar' => 'حمص'],
                ['name' => 'Palmyra', 'name_ar' => 'تدمر']
            ],
            'Latakia' => [
                ['name' => 'Latakia', 'name_ar' => 'اللاذقية'],
                ['name' => 'Jableh', 'name_ar' => 'جبلة']
            ],
            'اللاذقية' => [
                ['name' => 'Latakia', 'name_ar' => 'اللاذقية'],
                ['name' => 'Jableh', 'name_ar' => 'جبلة']
            ]
        ];
        
        $cities = $syrianCities[$state] ?? [];
        
        return collect($cities)->map(function($city, $index) use ($locale) {
            return [
                'id' => 'fallback_' . $index,
                'name' => $locale === 'ar' ? $city['name_ar'] : $city['name'],
                'value' => $locale === 'ar' ? $city['name_ar'] : $city['name']
            ];
        })->toArray();
    }
    
    /**
     * Get neighborhoods by city via AJAX
     */
    public function getNeighborhoodsByCity(Request $request)
    {
        $cityName = $request->get('city');
        
        // Find city by name
        $city = City::active()
            ->where(function($query) use ($cityName) {
                $query->where('name_ar', $cityName)
                      ->orWhere('name_en', $cityName);
            })
            ->first();
            
        if (!$city) {
            return response()->json([]);
        }
        
        $neighborhoods = $city->neighborhoods()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(function($neighborhood) {
                return [
                    'id' => $neighborhood->id,
                    'name' => $neighborhood->name,
                    'value' => $neighborhood->name
                ];
            });
        
        return response()->json($neighborhoods);
    }
}