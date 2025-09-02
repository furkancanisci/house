@extends('admin.layouts.app')

@section('title', 'Create Property')

@section('content-header', 'Create New Property')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">Properties</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.properties.store') }}" enctype="multipart/form-data">
        @csrf
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
                                           value="{{ old('title') }}" required>
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
                                        @foreach($propertyTypes as $type)
                                            <option value="{{ $type->name }}" {{ old('property_type') === $type->name ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                        <option value="house" {{ old('property_type') === 'house' ? 'selected' : '' }}>House</option>
                                        <option value="apartment" {{ old('property_type') === 'apartment' ? 'selected' : '' }}>Apartment</option>
                                        <option value="condo" {{ old('property_type') === 'condo' ? 'selected' : '' }}>Condo</option>
                                        <option value="townhouse" {{ old('property_type') === 'townhouse' ? 'selected' : '' }}>Townhouse</option>
                                        <option value="land" {{ old('property_type') === 'land' ? 'selected' : '' }}>Land</option>
                                        <option value="commercial" {{ old('property_type') === 'commercial' ? 'selected' : '' }}>Commercial</option>
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
                                        <option value="sale" {{ old('listing_type') === 'sale' ? 'selected' : '' }}>For Sale</option>
                                        <option value="rent" {{ old('listing_type') === 'rent' ? 'selected' : '' }}>For Rent</option>
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
                                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                        @can('approve properties')
                                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
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
                                               value="{{ old('price') }}" step="0.01" min="0" required>
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
                                        <option value="monthly" {{ old('price_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="yearly" {{ old('price_type') === 'yearly' ? 'selected' : '' }}>Yearly</option>
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
                                      rows="5" required>{{ old('description') }}</textarea>
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
                                   value="{{ old('street_address') }}" required>
                            @error('street_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state">{{ __('admin.state') }} <span class="text-danger">*</span></label>
                                    <select name="state" id="state" class="form-control @error('state') is-invalid @enderror" required>
                                        <option value="">{{ __('admin.select') }} {{ __('admin.state') }}</option>
                                        @foreach($states as $stateEn => $stateData)
                                            <option value="{{ app()->getLocale() === 'ar' ? $stateData['ar'] : $stateData['en'] }}" 
                                                    {{ old('state') === (app()->getLocale() === 'ar' ? $stateData['ar'] : $stateData['en']) ? 'selected' : '' }}>
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
                                    <label for="city">{{ __('admin.city') }} <span class="text-danger">*</span></label>
                                    <select name="city" id="city" class="form-control @error('city') is-invalid @enderror" required disabled>
                                        <option value="">{{ __('admin.select') }} {{ __('admin.state') }} {{ __('admin.first') }}</option>
                                    </select>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="neighborhood">{{ __('admin.neighborhood') }}</label>
                                    <select name="neighborhood" id="neighborhood" class="form-control @error('neighborhood') is-invalid @enderror" disabled>
                                        <option value="">{{ __('admin.select') }} {{ __('admin.city') }} {{ __('admin.first') }}</option>
                                    </select>
                                    @error('neighborhood')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code">{{ __('admin.postal_code') ?? 'Postal Code' }}</label>
                                    <input type="text" name="postal_code" id="postal_code" class="form-control @error('postal_code') is-invalid @enderror" 
                                           value="{{ old('postal_code') }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <div class="input-group">
                                        <input type="number" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" 
                                               value="{{ old('latitude') }}" step="0.0000001" min="-90" max="90" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="clearLatLng" title="Clear coordinates">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <div class="input-group">
                                        <input type="number" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" 
                                               value="{{ old('longitude') }}" step="0.0000001" min="-180" max="180" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary" id="getCurrentLocation" title="Get current location">
                                                <i class="fas fa-crosshairs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Interactive Map for Location Selection -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Select Location on Map</label>
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div class="map-container">
                                                <div id="propertyMap"></div>
                                                <div class="map-overlay">
                                                    <div class="btn-group-vertical">
                                                        <button type="button" class="btn btn-sm btn-primary" id="searchOnMap" title="Search address on map">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-info" id="centerMap" title="Center map on marker">
                                                            <i class="fas fa-crosshairs"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Click on the map to set the property location. The latitude and longitude will be automatically filled.
                                    </small>
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
                                           value="{{ old('bedrooms') }}" min="0" max="20">
                                    @error('bedrooms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="bathrooms">Bathrooms</label>
                                    <input type="number" name="bathrooms" id="bathrooms" class="form-control @error('bathrooms') is-invalid @enderror" 
                                           value="{{ old('bathrooms') }}" min="0" max="20">
                                    @error('bathrooms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="square_feet">Square Feet</label>
                                    <input type="number" name="square_feet" id="square_feet" class="form-control @error('square_feet') is-invalid @enderror" 
                                           value="{{ old('square_feet') }}" min="1">
                                    @error('square_feet')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="lot_size">Lot Size (sq ft)</label>
                                    <input type="number" name="lot_size" id="lot_size" class="form-control @error('lot_size') is-invalid @enderror" 
                                           value="{{ old('lot_size') }}" min="1">
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
                                           value="{{ old('year_built') }}" min="1800" max="{{ date('Y') + 2 }}">
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
                                        <option value="garage" {{ old('parking_type') === 'garage' ? 'selected' : '' }}>Garage</option>
                                        <option value="driveway" {{ old('parking_type') === 'driveway' ? 'selected' : '' }}>Driveway</option>
                                        <option value="street" {{ old('parking_type') === 'street' ? 'selected' : '' }}>Street</option>
                                        <option value="lot" {{ old('parking_type') === 'lot' ? 'selected' : '' }}>Parking Lot</option>
                                        <option value="none" {{ old('parking_type') === 'none' ? 'selected' : '' }}>No Parking</option>
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
                                           value="{{ old('parking_spaces') }}" min="0" max="20">
                                    @error('parking_spaces')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Property Images</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="images">Upload Images</label>
                            <input type="file" name="images[]" id="images" class="form-control-file @error('images') is-invalid @enderror" 
                                   multiple accept="image/*">
                            <small class="form-text text-muted">
                                Select multiple images (JPEG, PNG, JPG, WebP). Maximum 5MB per image.
                            </small>
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="imagePreview" class="row mt-3"></div>
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
                                    <option value="{{ $id }}" {{ old('user_id', auth()->id()) == $id ? 'selected' : '' }}>
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
                                       value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_featured">Featured Property</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_available" id="is_available" class="custom-control-input" 
                                       value="1" {{ old('is_available', '1') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_available">Available for Viewing</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="available_from">Available From</label>
                            <input type="date" name="available_from" id="available_from" class="form-control @error('available_from') is-invalid @enderror" 
                                   value="{{ old('available_from') }}" min="{{ date('Y-m-d') }}">
                            @error('available_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                   value="{{ old('contact_name') }}">
                            @error('contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="tel" name="contact_phone" id="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" 
                                   value="{{ old('contact_phone') }}">
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" name="contact_email" id="contact_email" class="form-control @error('contact_email') is-invalid @enderror" 
                                   value="{{ old('contact_email') }}">
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
                            <i class="fas fa-save"></i> Create Property
                        </button>
                        <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<style>
    .leaflet-popup-content {
        margin: 8px 12px;
        line-height: 1.4;
    }
    .map-container {
        margin-bottom: 1rem;
        position: relative;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }
    #propertyMap {
        height: 400px !important;
        width: 100% !important;
        max-width: 100% !important;
        border: 1px solid #ddd;
        border-radius: 4px;
        position: relative;
        z-index: 1;
        box-sizing: border-box;
        display: block;
    }
    .leaflet-container {
        width: 100% !important;
        height: 400px !important;
        max-width: 100% !important;
        position: relative !important;
        box-sizing: border-box !important;
        background: #f0f0f0;
    }
    .leaflet-map-pane {
        width: 100% !important;
        height: 100% !important;
    }
    .leaflet-tile-container {
        width: 100% !important;
        height: 100% !important;
    }
    .leaflet-tile {
        width: 256px !important;
        height: 256px !important;
        max-width: none !important;
        max-height: none !important;
    }
    .leaflet-control-attribution {
        font-size: 10px;
    }
    .map-overlay {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
    }
    /* Force container constraints */
    .card .card-body .map-container {
        max-width: 100% !important;
        overflow: hidden !important;
    }
    .card .card-body .map-container #propertyMap {
        max-width: 100% !important;
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
$(document).ready(function() {
    // Show/hide price type based on listing type
    $('#listing_type').on('change', function() {
        var listingType = $(this).val();
        if (listingType === 'rent') {
            $('#price_type').closest('.form-group').show();
            $('#price_type').prop('required', true);
        } else {
            $('#price_type').closest('.form-group').hide();
            $('#price_type').val('').prop('required', false);
        }
    }).trigger('change');

    // Image preview
    $('#images').on('change', function() {
        var files = this.files;
        var preview = $('#imagePreview');
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

    // Cascading dropdowns for location
    $('#state').on('change', function() {
        var state = $(this).val();
        var citySelect = $('#city');
        var neighborhoodSelect = $('#neighborhood');
        
        // Reset city and neighborhood dropdowns
        citySelect.html('<option value="">Loading...</option>').prop('disabled', true);
        neighborhoodSelect.html('<option value="">Select state first</option>').prop('disabled', true);
        
        if (state) {
            $.ajax({
                url: '{{ route('admin.properties.cities-by-state') }}',
                type: 'GET',
                data: { state: state },
                dataType: 'json',
                success: function(data) {
                    citySelect.html('<option value="">Select City</option>');
                    
                    if (data.length > 0) {
                        $.each(data, function(index, city) {
                            citySelect.append('<option value="' + city.value + '">' + city.name + '</option>');
                        });
                        citySelect.prop('disabled', false);
                    } else {
                        citySelect.html('<option value="">No cities available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading cities:', error);
                    citySelect.html('<option value="">Error loading cities</option>');
                }
            });
        } else {
            citySelect.html('<option value="">Select state first</option>').prop('disabled', true);
        }
    });

    $('#city').on('change', function() {
        var city = $(this).val();
        var neighborhoodSelect = $('#neighborhood');
        
        // Reset neighborhood dropdown
        neighborhoodSelect.html('<option value="">Loading...</option>').prop('disabled', true);
        
        if (city) {
            $.ajax({
                url: '{{ route('admin.properties.neighborhoods-by-city') }}',
                type: 'GET',
                data: { city: city },
                dataType: 'json',
                success: function(data) {
                    neighborhoodSelect.html('<option value="">Select Neighborhood</option>');
                    
                    if (data.length > 0) {
                        $.each(data, function(index, neighborhood) {
                            neighborhoodSelect.append('<option value="' + neighborhood.value + '">' + neighborhood.name + '</option>');
                        });
                        neighborhoodSelect.prop('disabled', false);
                    } else {
                        neighborhoodSelect.html('<option value="">No neighborhoods available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading neighborhoods:', error);
                    neighborhoodSelect.html('<option value="">Error loading neighborhoods</option>');
                }
            });
        } else {
            neighborhoodSelect.html('<option value="">Select city first</option>').prop('disabled', true);
        }
    });

    // Handle form validation for dependent dropdowns
    $('form').on('submit', function(e) {
        var state = $('#state').val();
        var city = $('#city').val();
        var listingType = $('#listing_type').val();
        
        // Check listing type
        if (!listingType) {
            e.preventDefault();
            alert('Please select a listing type (For Sale or For Rent)');
            $('#listing_type').focus();
            return false;
        }
        
        // Check state and city dependency
        if (state && !city) {
            e.preventDefault();
            alert('Please select a city');
            $('#city').focus();
            return false;
        }
        
        // Check price type for rent
        if (listingType === 'rent' && !$('#price_type').val()) {
            $('#price_type').val('monthly'); // Set default to monthly
        }
        
        // Debug log before submission
        console.log('Form submission data:', {
            listing_type: listingType,
            price_type: $('#price_type').val(),
            state: state,
            city: city
        });
    });

    // Map functionality
    let propertyMap;
    let mapMarker;
    let defaultLat = 35.2131; // Damascus, Syria
    let defaultLng = 36.7011;
    
    // Initialize map
    function initializeMap() {
        // Ensure container exists and has proper dimensions
        const mapContainer = document.getElementById('propertyMap');
        if (!mapContainer) return;
        
        // Force container dimensions
        mapContainer.style.width = '100%';
        mapContainer.style.height = '400px';
        mapContainer.style.maxWidth = '100%';
        mapContainer.style.position = 'relative';
        
        // Create map centered on Syria (Damascus)
        propertyMap = L.map('propertyMap', {
            preferCanvas: false,
            zoomControl: true,
            attributionControl: true,
            fadeAnimation: true,
            zoomAnimation: true,
            markerZoomAnimation: true
        }).setView([defaultLat, defaultLng], 8);
        
        // Add OpenStreetMap tiles with proper configuration
        const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
            minZoom: 3,
            tileSize: 256,
            zoomOffset: 0,
            crossOrigin: true
        });
        
        tileLayer.addTo(propertyMap);
        
        // Force map to refresh after tile layer is added
        setTimeout(function() {
            propertyMap.invalidateSize(true);
        }, 100);
        
        // Add click event to set marker
        propertyMap.on('click', function(e) {
            setMapMarker(e.latlng.lat, e.latlng.lng);
        });
        
        // Set initial marker if coordinates exist
        let currentLat = $('#latitude').val();
        let currentLng = $('#longitude').val();
        if (currentLat && currentLng) {
            setMapMarker(parseFloat(currentLat), parseFloat(currentLng));
            propertyMap.setView([currentLat, currentLng], 15);
        }
    }
    
    // Set marker on map and update coordinates
    function setMapMarker(lat, lng) {
        if (mapMarker) {
            propertyMap.removeLayer(mapMarker);
        }
        
        mapMarker = L.marker([lat, lng], {
            draggable: true
        }).addTo(propertyMap);
        
        mapMarker.bindPopup(`<b>Property Location</b><br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
        
        // Update input fields
        $('#latitude').val(lat.toFixed(6));
        $('#longitude').val(lng.toFixed(6));
        
        // Make marker draggable
        mapMarker.on('dragend', function(e) {
            let position = e.target.getLatLng();
            $('#latitude').val(position.lat.toFixed(6));
            $('#longitude').val(position.lng.toFixed(6));
            mapMarker.setPopupContent(`<b>Property Location</b><br>Lat: ${position.lat.toFixed(6)}<br>Lng: ${position.lng.toFixed(6)}`);
        });
    }
    
    // Clear coordinates and marker
    $('#clearLatLng').on('click', function() {
        $('#latitude').val('');
        $('#longitude').val('');
        if (mapMarker) {
            propertyMap.removeLayer(mapMarker);
            mapMarker = null;
        }
    });
    
    // Get current location
    $('#getCurrentLocation').on('click', function() {
        if (navigator.geolocation) {
            $(this).html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    let lat = position.coords.latitude;
                    let lng = position.coords.longitude;
                    setMapMarker(lat, lng);
                    propertyMap.setView([lat, lng], 15);
                    
                    $('#getCurrentLocation').html('<i class="fas fa-crosshairs"></i>').prop('disabled', false);
                },
                function(error) {
                    alert('Error getting location: ' + error.message);
                    $('#getCurrentLocation').html('<i class="fas fa-crosshairs"></i>').prop('disabled', false);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    });
    
    // Search address on map
    $('#searchOnMap').on('click', function() {
        let address = $('#street_address').val();
        let city = $('#city').val();
        let state = $('#state').val();
        
        if (!address && !city) {
            alert('Please enter a street address or select a city first.');
            return;
        }
        
        let searchQuery = [address, city, state, 'Syria'].filter(Boolean).join(', ');
        
        $(this).html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        // Use OpenStreetMap Nominatim for geocoding with Syria bounds
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=3&countrycodes=sy&bounded=1&viewbox=35.7,32.3,42.4,37.3`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    let lat = parseFloat(data[0].lat);
                    let lng = parseFloat(data[0].lon);
                    setMapMarker(lat, lng);
                    propertyMap.setView([lat, lng], 15);
                } else {
                    alert('Address not found. Please try a different address or click on the map.');
                }
                $('#searchOnMap').html('<i class="fas fa-search"></i>').prop('disabled', false);
            })
            .catch(error => {
                console.error('Geocoding error:', error);
                alert('Error searching for address. Please try again.');
                $('#searchOnMap').html('<i class="fas fa-search"></i>').prop('disabled', false);
            });
    });
    
    // Center map on marker
    $('#centerMap').on('click', function() {
        if (mapMarker) {
            propertyMap.setView(mapMarker.getLatLng(), 15);
        } else {
            propertyMap.setView([defaultLat, defaultLng], 8);
        }
    });
    
    // Update map when city changes
    $('#city').on('change', function() {
        let city = $(this).val();
        if (city && !$('#latitude').val()) {
            // Auto-search for city location if no coordinates set
            setTimeout(function() {
                $('#searchOnMap').click();
            }, 500);
        }
    });
    
    // Initialize map when document is ready
    setTimeout(function() {
        initializeMap();
        // Force map to resize properly
        setTimeout(function() {
            if (propertyMap) {
                propertyMap.invalidateSize();
                // Force container constraints
                enforceMapConstraints();
            }
        }, 250);
    }, 100);
    
    // Function to enforce map container constraints
    function enforceMapConstraints() {
        const mapContainer = document.getElementById('propertyMap');
        const leafletContainer = mapContainer?.querySelector('.leaflet-container');
        
        if (mapContainer) {
            mapContainer.style.width = '100%';
            mapContainer.style.maxWidth = '100%';
            mapContainer.style.height = '400px';
            mapContainer.style.position = 'relative';
        }
        
        if (leafletContainer) {
            leafletContainer.style.width = '100%';
            leafletContainer.style.maxWidth = '100%';
            leafletContainer.style.height = '400px';
            leafletContainer.style.position = 'relative';
        }
        
        // Force map to recalculate its size
        if (propertyMap) {
            propertyMap.invalidateSize(true);
        }
    }
    
    // Monitor for any size changes (less frequent to avoid interference)
    setTimeout(function() {
        setInterval(enforceMapConstraints, 5000);
    }, 2000);
});
</script>
@endpush