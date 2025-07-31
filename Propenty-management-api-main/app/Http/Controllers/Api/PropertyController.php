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
        $this->middleware('auth:sanctum')->except(['index', 'show', 'featured']);
    }

    /**
     * Display a listing of properties with filtering and searching.
     */

     public function test()
    {
        return response()->json([
            'message' => 'Test passed',
        ]);
    }
    
    public function index(Request $request): PropertyCollection
    {
        $properties = QueryBuilder::for(Property::class)
            ->allowedFilters([
                AllowedFilter::exact('property_type'),
                AllowedFilter::exact('listing_type'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('city'),
                AllowedFilter::exact('state'),
                AllowedFilter::callback('price_min', function ($query, $value) {
                    $query->where('price', '>=', $value);
                }),
                AllowedFilter::callback('price_max', function ($query, $value) {
                    $query->where('price', '<=', $value);
                }),
                AllowedFilter::callback('bedrooms', function ($query, $value) {
                    if ($value === '4+') {
                        $query->where('bedrooms', '>=', 4);
                    } else {
                        $query->where('bedrooms', $value);
                    }
                }),
                AllowedFilter::callback('bathrooms', function ($query, $value) {
                    if ($value === '3+') {
                        $query->where('bathrooms', '>=', 3);
                    } else {
                        $query->where('bathrooms', $value);
                    }
                }),
                AllowedFilter::callback('amenities', function ($query, $value) {
                    $amenities = is_array($value) ? $value : [$value];
                    foreach ($amenities as $amenity) {
                        $query->whereJsonContains('amenities', $amenity);
                    }
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                          ->orWhere('description', 'like', "%{$value}%")
                          ->orWhere('street_address', 'like', "%{$value}%")
                          ->orWhere('city', 'like', "%{$value}%")
                          ->orWhere('state', 'like', "%{$value}%")
                          ->orWhere('neighborhood', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::exact('is_featured'),
                AllowedFilter::exact('is_available'),
            ])
            ->allowedSorts([
                'price',
                'created_at',
                'updated_at',
                'views_count',
                'title',
                'bedrooms',
                'bathrooms',
                'square_feet',
            ])
            ->where('status', 'active')
            ->where('is_available', true)
            ->with(['user', 'media'])
            ->paginate($request->get('per_page', 12));

        return new PropertyCollection($properties);
    }

    /**
     * Store a newly created property.
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        try {
            $property = Property::create(array_merge(
                $request->validated(),
                ['user_id' => auth()->id()]
            ));

            // Handle image uploads
            if ($request->hasFile('main_image')) {
                try {
                    $property->addMediaFromRequest('main_image')
                        ->toMediaCollection('main_image');
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Error uploading main image.',
                        'error' => $e->getMessage()
                    ], 422);
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    try {
                        $property->addMedia($image)
                            ->toMediaCollection('images');
                    } catch (\Exception $e) {
                        // Log the error but continue with other images
                        \Log::error("Error uploading property image: " . $e->getMessage());
                        continue;
                    }
                }
            }

            return response()->json([
                'message' => 'Property created successfully.',
                'property' => new PropertyResource($property->load(['user', 'media'])),
            ], 201);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error("Error creating property: " . $e->getMessage());
            
            return response()->json([
                'message' => 'Error creating property.',
                'error' => $e->getMessage()
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

        return response()->json([
            'property' => new PropertyResource($property->load(['user', 'media'])),
        ]);
    }

    /**
     * Update the specified property.
     */
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        // Check if user owns the property
        if ($property->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized. You can only update your own properties.',
            ], 403);
        }

        $property->update($request->validated());

        // Handle image uploads
        if ($request->hasFile('main_image')) {
            $property->clearMediaCollection('main_image');
            $property->addMediaFromRequest('main_image')
                ->toMediaCollection('main_image');
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $property->addMedia($image)
                    ->toMediaCollection('images');
            }
        }

        // Remove images if specified
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

        return response()->json([
            'message' => 'Property updated successfully.',
            'property' => new PropertyResource($property->fresh()->load(['user', 'media'])),
        ]);
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
        $properties = Property::featured()
            ->active()
            ->with(['user', 'media'])
            ->paginate($limit);

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
            ->with(['user', 'media'])
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
