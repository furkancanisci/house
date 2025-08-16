<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertyCollection;
use App\Models\Property;
use App\Models\PropertyView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PropertyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Apply authentication middleware to all methods except public endpoints
        // For production, you may want to require authentication for 'store' as well
        $this->middleware('auth:sanctum')->except(['index', 'show', 'amenities', 'featured', 'testCreate', 'store', 'update']);
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
            'postalCode' => 'postal_code',
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
            'imagesToRemove' => 'remove_images',
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
            'message' => 'Property Management API is running',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
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
                'postal_code' => '12345',
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
        // Log the received filters for debugging
        \Log::info('Received request with filters:', [
            'url' => $request->fullUrl(),
            'query_params' => $request->query(),
            'all_params' => $filters
        ]);
        
        // Debug: Log all request headers
        \Log::info('Request Headers:', [
            'headers' => $request->headers->all(),
            'token' => $request->bearerToken(),
            'has_token' => $request->bearerToken() ? 'yes' : 'no'
        ]);
        
        // Start building the query
        $query = Property::query();
        
        // Apply listing type filter if present - check both camelCase and snake_case
        $listingType = $request->input('listing_type') ?: $request->input('listingType');
        if ($listingType && in_array($listingType, ['rent', 'sale'])) {
            $query->where('listing_type', $listingType);
        }

        // Apply price range filters
        if ($request->has('price_min') && is_numeric($request->price_min)) {
            $query->where('price', '>=', (float)$request->price_min);
        }
        if ($request->has('price_max') && is_numeric($request->price_max)) {
            $query->where('price', '<=', (float)$request->price_max);
        }

        // Apply bedroom filter
        if ($request->has('bedrooms')) {
            if ($request->bedrooms === '4+') {
                $query->where('bedrooms', '>=', 4);
            } elseif (is_numeric($request->bedrooms)) {
                $query->where('bedrooms', '>=', (int)$request->bedrooms);
            }
        }

        // Apply bathroom filter
        if ($request->has('bathrooms')) {
            if ($request->bathrooms === '3+') {
                $query->where('bathrooms', '>=', 3);
            } elseif (is_numeric($request->bathrooms)) {
                $query->where('bathrooms', '>=', (float)$request->bathrooms);
            }
        }

        // Apply property type filter if present - check both camelCase and snake_case
        $propertyType = $request->input('property_type') ?: $request->input('propertyType');
        if ($propertyType && !empty($propertyType)) {
            $query->where('property_type', $propertyType);
        }

        // Apply search query if present - check both 'search' and 'q' parameters
        $searchTerm = $request->input('search', $request->input('q'));
        if (!empty($searchTerm)) {
            \Log::info('Searching for term:', ['term' => $searchTerm, 'all_params' => $request->all()]);
            
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
                  ->orWhereRaw('LOWER(neighborhood) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(landmark) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(amenities) LIKE ?', [$searchTerm]);
            });
            
            // Debug: Log the final SQL query
            \Log::info('Final Search Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'search_term' => $searchTerm
            ]);
        }

        

        // Apply sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        
        if (in_array($sortField, ['price', 'bedrooms', 'bathrooms', 'square_feet', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        // Log the final SQL query and result count
        $resultCount = $query->count();
        \Log::info('Final SQL Query and Result Count:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'result_count' => $resultCount,
            'status_filter' => $query->where('status', 'active')->count()
        ]);
        
        if ($resultCount === 0) {
            \Log::info('No properties found with current filters. Total properties in DB: ' . \App\Models\Property::count());
            \Log::info('Sample property titles: ', 
                \App\Models\Property::select('id', 'title', 'status')
                    ->limit(5)
                    ->get()
                    ->toArray()
            );
        }

        // Ensure only active properties are returned
        $query->where('status', 'active');

        // Select fields needed by PropertyResource
        $query->select([
            'id', 'title', 'description', 'slug', 'property_type', 'listing_type',
            'bedrooms', 'bathrooms', 'square_feet', 'year_built', 'price', 'price_type',
            'street_address', 'city', 'state', 'postal_code', 'country', 'neighborhood',
            'latitude', 'longitude', 'amenities', 'nearby_places', 'status', 'is_featured',
            'is_available', 'available_from', 'published_at', 'views_count', 'contact_name',
            'contact_phone', 'contact_email', 'user_id', 'created_at', 'updated_at'
        ]);

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
            // Get validated data
            $validatedData = $request->validated();
            
            // Map camelCase field names to snake_case database column names
            $mappedData = $this->mapFieldNames($validatedData);
            
            // Add user_id to the data
            $mappedData['user_id'] = auth()->id() ?? 1; // Fallback to user 1 if not authenticated
            
            // Set default status if not provided
            $mappedData['status'] = $mappedData['status'] ?? 'active';
            
            // Create the property
            $property = Property::create($mappedData);
            
            // Handle main image upload
            if ($request->hasFile('main_image')) {
                $mainImage = $request->file('main_image');
                $property->addMedia($mainImage)
                    ->usingName($mainImage->getClientOriginalName())
                    ->usingFileName(time() . '_main_' . $mainImage->getClientOriginalName())
                    ->toMediaCollection('main_image');
            }
            
            // Handle multiple image uploads
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
            
            // Load the property with its relationships
            $property->load(['user', 'media']);
            
            // Return the response
            return response()->json([
                'message' => 'Property created successfully',
                'property' => new PropertyResource($property)
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
        // Record property view
        PropertyView::recordView($property, $request);
        $property->incrementViews();

        // Debug logging
        \Log::info('Property show request', [
            'property_id' => $property->id,
            'property_title' => $property->title,
            'request_path' => $request->path()
        ]);

        return response()->json([
            'property' => new PropertyResource($property->load(['user', 'media', 'favoritedByUsers'])),
        ]);
    }

    /**
     * Update the specified property.
     */
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        try {
            // Debug logging
            \Log::info('Property update request', [
                'property_id' => $property->id,
                'property_title' => $property->title,
                'user_id' => auth()->id(),
                'property_user_id' => $property->user_id,
                'request_data_keys' => array_keys($request->all()),
                'validated_data_keys' => array_keys($request->validated())
            ]);
            
            // Check if user owns the property (skip in development)
            $user = auth()->user();
            if ($user && $property->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only update your own properties.',
                ], 403);
            }

            // Map camelCase field names to snake_case database column names
            $mappedData = $this->mapFieldNames($request->validated());
            
            $property->update($mappedData);

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

            return response()->json([
                'message' => 'Property updated successfully.',
                'property' => new PropertyResource($property->fresh()->load(['user', 'media', 'favoritedByUsers'])),
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
        // Check if user owns the property
        if ($property->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized. You can only delete your own properties.',
            ], 403);
        }

        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully.',
        ]);
    }

    /**
     * Get featured properties.
     */
    public function featured(Request $request): PropertyCollection
    {
        $limit = $request->get('limit', 6);
        $searchTerm = $request->input('search', $request->input('q'));
        
        $query = Property::with(['user', 'media', 'favoritedByUsers'])
            ->where('is_featured', true);
            
        // Apply search filter if search term exists
        if (!empty($searchTerm)) {
            $searchTerm = strtolower(trim($searchTerm));
            $likeTerm = "%$searchTerm%";
            
            $query->where(function($q) use ($likeTerm) {
                $q->whereRaw('LOWER(title) LIKE ?', [$likeTerm])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$likeTerm])
                  ->orWhereRaw('LOWER(property_type) LIKE ?', [$likeTerm])
                  ->orWhereRaw('LOWER(city) LIKE ?', [$likeTerm])
                  ->orWhereRaw('LOWER(state) LIKE ?', [$likeTerm])
                  ->orWhereRaw('LOWER(street_address) LIKE ?', [$likeTerm]);
            });
        }
    
        $properties = $query->paginate($limit);
    
        return new PropertyCollection($properties);
    }
    

    /**
     * Get similar properties.
     */
    public function similar(Property $property, Request $request): PropertyCollection
    {
        $similar = Property::where('id', '!=', $property->id)
            ->where('property_type', $property->property_type)
            ->where('listing_type', $property->listing_type)
            ->where('city', $property->city)
            ->active()
            ->with(['user', 'media', 'favoritedByUsers'])
            ->take($request->get('limit', 4))
            ->get();

        return new PropertyCollection($similar);
    }

    /**
     * Toggle property favorite status.
     */
    public function toggleFavorite(Property $property): JsonResponse
    {
        $user = auth()->user();
        
        if ($user->favoriteProperties()->where('property_id', $property->id)->exists()) {
            $user->favoriteProperties()->detach($property->id);
            $message = 'Property removed from favorites.';
            $is_favorited = false;
        } else {
            $user->favoriteProperties()->attach($property->id);
            $message = 'Property added to favorites.';
            $is_favorited = true;
        }

        return response()->json([
            'message' => $message,
            'is_favorited' => $is_favorited,
        ]);
    }

    /**
     * Delete a specific property image.
     */
    public function deleteImage(Property $property, $mediaId): JsonResponse
    {
        try {
            // Check if user owns the property
            if ($property->user_id !== auth()->id()) {
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
                'property' => new PropertyResource($property->fresh()->load(['user', 'media', 'favoritedByUsers'])),
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
        if ($property->user_id !== auth()->id()) {
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
     * Get available property amenities.
     */
    public function amenities(): JsonResponse
    {
        return response()->json([
            'amenities' => Property::getAvailableAmenities(),
        ]);
    }
}
