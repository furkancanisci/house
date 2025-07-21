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

/**
 * @group Properties
 *
 * APIs for managing properties
 */
class DocumentedPropertyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('verified')->only(['store', 'update', 'destroy']);
    }

    /**
     * List properties
     *
     * Get a paginated list of properties with filtering options.
     *
     * @queryParam property_type string Filter by property type (apartment, house, condo, etc). Example: apartment
     * @queryParam listing_type string Filter by listing type (rent, sale). Example: rent
     * @queryParam city string Filter by city. Example: New York
     * @queryParam state string Filter by state. Example: NY
     * @queryParam price_min number Filter by minimum price. Example: 1000
     * @queryParam price_max number Filter by maximum price. Example: 5000
     * @queryParam bedrooms string Filter by number of bedrooms (use '4+' for 4 or more). Example: 2
     * @queryParam bathrooms string Filter by number of bathrooms (use '3+' for 3 or more). Example: 2
     * @queryParam amenities string|array Filter by amenities. Example: ["Air Conditioning","Pool"]
     * @queryParam search string Search term to match against title, description, or address. Example: downtown
     * @queryParam sort string Field to sort by (price, created_at, etc). Example: price
     * @queryParam per_page integer Number of results per page. Example: 12
     *
     * @apiResource App\Http\Resources\PropertyCollection
     * @apiResourceModel App\Models\Property with=user,media paginate=12
     *
     * @param Request $request
     * @return PropertyCollection
     */
    public function index(Request $request): PropertyCollection
    {
        // Implementation remains the same as PropertyController
    }

    /**
     * Create a new property
     *
     * Store a newly created property in the database. Only available to verified property owners.
     *
     * @authenticated
     *
     * @bodyParam title string required The title of the property. Example: Modern Downtown Apartment
     * @bodyParam description string required The detailed description of the property. Example: Stunning modern apartment in the heart of downtown with floor-to-ceiling windows and amazing city views.
     * @bodyParam property_type string required The type of property (apartment, house, condo, etc). Example: apartment
     * @bodyParam listing_type string required The listing type (rent, sale). Example: rent
     * @bodyParam price number required The price of the property. Example: 2500
     * @bodyParam price_type string The price type (monthly, yearly, total). Default is based on listing_type. Example: monthly
     * @bodyParam street_address string required The street address of the property. Example: 123 Main Street, Unit 15A
     * @bodyParam city string required The city where the property is located. Example: New York
     * @bodyParam state string required The state where the property is located. Example: NY
     * @bodyParam postal_code string required The postal code of the property. Example: 10001
     * @bodyParam country string The country where the property is located. Default: US. Example: US
     * @bodyParam latitude number The latitude coordinate of the property. Example: 40.7506
     * @bodyParam longitude number The longitude coordinate of the property. Example: -73.9756
     * @bodyParam neighborhood string The neighborhood where the property is located. Example: Midtown
     * @bodyParam bedrooms integer required The number of bedrooms. Example: 2
     * @bodyParam bathrooms integer required The number of bathrooms. Example: 2
     * @bodyParam square_feet integer The square footage of the property. Example: 1200
     * @bodyParam lot_size integer The lot size (for houses). Example: 5000
     * @bodyParam year_built integer The year the property was built. Example: 2020
     * @bodyParam parking_type string The type of parking available. Example: garage
     * @bodyParam parking_spaces integer The number of parking spaces. Example: 1
     * @bodyParam status string The status of the listing. Default: draft. Example: draft
     * @bodyParam is_featured boolean Whether the property is featured. Example: false
     * @bodyParam is_available boolean Whether the property is available. Example: true
     * @bodyParam available_from date The date the property becomes available. Example: 2023-07-01
     * @bodyParam amenities array An array of amenities available at the property. Example: ["Air Conditioning", "Dishwasher", "Laundry in Unit"]
     * @bodyParam main_image file The main image of the property (JPEG, PNG, WebP max 5MB).
     * @bodyParam images array An array of additional property images (max 20, JPEG, PNG, WebP max 5MB each).
     *
     * @response 201 {
     *   "message": "Property created successfully.",
     *   "property": {
     *     "id": 1,
     *     "title": "Modern Downtown Apartment",
     *     "description": "Stunning modern apartment in the heart of downtown with floor-to-ceiling windows and amazing city views.",
     *     "slug": "modern-downtown-apartment",
     *     "property_type": "apartment",
     *     "listing_type": "rent",
     *     "price": {
     *       "amount": 2500,
     *       "formatted": "$2,500/month",
     *       "type": "monthly",
     *       "currency": "USD"
     *     },
     *     "location": {
     *       "street_address": "123 Main Street, Unit 15A",
     *       "city": "New York",
     *       "state": "NY",
     *       "postal_code": "10001",
     *       "country": "US",
     *       "full_address": "123 Main Street, Unit 15A, New York, NY 10001",
     *       "neighborhood": "Midtown",
     *       "coordinates": {
     *         "latitude": 40.7506,
     *         "longitude": -73.9756
     *       }
     *     },
     *     "details": {
     *       "bedrooms": 2,
     *       "bathrooms": 2,
     *       "square_feet": 1200,
     *       "lot_size": null,
     *       "year_built": 2020,
     *       "parking": {
     *         "type": "garage",
     *         "spaces": 1
     *       }
     *     },
     *     "amenities": ["Air Conditioning", "Dishwasher", "Laundry in Unit"],
     *     "status": "draft",
     *     "is_featured": false,
     *     "is_available": true,
     *     "available_from": "2023-07-01",
     *     "images": {
     *       "main": "http://localhost:8000/storage/media/1/main_image.jpg",
     *       "gallery": [
     *         {
     *           "id": 2,
     *           "url": "http://localhost:8000/storage/media/2/image1.jpg",
     *           "thumb": "http://localhost:8000/storage/media/2/conversions/image1-thumb.jpg",
     *           "medium": "http://localhost:8000/storage/media/2/conversions/image1-medium.jpg",
     *           "large": "http://localhost:8000/storage/media/2/conversions/image1-large.jpg"
     *         }
     *       ],
     *       "count": 1
     *     },
     *     "owner": {
     *       "id": 1,
     *       "first_name": "John",
     *       "last_name": "Smith",
     *       "full_name": "John Smith",
     *       "email": "john.smith@example.com"
     *     },
     *     "created_at": "2023-06-15T10:30:00.000000Z",
     *     "updated_at": "2023-06-15T10:30:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "title": ["The title field is required."],
     *     "description": ["The description field is required."],
     *     "property_type": ["The selected property type is invalid."],
     *     "price": ["The price must be a positive number."]
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 403 {
     *   "message": "This action is only available to property owners."
     * }
     *
     * @param StorePropertyRequest $request
     * @return JsonResponse
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        // Implementation remains the same as PropertyController
    }

    /**
     * Get property details
     *
     * Retrieve detailed information about a specific property.
     *
     * @urlParam property string required The slug of the property. Example: modern-downtown-apartment
     *
     * @apiResource App\Http\Resources\PropertyResource
     * @apiResourceModel App\Models\Property with=user,media
     *
     * @param Request $request
     * @param Property $property
     * @return JsonResponse
     */
    public function show(Request $request, Property $property): JsonResponse
    {
        // Implementation remains the same as PropertyController
    }

    /**
     * Update property
     *
     * Update an existing property. Only the property owner can update their properties.
     *
     * @authenticated
     *
     * @urlParam property integer required The ID of the property. Example: 1
     * @bodyParam title string The title of the property. Example: Updated Apartment Title
     * @bodyParam description string The detailed description of the property. Example: Updated description with new details about the property.
     * @bodyParam property_type string The type of property. Example: apartment
     * @bodyParam listing_type string The listing type. Example: rent
     * @bodyParam price number The price of the property. Example: 2800
     * @bodyParam status string The status of the listing. Example: active
     * @bodyParam main_image file The main image of the property.
     * @bodyParam images array An array of additional property images.
     * @bodyParam remove_images array An array of media IDs to remove. Example: [5, 7]
     *
     * @response {
     *   "message": "Property updated successfully.",
     *   "property": {
     *     "id": 1,
     *     "title": "Updated Apartment Title",
     *     "description": "Updated description with new details about the property.",
     *     "price": {
     *       "amount": 2800,
     *       "formatted": "$2,800/month",
     *       "type": "monthly",
     *       "currency": "USD"
     *     },
     *     "status": "active"
     *   }
     * }
     *
     * @response 403 {
     *   "message": "Unauthorized. You can only update your own properties."
     * }
     *
     * @response 404 {
     *   "message": "Property not found."
     * }
     *
     * @param UpdatePropertyRequest $request
     * @param Property $property
     * @return JsonResponse
     */
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        // Implementation remains the same as PropertyController
    }

    /**
     * Delete property
     *
     * Remove a property from the database. Only the property owner can delete their properties.
     *
     * @authenticated
     *
     * @urlParam property integer required The ID of the property. Example: 1
     *
     * @response {
     *   "message": "Property deleted successfully."
     * }
     *
     * @response 403 {
     *   "message": "Unauthorized. You can only delete your own properties."
     * }
     *
     * @response 404 {
     *   "message": "Property not found."
     * }
     *
     * @param Property $property
     * @return JsonResponse
     */
    public function destroy(Property $property): JsonResponse
    {
        // Implementation remains the same as PropertyController
    }

    /**
     * Toggle property favorite status
     *
     * Add or remove a property from the authenticated user's favorites.
     *
     * @authenticated
     *
     * @urlParam property integer required The ID of the property. Example: 1
     *
     * @response {
     *   "message": "Property added to favorites.",
     *   "is_favorited": true
     * }
     *
     * @response {
     *   "message": "Property removed from favorites.",
     *   "is_favorited": false
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @param Property $property
     * @return JsonResponse
     */
    public function toggleFavorite(Property $property): JsonResponse
    {
        // Implementation remains the same as PropertyController
    }
}