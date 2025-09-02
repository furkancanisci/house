@extends('admin.layouts.app')

@section('title', 'Edit Property')

@section('content-header', 'Edit Property: ' . $property->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">Properties</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.properties.show', $property) }}">{{ $property->title }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.properties.update', $property) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Main Form -->
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Basic Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="title">Property Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $property->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="property_type">Property Type <span class="text-danger">*</span></label>
                                    <select name="property_type" id="property_type" class="form-control @error('property_type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        @foreach($propertyTypes as $propertyType)
                                            <option value="{{ $propertyType->name }}" 
                                                    {{ old('property_type', $property->property_type) === $propertyType->name ? 'selected' : '' }}>
                                                {{ $propertyType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('property_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="listing_type">Listing Type <span class="text-danger">*</span></label>
                                    <select name="listing_type" id="listing_type" class="form-control @error('listing_type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="sale" {{ old('listing_type', $property->listing_type) === 'sale' ? 'selected' : '' }}>For Sale</option>
                                        <option value="rent" {{ old('listing_type', $property->listing_type) === 'rent' ? 'selected' : '' }}>For Rent</option>
                                    </select>
                                    @error('listing_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="draft" {{ old('status', $property->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="pending" {{ old('status', $property->status) === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                        @can('approve properties')
                                        <option value="active" {{ old('status', $property->status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="rejected" {{ old('status', $property->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="expired" {{ old('status', $property->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                                        @endcan
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" 
                                               value="{{ old('price', $property->price) }}" step="0.01" min="0" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price_type">Price Period (for rent)</label>
                                    <select name="price_type" id="price_type" class="form-control @error('price_type') is-invalid @enderror">
                                        <option value="">Select Period</option>
                                        <option value="monthly" {{ old('price_type', $property->price_type) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="yearly" {{ old('price_type', $property->price_type) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                    @error('price_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="5" required>{{ old('description', $property->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Location</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="street_address">Street Address <span class="text-danger">*</span></label>
                            <input type="text" name="street_address" id="street_address" class="form-control @error('street_address') is-invalid @enderror" 
                                   value="{{ old('street_address', $property->street_address) }}" required>
                            @error('street_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state">{{ __('admin.state') ?? 'State' }} <span class="text-danger">*</span></label>
                                    <select name="state" id="state" class="form-control @error('state') is-invalid @enderror" required>
                                        <option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.state') ?? 'State' }}</option>
                                        @foreach($states as $stateEn => $stateData)
                                            <option value="{{ app()->getLocale() === 'ar' ? $stateData['ar'] : $stateData['en'] }}" 
                                                    {{ old('state', $property->state) === (app()->getLocale() === 'ar' ? $stateData['ar'] : $stateData['en']) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $stateData['ar'] : $stateData['en'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city">{{ __('admin.city') ?? 'City' }} <span class="text-danger">*</span></label>
                                    <select name="city" id="city" class="form-control @error('city') is-invalid @enderror" required>
                                        <option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.city') ?? 'City' }}</option>
                                        @foreach($cities as $city)
                                            <option value="{{ app()->getLocale() === 'ar' ? $city->name_ar : $city->name_en }}" 
                                                    {{ old('city', $property->city) === (app()->getLocale() === 'ar' ? $city->name_ar : $city->name_en) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $city->name_ar : $city->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" name="postal_code" id="postal_code" class="form-control @error('postal_code') is-invalid @enderror" 
                                           value="{{ old('postal_code', $property->postal_code) }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="neighborhood">{{ __('admin.neighborhood') ?? 'Neighborhood' }}</label>
                                    <select name="neighborhood" id="neighborhood" class="form-control @error('neighborhood') is-invalid @enderror">
                                        <option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.neighborhood') ?? 'Neighborhood' }}</option>
                                        @foreach($neighborhoods as $neighborhood)
                                            <option value="{{ app()->getLocale() === 'ar' ? $neighborhood->name_ar : $neighborhood->name_en }}" 
                                                    {{ old('neighborhood', $property->neighborhood) === (app()->getLocale() === 'ar' ? $neighborhood->name_ar : $neighborhood->name_en) ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'ar' ? $neighborhood->name_ar : $neighborhood->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('neighborhood')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input type="number" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" 
                                           value="{{ old('latitude', $property->latitude) }}" step="0.0000001" min="-90" max="90">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input type="number" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" 
                                           value="{{ old('longitude', $property->longitude) }}" step="0.0000001" min="-180" max="180">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Features -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Property Features</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bedrooms">Bedrooms</label>
                                    <input type="number" name="bedrooms" id="bedrooms" class="form-control @error('bedrooms') is-invalid @enderror" 
                                           value="{{ old('bedrooms', $property->bedrooms) }}" min="0" max="20">
                                    @error('bedrooms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bathrooms">Bathrooms</label>
                                    <input type="number" name="bathrooms" id="bathrooms" class="form-control @error('bathrooms') is-invalid @enderror" 
                                           value="{{ old('bathrooms', $property->bathrooms) }}" min="0" max="20">
                                    @error('bathrooms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="square_feet">Square Feet</label>
                                    <input type="number" name="square_feet" id="square_feet" class="form-control @error('square_feet') is-invalid @enderror" 
                                           value="{{ old('square_feet', $property->square_feet) }}" min="1">
                                    @error('square_feet')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="lot_size">Lot Size (sq ft)</label>
                                    <input type="number" name="lot_size" id="lot_size" class="form-control @error('lot_size') is-invalid @enderror" 
                                           value="{{ old('lot_size', $property->lot_size) }}" min="1">
                                    @error('lot_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year_built">Year Built</label>
                                    <input type="number" name="year_built" id="year_built" class="form-control @error('year_built') is-invalid @enderror" 
                                           value="{{ old('year_built', $property->year_built) }}" min="1800" max="{{ date('Y') + 2 }}">
                                    @error('year_built')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="parking_type">Parking Type</label>
                                    <select name="parking_type" id="parking_type" class="form-control @error('parking_type') is-invalid @enderror">
                                        <option value="">Select Parking</option>
                                        <option value="garage" {{ old('parking_type', $property->parking_type) === 'garage' ? 'selected' : '' }}>Garage</option>
                                        <option value="driveway" {{ old('parking_type', $property->parking_type) === 'driveway' ? 'selected' : '' }}>Driveway</option>
                                        <option value="street" {{ old('parking_type', $property->parking_type) === 'street' ? 'selected' : '' }}>Street</option>
                                        <option value="lot" {{ old('parking_type', $property->parking_type) === 'lot' ? 'selected' : '' }}>Parking Lot</option>
                                        <option value="none" {{ old('parking_type', $property->parking_type) === 'none' ? 'selected' : '' }}>No Parking</option>
                                    </select>
                                    @error('parking_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="parking_spaces">Parking Spaces</label>
                                    <input type="number" name="parking_spaces" id="parking_spaces" class="form-control @error('parking_spaces') is-invalid @enderror" 
                                           value="{{ old('parking_spaces', $property->parking_spaces) }}" min="0" max="20">
                                    @error('parking_spaces')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Images -->
                @if($property->media && $property->media->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Current Images</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($property->media as $media)
                            <div class="col-md-3 mb-3">
                                <div class="position-relative">
                                    <img src="{{ $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : $media->getUrl() }}" class="img-thumbnail" style="height: 150px; width: 100%; object-fit: cover;">
                                    <div class="custom-control custom-checkbox position-absolute" style="top: 5px; right: 5px; background: rgba(255,255,255,0.8); border-radius: 3px; padding: 2px;">
                                        <input type="checkbox" name="remove_images[]" value="{{ $media->id }}" class="custom-control-input" id="remove_{{ $media->id }}">
                                        <label class="custom-control-label text-danger" for="remove_{{ $media->id }}" style="font-size: 12px;">Remove</label>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- New Images -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Images</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="new_images">Upload New Images</label>
                            <input type="file" name="new_images[]" id="new_images" class="form-control-file @error('new_images') is-invalid @enderror" 
                                   multiple accept="image/*">
                            <small class="form-text text-muted">
                                Select multiple images (JPEG, PNG, JPG, WebP). Maximum 5MB per image.
                            </small>
                            @error('new_images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="newImagePreview" class="row mt-3"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Publish Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Publish Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="user_id">Property Owner <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Select Owner</option>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ old('user_id', $property->user_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_featured" id="is_featured" class="custom-control-input" 
                                       value="1" {{ old('is_featured', $property->is_featured) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_featured">Featured Property</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_available" id="is_available" class="custom-control-input" 
                                       value="1" {{ old('is_available', $property->is_available) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_available">Available for Viewing</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="available_from">Available From</label>
                            <input type="date" name="available_from" id="available_from" class="form-control @error('available_from') is-invalid @enderror" 
                                   value="{{ old('available_from', $property->available_from ? $property->available_from->format('Y-m-d') : '') }}" min="{{ date('Y-m-d') }}">
                            @error('available_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Property Owner Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Current Property Owner</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <img src="{{ $property->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($property->user->full_name ?? 'User') }}" 
                                 class="img-circle elevation-2 mb-3" alt="User Image" style="width: 80px; height: 80px;">
                            <h5>{{ $property->user->full_name ?? 'Unknown User' }}</h5>
                            <p class="text-muted">{{ $property->user->email ?? '' }}</p>
                            @if($property->user->phone)
                            <p><i class="fas fa-phone"></i> {{ $property->user->phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Contact Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="contact_name">Contact Name</label>
                            <input type="text" name="contact_name" id="contact_name" class="form-control @error('contact_name') is-invalid @enderror" 
                                   value="{{ old('contact_name', $property->contact_name) }}">
                            @error('contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="tel" name="contact_phone" id="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" 
                                   value="{{ old('contact_phone', $property->contact_phone) }}">
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" name="contact_email" id="contact_email" class="form-control @error('contact_email') is-invalid @enderror" 
                                   value="{{ old('contact_email', $property->contact_email) }}">
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Update Property
                        </button>
                        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show/hide price type based on listing type
    $('#listing_type').on('change', function() {
        if ($(this).val() === 'rent') {
            $('#price_type').closest('.form-group').show();
        } else {
            $('#price_type').closest('.form-group').hide();
            $('#price_type').val('');
        }
    }).trigger('change');

    // New image preview
    $('#new_images').on('change', function() {
        var files = this.files;
        var preview = $('#newImagePreview');
        preview.empty();

        if (files.length > 0) {
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = $('<div class="col-md-4 mb-3"><img src="' + e.target.result + '" class="img-thumbnail" style="height: 150px; object-fit: cover;"></div>');
                        preview.append(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    });

    // Handle state change to update cities
    $('#state').on('change', function() {
        var selectedState = $(this).val();
        var citySelect = $('#city');
        var neighborhoodSelect = $('#neighborhood');
        
        // Clear city and neighborhood dropdowns
        citySelect.html('<option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.city') ?? 'City' }}</option>');
        neighborhoodSelect.html('<option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.neighborhood') ?? 'Neighborhood' }}</option>');
        
        if (selectedState) {
            // Make AJAX request to get cities for the selected state
            $.ajax({
                url: '{{ route('admin.get-cities') }}',
                type: 'GET',
                data: { state: selectedState },
                success: function(cities) {
                    $.each(cities, function(index, city) {
                        var cityName = '{{ app()->getLocale() }}' === 'ar' ? city.name_ar : city.name_en;
                        citySelect.append('<option value="' + cityName + '">' + cityName + '</option>');
                    });
                },
                error: function() {
                    console.log('Error loading cities');
                }
            });
        }
    });

    // Handle city change to update neighborhoods
    $('#city').on('change', function() {
        var selectedCity = $(this).val();
        var neighborhoodSelect = $('#neighborhood');
        
        // Clear neighborhood dropdown
        neighborhoodSelect.html('<option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.neighborhood') ?? 'Neighborhood' }}</option>');
        
        if (selectedCity) {
            // Make AJAX request to get neighborhoods for the selected city
            $.ajax({
                url: '{{ route('admin.get-neighborhoods') }}',
                type: 'GET',
                data: { city: selectedCity },
                success: function(neighborhoods) {
                    $.each(neighborhoods, function(index, neighborhood) {
                        var neighborhoodName = '{{ app()->getLocale() }}' === 'ar' ? neighborhood.name_ar : neighborhood.name_en;
                        neighborhoodSelect.append('<option value="' + neighborhoodName + '">' + neighborhoodName + '</option>');
                    });
                },
                error: function() {
                    console.log('Error loading neighborhoods');
                }
            });
        }
    });
});
</script>
@endpush