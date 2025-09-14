<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertyCollection;
use App\Models\Property;
use App\Models\PropertyView;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PropertyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'featured', 'priceTypes']);
    }

    /**
     * Map camelCase field names to snake_case database column names
     */
    private function mapFieldNames(array $data): array
    {
        $fieldMapping = [
            'propertyType' => 'property_type',
            'listingType' => 'listing_type',
            'address' => 'street_address',
            'squareFootage' => 'square_feet',
            'lotSize' => 'lot_size',
            'yearBuilt' => 'year_built',
            'parking' => 'parking_type',
            'isFeatured' => 'is_featured',
            'isAvailable' => 'is_available',
            'availableDate' => 'available_from',
            'contactName' => 'contact_name',
            'contactPhone' => 'contact_phone',
            'contactEmail' => 'contact_email',
            'documentTypeId' => 'document_type_id',
            'document_type_id' => 'document_type_id', // Also accept snake_case
            'imagesToRemove' => 'remove_images',
            'mainImage' => 'main_image',
            'main_image' => 'main_image', // Also accept snake_case
        ];

        $mappedData = [];
        
        foreach ($data as $key => $value) {
            // If there's a mapping for this field, use the mapped name
            if (isset($fieldMapping[$key])) {
                $mappedData[$fieldMapping[$key]] = $value;
            } else {
                // Otherwise, keep the original field name
                $mappedData[$key] = $value;
            }
        }

        return $mappedData;
    }
    
    /**
     * Test endpoint
     */
    public function test()
    {
        return response()->json([
            'status' => 'OK',
        ]);
    }


    /**
     * Simple test property creation without validation
     */
    public function testCreate()
    {
        try {
            $property = Property::create([
                'title' => 'Test Property Eloquent',
                'description' => 'A test property using Eloquent model',
                'property_type' => 'apartment',
                'listing_type' => 'rent',
                'price' => 1500,
                'street_address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'TS',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'contact_name' => 'John Doe',
                'contact_phone' => '555-1234',
                'contact_email' => 'john@example.com',
                'user_id' => 1, // Now we have a valid user
                'status' => 'active',
                'is_available' => true
            ]);

            return response()->json([
                'message' => 'Test property created successfully using Eloquent.',
                'property_id' => $property->id,
                'property_slug' => $property->slug,
                'method' => 'eloquent_model'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating test property.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10)
            ], 500);
        }
    }

    /**
     * Display a listing of properties with filtering and searching.
     */
    public function index(Request $request): PropertyCollection
    {
        // Get the filters from the request
        $filters = $request->all();
        
        // Ensure proper UTF-8 encoding for all string inputs
        array_walk_recursive($filters, function(&$value) {
            if (is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            }
        });
        
        // Log the received filters for debugging
        \Illuminate\Support\Facades\Log::info('Received request with filters:', [
            'url' => $request->fullUrl(),
            'query_params' => $request->query(),
            'all_params' => $filters
        ]);
        
        // Start building the query
        $query = Property::query();
        
        // Ensure only active and available properties are returned
        $query->active();
        
        // Apply listing type filter - support both camelCase and snake_case
        $listingType = $request->input('listing_type') ?: $request->input('listingType');
        if ($listingType && in_array($listingType, ['rent', 'sale'])) {
            $query->forListing($listingType);
        }

        // Apply property type filter - support both camelCase and snake_case
        $propertyType = $request->input('property_type') ?: $request->input('propertyType');
        if ($propertyType && !empty($propertyType) && $propertyType !== 'any') {
            // Find property type by name or slug
            $type = \App\Models\PropertyType::where('name', $propertyType)
                ->orWhere('slug', $propertyType)
                ->first();
            if ($type) {
                $query->where('property_type_id', $type->id);
            }
        }

        // Apply price range filters - support both min/max and minPrice/maxPrice
        $minPrice = $request->input('price_min') ?: $request->input('minPrice');
        $maxPrice = $request->input('price_max') ?: $request->input('maxPrice');
        
        if ($minPrice && is_numeric($minPrice) && $minPrice > 0) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice && is_numeric($maxPrice) && $maxPrice > 0) {
            $query->where('price', '<=', (float)$maxPrice);
        }

        // Apply square footage filters
        $minSquareFootage = $request->input('min_square_feet') ?: $request->input('minSquareFootage');
        $maxSquareFootage = $request->input('max_square_feet') ?: $request->input('maxSquareFootage');
        
        if ($minSquareFootage && is_numeric($minSquareFootage) && $minSquareFootage > 0) {
            $query->where('square_feet', '>=', (int)$minSquareFootage);
        }
        if ($maxSquareFootage && is_numeric($maxSquareFootage) && $maxSquareFootage > 0) {
            $query->where('square_feet', '<=', (int)$maxSquareFootage);
        }

        // Apply location filters with Arabic/English support
        $location = $request->input('location');
        if ($location && !empty($location)) {
            $query = LocationService::buildLocationQuery($query, $location);
        }

        // Apply features filter
        if ($request->has('features') && !empty($request->features)) {
            $features = is_array($request->features) ? $request->features : [$request->features];
            $query->whereHas('features', function ($q) use ($features) {
                $q->whereIn('features.id', $features)->where('features.is_active', true);
            });
        }
        
        // Apply utilities filter
        if ($request->has('utilities') && !empty($request->utilities)) {
            $utilities = is_array($request->utilities) ? $request->utilities : [$request->utilities];
            $query->whereHas('utilities', function ($q) use ($utilities) {
                $q->whereIn('utilities.id', $utilities)->where('utilities.is_active', true);
            });
        }

        // Apply search query - check both 'search', 'q', and 'searchQuery' parameters
        $searchTerm = $request->input('search', $request->input('q', $request->input('searchQuery')));
        if (!empty($searchTerm)) {
            \Illuminate\Support\Facades\Log::info('Searching for term:', ['term' => $searchTerm]);
            
            // Make search case-insensitive and trim whitespace
            $searchTerm = strtolower(trim($searchTerm));
            $searchTerm = "%$searchTerm%";
            
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(title) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(property_type) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(city) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(state) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(street_address) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(neighborhood) LIKE ?', [$searchTerm]);
            });
        }

        // Apply sorting - support both sortBy/sortOrder and sort/direction
        $sortField = $request->input('sort', $request->input('sortBy', 'created_at'));
        $sortDirection = $request->input('direction', $request->input('sortOrder', 'desc'));
        
        // Map frontend sort fields to database columns
        $sortFieldMap = [
            'price' => 'price',
            'bedrooms' => 'bedrooms',
            'bathrooms' => 'bathrooms',
            'squareFootage' => 'square_feet',
            'square_feet' => 'square_feet',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'newest' => 'created_at',
            'oldest' => 'created_at'
        ];
        
        $dbSortField = $sortFieldMap[$sortField] ?? 'created_at';
        
        // Handle special sort cases
        if ($sortField === 'oldest') {
            $sortDirection = 'asc';
        } elseif ($sortField === 'newest') {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($dbSortField, $sortDirection === 'asc' ? 'asc' : 'desc');

        // Log the search query for debugging
        if (!empty($searchTerm)) {
            \Illuminate\Support\Facades\Log::info('Search query details:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'search_term' => $searchTerm
            ]);
        }

        $query->select([
            'id', 'title', 'description', 'slug', 'property_type', 'listing_type',
            'bedrooms', 'bathrooms', 'square_feet', 'year_built', 'price', 'price_type',
            'street_address', 'city', 'state', 'neighborhood',
            'latitude', 'longitude', 'nearby_places', 'status', 'is_featured',
            'is_available', 'available_from', 'published_at', 'views_count',
            'user_id', 'document_type_id', 'created_at', 'updated_at'
        ]);

        // Load relationships
        $query->with(['documentType', 'features', 'utilities', 'priceType']);

        // Paginate the results
        $perPage = $request->input('per_page', 12);
        $properties = $query->paginate($perPage);
        return new PropertyCollection($properties);
    }

    /**
     * Store a newly created property.
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        try {
            // Log raw request for debugging
            \Illuminate\Support\Facades\Log::info('PropertyController::store - Raw Request', [
                'all_input' => $request->all(),
                'files' => $request->allFiles() ? array_keys($request->allFiles()) : [],
                'has_title' => $request->has('title'),
                'title_value' => $request->input('title')
            ]);
            
            // Get validated data
            $validatedData = $request->validated();
            
            // Map camelCase field names to snake_case database column names
            $mappedData = $this->mapFieldNames($validatedData);
            
            // Get the authenticated user
            $user = request()->user();
            
            // Check if user is authenticated
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            
            // Add user_id to the data
            $mappedData['user_id'] = $user->id;
            
            // Set default status if not provided
            $mappedData['status'] = $mappedData['status'] ?? 'active';
            
            // Ensure required fields have proper defaults and validation
            $mappedData['published_at'] = $mappedData['published_at'] ?? now();
            $mappedData['views_count'] = 0;
            $mappedData['rating'] = null;
            $mappedData['reviews_count'] = 0;
            
            // Ensure required fields are present
            if (empty($mappedData['city'])) {
                $mappedData['city'] = 'Unknown City';
            }
            if (empty($mappedData['state'])) {
                $mappedData['state'] = 'Unknown State';
            }
            if (empty($mappedData['bedrooms'])) {
                $mappedData['bedrooms'] = 0;
            }
            if (empty($mappedData['bathrooms'])) {
                $mappedData['bathrooms'] = 0;
            }
            
            // Clean up any null or problematic values that might cause PostgreSQL issues
            foreach ($mappedData as $key => $value) {
                if ($value === '' && in_array($key, ['latitude', 'longitude', 'lot_size', 'year_built', 'parking_spaces'])) {
                    $mappedData[$key] = null;
                }
            }
            
            // Remove image fields from the database insertion as they should not be stored in the properties table
            unset($mappedData['main_image']);
            unset($mappedData['images']);
            unset($mappedData['base64_images']);
            

            // Convert nearby_places array to JSON for PostgreSQL
            if (isset($mappedData['nearby_places']) && is_array($mappedData['nearby_places'])) {
                $mappedData['nearby_places'] = json_encode($mappedData['nearby_places']);
            } elseif (!isset($mappedData['nearby_places'])) {
                $mappedData['nearby_places'] = json_encode([]);
            }
            
            // Extract features and utilities before creating property
            $features = $mappedData['features'] ?? [];
            $utilities = $mappedData['utilities'] ?? [];
            unset($mappedData['features'], $mappedData['utilities']);
            
            // Log the data being inserted for debugging
            \Illuminate\Support\Facades\Log::info('Creating property with data', [
                'mapped_data_keys' => array_keys($mappedData),
                'user_id' => $mappedData['user_id'],
                'title' => $mappedData['title'] ?? 'N/A',
                'has_main_image' => $request->hasFile('main_image') || $request->hasFile('mainImage'),
                'has_images' => $request->hasFile('images'),
                'has_base64_images' => $request->has('base64_images'),
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type')
            ]);
            
            // Create the property using a PostgreSQL-safe approach
            try {
                // Generate a slug if not present
                if (!isset($mappedData['slug'])) {
                    $baseSlug = \Illuminate\Support\Str::slug($mappedData['title']);
                    $slug = $baseSlug;
                    $counter = 1;
                    
                    while (DB::table('properties')->where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                    $mappedData['slug'] = $slug;
                }
                
                // Add timestamps
                $mappedData['created_at'] = now();
                $mappedData['updated_at'] = now();
                
                // Insert directly using DB facade to avoid the insertGetId issue
                $propertyId = DB::table('properties')->insertGetId($mappedData);
                
                if (!$propertyId) {
                    throw new \Exception('Failed to insert property into database');
                }
                
                // Get the created property
                $property = Property::find($propertyId);
                
                if (!$property) {
                    throw new \Exception('Property inserted but could not be retrieved');
                }
                
            } catch (\Exception $createException) {
                // Check if this is an array to string conversion error
                if (strpos($createException->getMessage(), 'Array to string conversion') !== false) {
                    \Illuminate\Support\Facades\Log::error('Array to string conversion issue detected in property creation process', [
                        'error' => $createException->getMessage(),
                        'data_types' => array_map('gettype', $mappedData),
                        'array_fields' => array_keys(array_filter($mappedData, 'is_array'))
                    ]);
                    
                    throw new \Exception('Array to string conversion issue detected in property creation process');
                }
                
                \Illuminate\Support\Facades\Log::error('Property creation failed completely', [
                    'error' => $createException->getMessage(),
                    'trace' => $createException->getTraceAsString(),
                    'data_keys' => array_keys($mappedData),
                    'data_sample' => array_slice($mappedData, 0, 5)
                ]);
                
                throw new \Exception('Failed to create property: ' . $createException->getMessage());
            }
            
            // Handle main image upload (support both main_image and mainImage)
            $mainImageFile = $request->file('main_image') ?? $request->file('mainImage');
            if ($mainImageFile) {
                try {
                    \Illuminate\Support\Facades\Log::info('Attempting to upload main image', [
                        'filename' => $mainImageFile->getClientOriginalName(),
                        'size' => $mainImageFile->getSize(),
                        'mime' => $mainImageFile->getMimeType()
                    ]);
                    
                    $media = $property->addMedia($mainImageFile)
                        ->usingName($mainImageFile->getClientOriginalName())
                        ->usingFileName(time() . '_main_' . $mainImageFile->getClientOriginalName())
                        ->toMediaCollection('main_image');
                    
                    \Illuminate\Support\Facades\Log::info('Main image uploaded successfully', [
                        'media_id' => $media->id,
                        'url' => $media->getUrl(),
                        'collection' => 'main_image'
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to upload main image', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('No main image file found in request', [
                    'has_main_image' => $request->hasFile('main_image'),
                    'has_mainImage' => $request->hasFile('mainImage'),
                    'all_files' => array_keys($request->allFiles())
                ]);
            }
            
            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                try {
                    $images = $request->file('images');
                    $uploadedCount = 0;
                    
                    if (is_array($images)) {
                        foreach ($images as $index => $image) {
                            $media = $property->addMedia($image)
                                ->usingName($image->getClientOriginalName())
                                ->usingFileName(time() . '_' . $index . '_' . $image->getClientOriginalName())
                                ->toMediaCollection('images');
                            $uploadedCount++;
                            
                            \Illuminate\Support\Facades\Log::info('Gallery image uploaded', [
                                'index' => $index,
                                'media_id' => $media->id,
                                'url' => $media->getUrl()
                            ]);
                        }
                    } else {
                        // Single image file
                        $media = $property->addMedia($images)
                            ->usingName($images->getClientOriginalName())
                            ->usingFileName(time() . '_single_' . $images->getClientOriginalName())
                            ->toMediaCollection('images');
                        $uploadedCount = 1;
                        
                        \Illuminate\Support\Facades\Log::info('Single gallery image uploaded', [
                            'media_id' => $media->id,
                            'url' => $media->getUrl()
                        ]);
                    }
                    
                    \Illuminate\Support\Facades\Log::info('Gallery images uploaded successfully', [
                        'count' => $uploadedCount
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to upload gallery images', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::info('No gallery images in request');
            }
            
            // Handle base64 image uploads if present
            if ($request->has('base64_images')) {
                foreach ($request->base64_images as $index => $base64Image) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                        $data = substr($base64Image, strpos($base64Image, ',') + 1);
                        $data = base64_decode($data);
                        $extension = strtolower($type[1]);
                        
                        $fileName = time() . '_base64_' . $index . '.' . $extension;
                        $tempPath = storage_path('app/temp/' . $fileName);
                        
                        // Ensure temp directory exists
                        if (!file_exists(dirname($tempPath))) {
                            mkdir(dirname($tempPath), 0755, true);
                        }
                        
                        file_put_contents($tempPath, $data);
                        
                        $property->addMedia($tempPath)
                            ->usingName('Property Image ' . ($index + 1))
                            ->usingFileName($fileName)
                            ->toMediaCollection('images');
                            
                        // Clean up temp file
                        unlink($tempPath);
                    }
                }
            }
            
            // Load the property with its relationships
            $property->load(['user', 'media', 'documentType', 'features', 'utilities']);
            
            // Sync features and utilities relationships
            if (!empty($features)) {
                $property->features()->sync($features);
            }
            if (!empty($utilities)) {
                $property->utilities()->sync($utilities);
            }
            
            // Return the response
            return response()->json([
                'message' => 'Property created successfully',
                'property' => new PropertyResource($property->fresh()->load(['user', 'media', 'documentType', 'features', 'utilities']))
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating property',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Display the specified property.
     */
    public function show(Request $request, Property $property): JsonResponse
    {
        // Check if property is active and available for public viewing
        if ($property->status !== 'active' || !$property->is_available) {
            return response()->json([
                'message' => 'Property not found or not available.',
            ], 404);
        }

        try {
            // Record property view - but don't let it fail the entire request
            PropertyView::recordView($property, $request);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to record property view', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }
        
        try {
            $property->incrementViews();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to increment property views', [
                'property_id' => $property->id,
                'error' => $e->getMessage()
            ]);
        }

        // Debug logging
        \Illuminate\Support\Facades\Log::info('Property show request', [
            'property_id' => $property->id,
            'property_title' => $property->title,
            'request_path' => $request->path()
        ]);

        // Load relationships for the property detail view
        $property->load(['user', 'media', 'favoritedByUsers', 'documentType', 'features', 'utilities']);

        return response()->json([
            'property' => new PropertyResource($property),
        ]);
    }

    /**
     * Update the specified property.
     */
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = request()->user();
            
            // Debug logging
            \Illuminate\Support\Facades\Log::info('Property update request', [
                'property_id' => $property->id,
                'property_title' => $property->title,
                'user_id' => $user ? $user->id : null,
                'property_user_id' => $property->user_id,
                'request_data_keys' => array_keys($request->all()),
                'validated_data_keys' => array_keys($request->validated())
            ]);
            
            // Check if user owns the property (skip in development)
            if ($user && $property->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only update your own properties.',
                ], 403);
            }

            // Map camelCase field names to snake_case database column names
            $mappedData = $this->mapFieldNames($request->validated());
            
            // Extract features and utilities before updating property
            $features = $mappedData['features'] ?? null;
            $utilities = $mappedData['utilities'] ?? null;
            unset($mappedData['features'], $mappedData['utilities']);
            
            $property->update($mappedData);
            
            // Sync features and utilities relationships if provided
            if ($features !== null) {
                $property->features()->sync($features);
            }
            if ($utilities !== null) {
                $property->utilities()->sync($utilities);
            }

            // Handle main image upload
            if ($request->hasFile('main_image')) {
                $property->clearMediaCollection('main_image');
                $mainImage = $request->file('main_image');
                $property->addMedia($mainImage)
                    ->usingName($mainImage->getClientOriginalName())
                    ->usingFileName(time() . '_main_' . $mainImage->getClientOriginalName())
                    ->toMediaCollection('main_image');
            }

            // Remove specific images if specified
            if ($request->has('remove_images')) {
                $removeImages = is_array($request->remove_images) 
                    ? $request->remove_images 
                    : [$request->remove_images];
                
                foreach ($removeImages as $mediaId) {
                    $media = $property->media()->find($mediaId);
                    if ($media) {
                        $media->delete();
                    }
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $property->addMedia($image)
                        ->usingName($image->getClientOriginalName())
                        ->usingFileName(time() . '_' . $index . '_' . $image->getClientOriginalName())
                        ->toMediaCollection('images');
                }
            }

            // Handle base64 image uploads if present
            if ($request->has('base64_images')) {
                foreach ($request->base64_images as $index => $base64Image) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                        $data = substr($base64Image, strpos($base64Image, ',') + 1);
                        $data = base64_decode($data);
                        $extension = strtolower($type[1]);
                        
                        $fileName = time() . '_base64_' . $index . '.' . $extension;
                        $tempPath = storage_path('app/temp/' . $fileName);
                        
                        // Ensure temp directory exists
                        if (!file_exists(dirname($tempPath))) {
                            mkdir(dirname($tempPath), 0755, true);
                        }
                        
                        file_put_contents($tempPath, $data);
                        
                        $property->addMedia($tempPath)
                            ->usingName('Property Image ' . ($index + 1))
                            ->usingFileName($fileName)
                            ->toMediaCollection('images');
                            
                        // Clean up temp file
                        unlink($tempPath);
                    }
                }
            }

            // Load relationships for the updated property
            $property->load(['user', 'media', 'favoritedByUsers', 'documentType', 'features', 'utilities']);

            return response()->json([
                'message' => 'Property updated successfully.',
                'property' => new PropertyResource($property),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating property',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Property $property): JsonResponse
    {
        try {
            // Check if user owns the property
            $user = request()->user();
            if ($user && $property->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only delete your own properties.',
                ], 403);
            }

            // Delete all media associated with the property
            $property->clearMediaCollection('images');
            $property->clearMediaCollection('main_image');

            // Delete the property
            $property->delete();

            return response()->json([
                'message' => 'Property deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting property',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get featured properties.
     */
    public function featured(Request $request): PropertyCollection
    {
        try {
            $limit = $request->input('limit', 6);
            
            $properties = Property::where('is_featured', true)
                ->where('status', 'active')
                ->where('is_available', true)
                ->orderBy('created_at', 'desc')
                ->paginate($limit);

            // Log the properties for debugging
            \Log::info('Featured properties query result', [
                'count' => $properties->count(),
                'items' => $properties->items()
            ]);

            return new PropertyCollection($properties);
        } catch (\Exception $e) {
            \Log::error('Error fetching featured properties: ' . $e->getMessage());
            // Return empty paginated collection on error
            return new PropertyCollection(Property::where('id', -1)->paginate(0));
        }
    }

    /**
     * Get similar properties.
     */
    public function similar(Property $property, Request $request): PropertyCollection
    {
        $similar = Property::where('id', '!=', $property->id)
            ->where('property_type_id', $property->property_type_id)
            ->where('city_id', $property->city_id)
            ->active()
            ->with(['user', 'media', 'favoritedByUsers', 'features', 'utilities'])
            ->take($request->get('limit', 4))
            ->get();

        return new PropertyCollection($similar);
    }

    /**
     * Toggle property favorite status.
     */

     
    public function toggleFavorite(Property $property): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = request()->user();
            
       
            
            // Check if user is authenticated
            if (!$user) {
                // Try alternative method to get user
                $user = auth()->user();
          
                if (!$user) {
                    return response()->json([
                        'message' => 'Unauthenticated.',
                        'debug' => [
                            'auth_header' => request()->header('Authorization'),
                            'session_id' => session()->getId()
                        ]
                    ], 401);
                }
            }
            
            // Check if property exists
            if (!$property) {
                return response()->json([
                    'message' => 'Property not found.',
                ], 404);
            }
            
            // Check if the property is already favorited by the user
            // SQL equivalent: SELECT COUNT(*) > 0 AS is_already_favorited FROM property_favorites WHERE user_id = ? AND property_id = ?;
            $isAlreadyFavorited = $user->favoriteProperties()->where('property_id', $property->id)->exists();
            
            if ($isAlreadyFavorited) {
                // SQL equivalent: DELETE FROM property_favorites WHERE user_id = ? AND property_id = ?;
                $user->favoriteProperties()->detach($property->id);
                $message = 'Property removed from favorites.';
                $is_favorited = false;
            } else {
                // SQL equivalent: INSERT INTO property_favorites (user_id, property_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW());
                $user->favoriteProperties()->attach($property->id);
                $message = 'Property added to favorites.';
                $is_favorited = true;
            }

            return response()->json([
                'message' => $message,
                'is_favorited' => $is_favorited,
            ]);
        } catch (\Exception $e) {
           
            
            return response()->json([
                'message' => 'An error occurred while toggling favorite status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific property image.
     */
    public function deleteImage(Property $property, $mediaId): JsonResponse
    {
        try {
            // Check if user owns the property
            $user = request()->user();
            if (!$user || $property->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only delete images from your own properties.',
                ], 403);
            }

            // Find the media item
            $media = $property->media()->find($mediaId);
            
            if (!$media) {
                return response()->json([
                    'message' => 'Image not found.',
                ], 404);
            }

            // Delete the media item
            $media->delete();

            return response()->json([
                'message' => 'Image deleted successfully.',
                'property' => new PropertyResource($property->fresh()->load(['user', 'media', 'favoritedByUsers', 'features', 'utilities'])),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get property analytics data.
     */
    public function analytics(Property $property): JsonResponse
    {
        // Check if user owns the property
        $user = request()->user();
        if (!$user || $property->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized. You can only view analytics for your own properties.',
            ], 403);
        }

        $views = $property->views()
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->where('viewed_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalViews = $property->views_count;
        $uniqueViews = $property->views()->distinct(['ip_address', 'user_id'])->count();
        $favoritesCount = $property->favoritedByUsers()->count();

        return response()->json([
            'analytics' => [
                'total_views' => $totalViews,
                'unique_views' => $uniqueViews,
                'favorites_count' => $favoritesCount,
                'views_chart_data' => $views,
                'conversion_rate' => $totalViews > 0 ? round(($favoritesCount / $totalViews) * 100, 2) : 0,
            ],
        ]);
    }

    /**
     * Get available amenities for properties.
     */
    public function amenities(Request $request): JsonResponse
    {
        try {
            $features = \App\Models\Feature::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
            
            $utilities = \App\Models\Utility::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
            
            return response()->json([
                'features' => $features,
                'utilities' => $utilities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching amenities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available price types for properties.
     */
    public function priceTypes(Request $request): JsonResponse
    {
        try {
            $listingType = $request->query('listing_type');
            
            $query = \App\Models\PriceType::where('is_active', true);
            
            if ($listingType && in_array($listingType, ['rent', 'sale'])) {
                $query->where('listing_type', $listingType);
            }
            
            $priceTypes = $query->orderBy('id')->get();
            
            return response()->json([
                'success' => true,
                'data' => $priceTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching price types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}