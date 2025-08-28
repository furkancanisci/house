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
        // Merge data to support both camelCase and snake_case field names
        $this->merge([
            // Handle document type ID (support both formats)
            'documentTypeId' => $this->documentTypeId ?? $this->document_type_id ?? null,
            'document_type_id' => $this->documentTypeId ?? $this->document_type_id ?? null,
            
            // Handle main image (support both formats)
            'mainImage' => $this->mainImage ?? $this->main_image ?? null,
            'main_image' => $this->mainImage ?? $this->main_image ?? null,
            
            // Handle amenities - convert to JSON if it's an array
            'amenities' => $this->amenities && is_array($this->amenities) ? json_encode($this->amenities) : $this->amenities,
        ]);
    }
}