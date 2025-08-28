<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all requests for now, we'll handle authorization in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'propertyType' => 'required|string|in:apartment,house,condo,townhouse,studio,loft,villa,commercial,land',
            'listingType' => 'required|string|in:rent,sale',
            'price' => 'required|numeric|min:0',
            'price_type' => 'nullable|string|in:monthly,yearly,total',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postalCode' => 'required|string|min:1|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'neighborhood' => 'nullable|string|max:100',
            'bedrooms' => 'required|integer|min:0|max:20',
            'bathrooms' => 'required|integer|min:0|max:20',
            'squareFootage' => 'nullable|integer|min:0',
            'lotSize' => 'nullable|integer|min:0',
            'yearBuilt' => 'nullable|integer|min:1800|max:' . (date('Y') + 2),
            'parking' => 'nullable|string|max:100',
            'parking_spaces' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:draft,active,pending,sold,rented,inactive',
            'is_featured' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
            'availableDate' => 'nullable|date',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'nearby_places' => 'nullable|array',
            'nearby_places.*.name' => 'required|string',
            'nearby_places.*.type' => 'required|string',
            'nearby_places.*.distance' => 'required|numeric',
            'contactName' => 'nullable|string|max:100',
            'contactPhone' => 'nullable|string|max:20',
            'contactEmail' => 'nullable|email|max:100',
            'documentTypeId' => 'nullable|integer|exists:property_document_types,id',
            'document_type_id' => 'nullable|integer|exists:property_document_types,id', // Also accept snake_case
            'mainImage' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max, no dimension restrictions
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // Also accept snake_case
            'images' => 'nullable|array|max:20', // Maximum 20 images
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max per image, no dimension restrictions
            'base64_images' => 'nullable|array|max:20', // Maximum 20 base64 images
            'base64_images.*' => 'string|regex:/^data:image\/(jpeg|jpg|png|webp);base64,/', // Valid base64 image format
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Log incoming request data for debugging
        \Illuminate\Support\Facades\Log::info('StorePropertyRequest - Raw Input', [
            'all' => $this->all(),
            'files' => $this->allFiles(),
            'method' => $this->method(),
            'content_type' => $this->header('Content-Type'),
            'has_title' => $this->has('title'),
            'title_value' => $this->input('title'),
            'has_description' => $this->has('description'),
            'description_value' => $this->input('description'),
            'input_keys' => array_keys($this->all())
        ]);
        
        // Support both camelCase (from frontend) and snake_case field names
        $data = [];
        
        // Map field names - check both camelCase and snake_case variants
        // For each field, check if it exists before trying to get it
        if ($this->has('propertyType') || $this->has('property_type')) {
            $data['propertyType'] = $this->input('propertyType') ?? $this->input('property_type');
        }
        if ($this->has('listingType') || $this->has('listing_type')) {
            $data['listingType'] = $this->input('listingType') ?? $this->input('listing_type');
        }
        if ($this->has('price')) {
            $data['price'] = $this->input('price');
        }
        if ($this->has('price_type') || $this->has('priceType')) {
            $data['price_type'] = $this->input('price_type') ?? $this->input('priceType') ?? 'monthly';
        }
        if ($this->has('address') || $this->has('street_address')) {
            $data['address'] = $this->input('address') ?? $this->input('street_address');
        }
        if ($this->has('postalCode') || $this->has('postal_code')) {
            $data['postalCode'] = $this->input('postalCode') ?? $this->input('postal_code');
        }
        if ($this->has('squareFootage') || $this->has('square_feet')) {
            $data['squareFootage'] = $this->input('squareFootage') ?? $this->input('square_feet');
        }
        if ($this->has('lotSize') || $this->has('lot_size')) {
            $data['lotSize'] = $this->input('lotSize') ?? $this->input('lot_size');
        }
        if ($this->has('yearBuilt') || $this->has('year_built')) {
            $data['yearBuilt'] = $this->input('yearBuilt') ?? $this->input('year_built');
        }
        if ($this->has('parking') || $this->has('parking_type')) {
            $data['parking'] = $this->input('parking') ?? $this->input('parking_type');
        }
        if ($this->has('parking_spaces') || $this->has('parkingSpaces')) {
            $data['parking_spaces'] = $this->input('parking_spaces') ?? $this->input('parkingSpaces');
        }
        if ($this->has('availableDate') || $this->has('available_date')) {
            $data['availableDate'] = $this->input('availableDate') ?? $this->input('available_date');
        }
        
        // Handle contact fields
        if ($this->has('contactName') || $this->has('contact_name')) {
            $data['contactName'] = $this->input('contactName') ?? $this->input('contact_name');
        }
        if ($this->has('contactPhone') || $this->has('contact_phone')) {
            $data['contactPhone'] = $this->input('contactPhone') ?? $this->input('contact_phone');
        }
        if ($this->has('contactEmail') || $this->has('contact_email')) {
            $data['contactEmail'] = $this->input('contactEmail') ?? $this->input('contact_email');
        }
        
        // Handle document type ID (support both formats)
        if ($this->has('documentTypeId') || $this->has('document_type_id')) {
            $data['documentTypeId'] = $this->input('documentTypeId') ?? $this->input('document_type_id');
            $data['document_type_id'] = $data['documentTypeId'];
        }
        
        // Handle main image (support both formats)
        if ($this->hasFile('mainImage')) {
            $data['mainImage'] = $this->file('mainImage');
            $data['main_image'] = $this->file('mainImage');
        } elseif ($this->hasFile('main_image')) {
            $data['mainImage'] = $this->file('main_image');
            $data['main_image'] = $this->file('main_image');
        }
        
        // Handle multiple images array
        if ($this->hasFile('images')) {
            $data['images'] = $this->file('images');
        }
        
        // Handle amenities - check if it's coming as array indices from FormData
        $amenities = [];
        $index = 0;
        while ($this->has("amenities[{$index}]")) {
            $amenities[] = $this->input("amenities[{$index}]");
            $index++;
        }
        
        // If no indexed amenities, try getting as regular field
        if (empty($amenities)) {
            $amenities = $this->input('amenities');
            if ($amenities && !is_array($amenities)) {
                // If amenities is a JSON string, decode it
                $amenities = json_decode($amenities, true) ?? [];
            }
        }
        $data['amenities'] = $amenities;
        
        // Handle nearby places
        if ($this->has('nearby_places') || $this->has('nearbyPlaces')) {
            $data['nearby_places'] = $this->input('nearby_places') ?? $this->input('nearbyPlaces');
        }
        
        // Handle fields that don't need mapping - just pass them through
        $passthrough_fields = ['title', 'description', 'city', 'state', 'country', 'latitude', 'longitude', 'neighborhood', 'bedrooms', 'bathrooms', 'status'];
        foreach ($passthrough_fields as $field) {
            if ($this->has($field)) {
                $data[$field] = $this->input($field);
            }
        }
        
        // Handle boolean fields - convert string "1"/"0" to actual booleans
        if ($this->has('is_featured') || $this->has('isFeatured')) {
            $isFeatured = $this->input('is_featured') ?? $this->input('isFeatured');
            $data['is_featured'] = $isFeatured === '1' || $isFeatured === true || $isFeatured === 1;
        } else {
            $data['is_featured'] = false; // Default value
        }
        
        if ($this->has('is_available') || $this->has('isAvailable')) {
            $isAvailable = $this->input('is_available') ?? $this->input('isAvailable');
            $data['is_available'] = $isAvailable === '1' || $isAvailable === true || $isAvailable === 1 || $isAvailable === null;
        } else {
            $data['is_available'] = true; // Default value
        }
        
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }
        
        // Log the prepared data
        \Illuminate\Support\Facades\Log::info('StorePropertyRequest - Prepared Data', [
            'data' => $data,
            'has_main_image' => isset($data['mainImage']),
            'amenities_count' => is_array($data['amenities'] ?? null) ? count($data['amenities']) : 0,
            'data_keys' => array_keys($data)
        ]);
        
        // Merge the prepared data with the request
        $this->merge($data);
        
        // Log the final merged data
        \Illuminate\Support\Facades\Log::info('StorePropertyRequest - After Merge', [
            'all' => $this->all(),
            'has_title' => $this->has('title'),
            'title_value' => $this->input('title')
        ]);
    }
}