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
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postalCode' => 'required|string|min:1|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'neighborhood' => 'nullable|string|max:100',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
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
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120|dimensions:min_width=400,min_height=300', // 5MB max, min 400x300
            'images' => 'nullable|array|max:20', // Maximum 20 images
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120|dimensions:min_width=400,min_height=300', // 5MB max per image, min 400x300
            'base64_images' => 'nullable|array|max:20', // Maximum 20 base64 images
            'base64_images.*' => 'string|regex:/^data:image\/(jpeg|jpg|png|webp);base64,/', // Valid base64 image format
        ];
    }
}