<?php

namespace App\Http\Requests\Property;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'listing_type' => 'sometimes|required|in:rent,sale',
            
            // Pricing
            'price' => 'sometimes|required|numeric|min:0|max:99999999.99',
            'price_type' => 'sometimes|nullable|in:monthly,yearly,total',
            
            // Location
            'street_address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|nullable|string|max:100',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'neighborhood' => 'sometimes|nullable|string|max:100',
            
            // Property Details
            'bedrooms' => 'sometimes|required|integer|min:0|max:20',
            'bathrooms' => 'sometimes|required|integer|min:0|max:20',
            'square_feet' => 'sometimes|nullable|integer|min:1|max:50000',
            'lot_size' => 'sometimes|nullable|integer|min:1|max:1000000',
            'year_built' => 'sometimes|nullable|integer|min:1800|max:' . (date('Y') + 2),
            'parking_type' => 'sometimes|nullable|in:none,street,garage,driveway,carport',
            'parking_spaces' => 'sometimes|nullable|integer|min:0|max:10',
            
            // Status
            'status' => 'sometimes|nullable|in:draft,active,pending,sold,rented,inactive',
            'is_featured' => 'sometimes|nullable|boolean',
            'is_available' => 'sometimes|nullable|boolean',
            'available_from' => 'sometimes|nullable|date|after_or_equal:today',
            
            // Amenities and Features
            'amenities' => 'sometimes|nullable|array',
            'amenities.*' => 'string|in:' . implode(',', Property::getAvailableAmenities()),
            'nearby_places' => 'sometimes|nullable|array',
            'nearby_places.*.name' => 'required_with:nearby_places|string|max:100',
            'nearby_places.*.type' => 'required_with:nearby_places|string|max:50',
            'nearby_places.*.distance' => 'required_with:nearby_places|numeric|min:0',
            
            // Media
            'main_image' => 'sometimes|nullable|image|mimes:jpeg,png,webp|max:5120', // 5MB max
            'images' => 'sometimes|nullable|array|max:20',
            'images.*' => 'image|mimes:jpeg,png,webp|max:5120', // 5MB max per image
            'remove_images' => 'sometimes|nullable|array',
            'remove_images.*' => 'integer|exists:media,id',
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
            'postal_code.required' => 'Postal/ZIP code is required.',
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
            'amenities.array' => 'Amenities must be provided as a list.',
            'amenities.*.in' => 'One or more selected amenities are invalid.',
            'main_image.image' => 'Main image must be a valid image file.',
            'main_image.mimes' => 'Main image must be JPEG, PNG, or WebP format.',
            'main_image.max' => 'Main image size cannot exceed 5MB.',
            'images.array' => 'Images must be provided as a list.',
            'images.max' => 'You cannot upload more than 20 images.',
            'images.*.image' => 'All uploaded files must be valid images.',
            'images.*.mimes' => 'All images must be JPEG, PNG, or WebP format.',
            'images.*.max' => 'Each image size cannot exceed 5MB.',
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
            'postal_code' => 'postal/ZIP code',
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
        // Clean up amenities array if provided
        if ($this->has('amenities') && is_array($this->amenities)) {
            $this->merge([
                'amenities' => array_filter(array_unique($this->amenities))
            ]);
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
    }
}
