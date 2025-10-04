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
            'propertyType' => 'required|string|max:255', // Accept any property type slug from frontend
            'property_type_id' => 'nullable|integer|exists:property_types,id', // Foreign key validation
            'listingType' => 'required|string|in:rent,sale',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3|exists:currencies,code',
            'price_type' => 'nullable|string|in:negotiable,final_price,popular_saying,price_from_last,monthly,yearly,total,fixed',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
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
            
            // Features and Utilities
            'features' => 'nullable|array',
            'features.*' => 'integer|exists:features,id',
            'utilities' => 'nullable|array',
            'utilities.*' => 'integer|exists:utilities,id',
            
            // Phase 1 property fields
            'floorNumber' => 'nullable|integer|min:0|max:200',
            'floor_number' => 'nullable|integer|min:0|max:200',
            'totalFloors' => 'nullable|integer|min:1|max:200',
            'total_floors' => 'nullable|integer|min:1|max:200',
            'balconyCount' => 'nullable|integer|min:0|max:20',
            'balcony_count' => 'nullable|integer|min:0|max:20',
            'orientation' => 'nullable|string|in:north,south,east,west,northeast,northwest,southeast,southwest',
            'viewType' => 'nullable|string|in:city,sea,mountain,garden,street,courtyard',
            'view_type' => 'nullable|string|in:city,sea,mountain,garden,street,courtyard',
            
            // Phase 2 advanced property fields
            'buildingAge' => 'nullable|integer|min:0|max:200',
            'building_age' => 'nullable|integer|min:0|max:200',
            'buildingType' => 'nullable|string|in:concrete,brick,wood,steel,mixed',
            'building_type' => 'nullable|string|in:concrete,brick,wood,steel,mixed',
            'floorType' => 'nullable|string|in:tile,hardwood,carpet,laminate,marble,concrete',
            'floor_type' => 'nullable|string|in:tile,hardwood,carpet,laminate,marble,concrete',
            'windowType' => 'nullable|string|in:single,double,triple,aluminum,pvc,wood',
            'window_type' => 'nullable|string|in:single,double,triple,aluminum,pvc,wood',
            'maintenanceFee' => 'nullable|numeric|min:0|max:999999.99',
            'maintenance_fee' => 'nullable|numeric|min:0|max:999999.99',
            'depositAmount' => 'nullable|numeric|min:0|max:999999.99',
            'deposit_amount' => 'nullable|numeric|min:0|max:999999.99',
            'annualTax' => 'nullable|numeric|min:0|max:999999.99',
            'annual_tax' => 'nullable|numeric|min:0|max:999999.99',
            'notes' => 'nullable|string|max:1000',
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
        if ($this->has('currency')) {
            $data['currency'] = $this->input('currency');
        }
        if ($this->has('price_type') || $this->has('priceType')) {
            $data['price_type'] = $this->input('price_type') ?? $this->input('priceType') ?? 'monthly';
        }
        if ($this->has('address') || $this->has('street_address')) {
            $data['address'] = $this->input('address') ?? $this->input('street_address');
        }
        // Postal code field has been removed from the database
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
        
        // Handle property type ID (support both formats)
        if ($this->has('propertyTypeId') || $this->has('property_type_id')) {
            $data['propertyTypeId'] = $this->input('propertyTypeId') ?? $this->input('property_type_id');
            $data['property_type_id'] = $data['propertyTypeId'];
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
        
        // Handle features - check if it's coming as array indices from FormData
        $features = [];
        $index = 0;
        while ($this->has("features[{$index}]")) {
            $features[] = (int) $this->input("features[{$index}]");
            $index++;
        }
        
        // If no indexed features, try getting as regular field
        if (empty($features)) {
            $features = $this->input('features');
            if ($features && !is_array($features)) {
                // If features is a JSON string, decode it
                $features = json_decode($features, true) ?? [];
            }
            // Convert to integers
            if (is_array($features)) {
                $features = array_map('intval', $features);
            }
        }
        $data['features'] = $features;
        
        // Handle utilities - check if it's coming as array indices from FormData
        $utilities = [];
        $index = 0;
        while ($this->has("utilities[{$index}]")) {
            $utilities[] = (int) $this->input("utilities[{$index}]");
            $index++;
        }
        
        // If no indexed utilities, try getting as regular field
        if (empty($utilities)) {
            $utilities = $this->input('utilities');
            if ($utilities && !is_array($utilities)) {
                // If utilities is a JSON string, decode it
                $utilities = json_decode($utilities, true) ?? [];
            }
            // Convert to integers
            if (is_array($utilities)) {
                $utilities = array_map('intval', $utilities);
            }
        }
        $data['utilities'] = $utilities;
        
        // Handle nearby places
        if ($this->has('nearby_places') || $this->has('nearbyPlaces')) {
            $data['nearby_places'] = $this->input('nearby_places') ?? $this->input('nearbyPlaces');
        }
        
        // Handle Phase 1 property fields
        if ($this->has('floorNumber') || $this->has('floor_number')) {
            $data['floorNumber'] = $this->input('floorNumber') ?? $this->input('floor_number');
            $data['floor_number'] = $data['floorNumber'];
        }
        if ($this->has('totalFloors') || $this->has('total_floors')) {
            $data['totalFloors'] = $this->input('totalFloors') ?? $this->input('total_floors');
            $data['total_floors'] = $data['totalFloors'];
        }
        if ($this->has('balconyCount') || $this->has('balcony_count')) {
            $data['balconyCount'] = $this->input('balconyCount') ?? $this->input('balcony_count');
            $data['balcony_count'] = $data['balconyCount'];
        }
        if ($this->has('orientation')) {
            $data['orientation'] = $this->input('orientation');
        }
        if ($this->has('viewType') || $this->has('view_type')) {
            $data['viewType'] = $this->input('viewType') ?? $this->input('view_type');
            $data['view_type'] = $data['viewType'];
        }
        
        // Handle Phase 2 advanced property fields
        if ($this->has('buildingAge') || $this->has('building_age')) {
            $data['buildingAge'] = $this->input('buildingAge') ?? $this->input('building_age');
            $data['building_age'] = $data['buildingAge'];
        }
        if ($this->has('buildingType') || $this->has('building_type')) {
            $data['buildingType'] = $this->input('buildingType') ?? $this->input('building_type');
            $data['building_type'] = $data['buildingType'];
        }
        if ($this->has('floorType') || $this->has('floor_type')) {
            $data['floorType'] = $this->input('floorType') ?? $this->input('floor_type');
            $data['floor_type'] = $data['floorType'];
        }
        if ($this->has('windowType') || $this->has('window_type')) {
            $data['windowType'] = $this->input('windowType') ?? $this->input('window_type');
            $data['window_type'] = $data['windowType'];
        }
        if ($this->has('maintenanceFee') || $this->has('maintenance_fee')) {
            $data['maintenanceFee'] = $this->input('maintenanceFee') ?? $this->input('maintenance_fee');
            $data['maintenance_fee'] = $data['maintenanceFee'];
        }
        if ($this->has('depositAmount') || $this->has('deposit_amount')) {
            $data['depositAmount'] = $this->input('depositAmount') ?? $this->input('deposit_amount');
            $data['deposit_amount'] = $data['depositAmount'];
        }
        if ($this->has('annualTax') || $this->has('annual_tax')) {
            $data['annualTax'] = $this->input('annualTax') ?? $this->input('annual_tax');
            $data['annual_tax'] = $data['annualTax'];
        }
        if ($this->has('notes')) {
            $data['notes'] = $this->input('notes');
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
        
        // Set default status if not provided - all new properties require admin approval
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }
        
        // Log the prepared data
        \Illuminate\Support\Facades\Log::info('StorePropertyRequest - Prepared Data', [
            'data' => $data,
            'has_main_image' => isset($data['mainImage']),
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