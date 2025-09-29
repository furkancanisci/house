<?php

namespace App\Http\Requests\Property;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For development, allow all updates
        // In production, you should enable proper authorization
      /*  return true;*/
        
        // Production authorization code:
        $property = $this->route('property');
        return auth()->check() && 
               $property && 
               $property->user_id === auth()->id();
        
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic Information (all optional for updates)
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:5000',
            'property_type' => 'sometimes|required|in:apartment,house,condo,townhouse,studio,loft,villa,commercial,land',
            'propertyType' => 'sometimes|required|in:apartment,house,condo,townhouse,studio,loft,villa,commercial,land', // Alternative field name
            'listing_type' => 'sometimes|required|in:rent,sale',
            'listingType' => 'sometimes|required|in:rent,sale', // Alternative field name
            
            // Pricing
            'price' => 'sometimes|required|numeric|min:0|max:99999999.99',
            'price_type' => 'sometimes|nullable|in:negotiable,final_price,popular_saying,price_from_last,monthly,yearly,total,fixed',
            
            // Location
            'street_address' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255', // Alternative field name
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            // 'postal_code' => 'sometimes|required|string|max:20', // Removed - column no longer exists
            'country' => 'sometimes|nullable|string|max:100',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'neighborhood' => 'sometimes|nullable|string|max:100',
            
            // Property Details
            'bedrooms' => 'sometimes|required|integer|min:0|max:20',
            'bathrooms' => 'sometimes|required|integer|min:0|max:20',
            'square_feet' => 'sometimes|nullable|integer|min:1|max:50000',
            'squareFootage' => 'sometimes|nullable|integer|min:1|max:50000', // Alternative field name
            'lot_size' => 'sometimes|nullable|integer|min:1|max:1000000',
            'year_built' => 'sometimes|nullable|integer|min:1800|max:' . (date('Y') + 2),
            'yearBuilt' => 'sometimes|nullable|integer|min:1800|max:' . (date('Y') + 2), // Alternative field name
            'parking_type' => 'sometimes|nullable|in:none,street,garage,driveway,carport',
            'parking_spaces' => 'sometimes|nullable|integer|min:0|max:10',
            
            // Status
            'status' => 'sometimes|nullable|in:draft,active,pending,sold,rented,inactive',
            'is_featured' => 'sometimes|nullable|boolean',
            'is_available' => 'sometimes|nullable|boolean',
            'available_from' => 'sometimes|nullable|date|after_or_equal:today',
            
            // Features
            'features' => 'sometimes|nullable|array',
            'features.*' => 'integer|exists:features,id',
            'utilities' => 'sometimes|nullable|array',
            'utilities.*' => 'integer|exists:utilities,id',
            
            // Phase 1 property fields
            'floorNumber' => 'sometimes|nullable|integer|min:0|max:200',
            'floor_number' => 'sometimes|nullable|integer|min:0|max:200',
            'totalFloors' => 'sometimes|nullable|integer|min:1|max:200',
            'total_floors' => 'sometimes|nullable|integer|min:1|max:200',
            'balconyCount' => 'sometimes|nullable|integer|min:0|max:20',
            'balcony_count' => 'sometimes|nullable|integer|min:0|max:20',
            'orientation' => 'sometimes|nullable|string|in:north,south,east,west,northeast,northwest,southeast,southwest',
            'viewType' => 'sometimes|nullable|string|in:city,sea,mountain,garden,street,courtyard',
            'view_type' => 'sometimes|nullable|string|in:city,sea,mountain,garden,street,courtyard',
            
            // Phase 2 advanced property fields
            'buildingAge' => 'sometimes|nullable|integer|min:0|max:200',
            'building_age' => 'sometimes|nullable|integer|min:0|max:200',
            'buildingType' => 'sometimes|nullable|string|in:concrete,brick,wood,steel,mixed',
            'building_type' => 'sometimes|nullable|string|in:concrete,brick,wood,steel,mixed',
            'floorType' => 'sometimes|nullable|string|in:tile,hardwood,carpet,laminate,marble,concrete',
            'floor_type' => 'sometimes|nullable|string|in:tile,hardwood,carpet,laminate,marble,concrete',
            'windowType' => 'sometimes|nullable|string|in:single,double,triple,aluminum,pvc,wood',
            'window_type' => 'sometimes|nullable|string|in:single,double,triple,aluminum,pvc,wood',
            'maintenanceFee' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'maintenance_fee' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'depositAmount' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'deposit_amount' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'annualTax' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'annual_tax' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'nearby_places' => 'sometimes|nullable|array',
            'nearby_places.*.name' => 'required_with:nearby_places|string|max:100',
            'nearby_places.*.type' => 'required_with:nearby_places|string|max:50',
            'nearby_places.*.distance' => 'required_with:nearby_places|numeric|min:0',
            
            // Contact Information
            'contactName' => 'sometimes|required|string|max:100',
            'contactPhone' => 'sometimes|required|string|max:20',
            'contactEmail' => 'sometimes|required|email|max:100',
            'contact_name' => 'sometimes|required|string|max:100', // Alternative field name
            'contact_phone' => 'sometimes|required|string|max:20', // Alternative field name
            'contact_email' => 'sometimes|required|email|max:100', // Alternative field name
            
            // Media
            'main_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:51200', // 50MB max, no dimension restrictions
            'images' => 'sometimes|nullable|array|max:20', // Maximum 20 images
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:51200', // 50MB max per image, no dimension restrictions
            'base64_images' => 'sometimes|nullable|array|max:20', // Maximum 20 base64 images
            'base64_images.*' => 'string|regex:/^data:image\/(jpeg|jpg|png|webp);base64,/', // Valid base64 image format
            'videos' => 'sometimes|nullable|array|max:1', // Maximum 1 video per property
            'videos.*' => 'file|mimes:mp4,avi,mov,wmv,webm|max:512000', // 500MB max per video
            'remove_images' => 'sometimes|nullable|array',
            'remove_images.*' => 'integer|exists:media,id',
            'imagesToRemove' => 'sometimes|nullable|array', // Alternative field name
            'imagesToRemove.*' => 'integer',
            
            // Additional fields that might be sent from frontend
            'petPolicy' => 'sometimes|nullable|string|max:255',
            'utilities' => 'sometimes|nullable|string|max:255',
            'hoaFees' => 'sometimes|nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Property title is required.',
            'title.max' => 'Property title cannot exceed 255 characters.',
            'description.required' => 'Property description is required.',
            'description.max' => 'Property description cannot exceed 5000 characters.',
            'property_type.required' => 'Property type is required.',
            'property_type.in' => 'Invalid property type selected.',
            'listing_type.required' => 'Listing type (rent/sale) is required.',
            'listing_type.in' => 'Invalid listing type selected.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price is too high.',
            'street_address.required' => 'Street address is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State/Province is required.',
            // 'postal_code.required' => 'Postal/ZIP code is required.', // Removed - field no longer exists
            'bedrooms.required' => 'Number of bedrooms is required.',
            'bedrooms.integer' => 'Number of bedrooms must be a whole number.',
            'bedrooms.min' => 'Number of bedrooms cannot be negative.',
            'bedrooms.max' => 'Number of bedrooms cannot exceed 20.',
            'bathrooms.required' => 'Number of bathrooms is required.',
            'bathrooms.integer' => 'Number of bathrooms must be a whole number.',
            'bathrooms.min' => 'Number of bathrooms cannot be negative.',
            'bathrooms.max' => 'Number of bathrooms cannot exceed 20.',
            'square_feet.integer' => 'Square footage must be a whole number.',
            'square_feet.min' => 'Square footage must be at least 1.',
            'square_feet.max' => 'Square footage cannot exceed 50,000.',
            'year_built.integer' => 'Year built must be a valid year.',
            'year_built.min' => 'Year built cannot be before 1800.',
            'year_built.max' => 'Year built cannot be in the future.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'contactName.required' => 'Contact name is required.',
            'contactName.string' => 'Contact name must be a valid string.',
            'contactName.max' => 'Contact name cannot exceed 100 characters.',
            'contactPhone.required' => 'Contact phone is required.',
            'contactPhone.string' => 'Contact phone must be a valid string.',
            'contactPhone.max' => 'Contact phone cannot exceed 20 characters.',
            'contactEmail.required' => 'Contact email is required.',
            'contactEmail.email' => 'Contact email must be a valid email address.',
            'contactEmail.max' => 'Contact email cannot exceed 100 characters.',
            'main_image.image' => 'Main image must be a valid image file.',
            'main_image.mimes' => 'Main image must be JPEG, PNG, or WebP format.',
            'main_image.max' => 'Main image size cannot exceed 50MB.',
            'images.array' => 'Images must be provided as a list.',
            'images.max' => 'You cannot upload more than 20 images.',
            'images.*.image' => 'All uploaded files must be valid images.',
            'images.*.mimes' => 'All images must be JPEG, PNG, or WebP format.',
            'images.*.max' => 'Each image size cannot exceed 50MB.',
            'videos.array' => 'Videos must be provided as a list.',
            'videos.max' => 'You cannot upload more than 1 video.',
            'videos.*.file' => 'All uploaded files must be valid video files.',
            'videos.*.mimes' => 'All videos must be MP4, AVI, MOV, WMV, or WebM format.',
            'videos.*.max' => 'Each video size cannot exceed 500MB.',
            'base64_images.array' => 'Base64 images must be provided as a list.',
            'base64_images.max' => 'You cannot upload more than 20 base64 images.',
            'base64_images.*.regex' => 'Invalid base64 image format. Must be JPEG, PNG, or WebP.',
            'remove_images.array' => 'Images to remove must be provided as a list.',
            'remove_images.*.integer' => 'Invalid image ID provided.',
            'remove_images.*.exists' => 'One or more images to remove do not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'property_type' => 'property type',
            'listing_type' => 'listing type',
            'street_address' => 'street address',
            // 'postal_code' => 'postal/ZIP code', // Removed - field no longer exists
            'square_feet' => 'square footage',
            'year_built' => 'year built',
            'parking_type' => 'parking type',
            'parking_spaces' => 'parking spaces',
            'is_featured' => 'featured status',
            'is_available' => 'availability status',
            'available_from' => 'available from date',
            'main_image' => 'main image',
            'remove_images' => 'images to remove',
            'nearby_places.*.name' => 'nearby place name',
            'nearby_places.*.type' => 'nearby place type',
            'nearby_places.*.distance' => 'nearby place distance',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Handle features array if provided
        if ($this->has('features')) {
            $features = $this->input('features');
            if (is_array($features)) {
                $features = array_map('intval', array_filter($features));
                $this->merge(['features' => $features]);
            }
        }
        
        // Handle utilities array if provided
        if ($this->has('utilities')) {
            $utilities = $this->input('utilities');
            if (is_array($utilities)) {
                $utilities = array_map('intval', array_filter($utilities));
                $this->merge(['utilities' => $utilities]);
            }
        }

        // Convert boolean fields
        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }

        if ($this->has('is_available')) {
            $this->merge(['is_available' => $this->boolean('is_available')]);
        }

        // Set default price_type based on listing_type if not provided
        if ($this->has('listing_type') && !$this->has('price_type')) {
            $this->merge([
                'price_type' => $this->listing_type === 'rent' ? 'monthly' : 'total'
            ]);
        }
        
        // Handle Phase 1 property fields
        if ($this->has('floorNumber') || $this->has('floor_number')) {
            $floorNumber = $this->input('floorNumber') ?? $this->input('floor_number');
            $this->merge(['floorNumber' => $floorNumber, 'floor_number' => $floorNumber]);
        }
        if ($this->has('totalFloors') || $this->has('total_floors')) {
            $totalFloors = $this->input('totalFloors') ?? $this->input('total_floors');
            $this->merge(['totalFloors' => $totalFloors, 'total_floors' => $totalFloors]);
        }
        if ($this->has('balconyCount') || $this->has('balcony_count')) {
            $balconyCount = $this->input('balconyCount') ?? $this->input('balcony_count');
            $this->merge(['balconyCount' => $balconyCount, 'balcony_count' => $balconyCount]);
        }
        if ($this->has('orientation')) {
            $this->merge(['orientation' => $this->input('orientation')]);
        }
        if ($this->has('viewType') || $this->has('view_type')) {
            $viewType = $this->input('viewType') ?? $this->input('view_type');
            $this->merge(['viewType' => $viewType, 'view_type' => $viewType]);
        }
        
        // Handle Phase 2 advanced property fields
        if ($this->has('buildingAge') || $this->has('building_age')) {
            $buildingAge = $this->input('buildingAge') ?? $this->input('building_age');
            $this->merge(['buildingAge' => $buildingAge, 'building_age' => $buildingAge]);
        }
        if ($this->has('buildingType') || $this->has('building_type')) {
            $buildingType = $this->input('buildingType') ?? $this->input('building_type');
            $this->merge(['buildingType' => $buildingType, 'building_type' => $buildingType]);
        }
        if ($this->has('floorType') || $this->has('floor_type')) {
            $floorType = $this->input('floorType') ?? $this->input('floor_type');
            $this->merge(['floorType' => $floorType, 'floor_type' => $floorType]);
        }
        if ($this->has('windowType') || $this->has('window_type')) {
            $windowType = $this->input('windowType') ?? $this->input('window_type');
            $this->merge(['windowType' => $windowType, 'window_type' => $windowType]);
        }
        if ($this->has('maintenanceFee') || $this->has('maintenance_fee')) {
            $maintenanceFee = $this->input('maintenanceFee') ?? $this->input('maintenance_fee');
            $this->merge(['maintenanceFee' => $maintenanceFee, 'maintenance_fee' => $maintenanceFee]);
        }
        if ($this->has('depositAmount') || $this->has('deposit_amount')) {
            $depositAmount = $this->input('depositAmount') ?? $this->input('deposit_amount');
            $this->merge(['depositAmount' => $depositAmount, 'deposit_amount' => $depositAmount]);
        }
        if ($this->has('annualTax') || $this->has('annual_tax')) {
            $annualTax = $this->input('annualTax') ?? $this->input('annual_tax');
            $this->merge(['annualTax' => $annualTax, 'annual_tax' => $annualTax]);
        }
    }
}
