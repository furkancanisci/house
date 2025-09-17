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
                                    <label for="listing_type">{{ __('admin.listing_type') }} <span class="text-danger">*</span></label>
                                    <select name="listing_type" id="listing_type" class="form-control @error('listing_type') is-invalid @enderror" required>
                                        <option value="">{{ __('admin.select_type') }}</option>
                                        <option value="sale" {{ old('listing_type', $property->listing_type) === 'sale' ? 'selected' : '' }}>{{ __('admin.for_sale') }}</option>
                                        <option value="rent" {{ old('listing_type', $property->listing_type) === 'rent' ? 'selected' : '' }}>{{ __('admin.for_rent') }}</option>
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
                                    <label for="document_type_id">Document Type <span class="text-danger">*</span></label>
                                    <select name="document_type_id" id="document_type_id" class="form-control @error('document_type_id') is-invalid @enderror" required>
                                        <option value="">Select Document Type</option>
                                        @if(isset($documentTypes) && $documentTypes->count() > 0)
                                            @foreach($documentTypes as $documentType)
                                                <option value="{{ $documentType->id }}" {{ old('document_type_id', $property->document_type_id) == $documentType->id ? 'selected' : '' }}>
                                                    {{ $documentType->name_ar }} - {{ $documentType->name_en }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">No document types available</option>
                                        @endif
                                    </select>
                                    @error('document_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pet_policy">Pet Policy</label>
                                    <input type="text" name="pet_policy" id="pet_policy" class="form-control @error('pet_policy') is-invalid @enderror"
                                           value="{{ old('pet_policy', $property->pet_policy) }}" placeholder="e.g., Cats and small dogs allowed">
                                    @error('pet_policy')
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
                                    <label for="price_type">{{ __('admin.price_type') }} <span class="text-danger">*</span></label>
                                    <select name="price_type" id="price_type" class="form-control @error('price_type') is-invalid @enderror" required>
                                        <option value="">{{ __('admin.select_price_type') }}</option>
                                        @foreach($priceTypes as $priceType)
                                            @php
                                                $cssClass = '';
                                                if (in_array($priceType->key, ['monthly', 'yearly'])) {
                                                    $cssClass = 'rent-option';
                                                } elseif (in_array($priceType->key, ['total', 'fixed'])) {
                                                    $cssClass = 'sale-option';
                                                } else {
                                                    $cssClass = 'negotiation-option';
                                                }
                                            @endphp
                                            <option value="{{ $priceType->key }}" 
                                                class="{{ $cssClass }}" 
                                                {{ old('price_type', $property->price_type) === $priceType->key ? 'selected' : '' }}>
                                                {{ $priceType->localized_name }}
                                            </option>
                                        @endforeach
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

                        <!-- Hidden fields for foreign keys -->
                        <input type="hidden" name="governorate_id" id="governorate_id" value="{{ old('governorate_id', $property->governorate_id) }}">
                        <input type="hidden" name="city_id" id="city_id" value="{{ old('city_id', $property->city_id) }}">
                        <input type="hidden" name="neighborhood_id" id="neighborhood_id" value="{{ old('neighborhood_id', $property->neighborhood_id) }}">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="governorate">{{ __('admin.governorate') ?? 'Governorate' }} <span class="text-danger">*</span></label>
                                    <select name="governorate" id="governorate" class="form-control @error('governorate') is-invalid @enderror" required>
                                        <option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.governorate') ?? 'Governorate' }}</option>
                                        @foreach($governorates as $governorate)
                                            <option value="{{ $governorate->id }}" 
                                                    data-name-ar="{{ $governorate->name_ar }}" 
                                                    data-name-en="{{ $governorate->name_en }}"
                                                    data-name-ku="{{ $governorate->name_ku }}"
                                                    {{ old('governorate_id', $property->governorate_id) == $governorate->id ? 'selected' : '' }}>
                                                @if(app()->getLocale() === 'ku')
                                                    {{ $governorate->name_ku ?: $governorate->name_ar }}
                                                @elseif(app()->getLocale() === 'ar')
                                                    {{ $governorate->name_ar }}
                                                @else
                                                    {{ $governorate->name_en }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('governorate')
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
                                            <option value="{{ $city->id }}" 
                                                    data-governorate-id="{{ $city->governorate_id }}"
                                                    data-name-ar="{{ $city->name_ar }}" 
                                                    data-name-en="{{ $city->name_en }}"
                                                    data-name-ku="{{ $city->name_ku }}"
                                                    {{ old('city_id', $property->city_id) == $city->id ? 'selected' : '' }}>
                                                @if(app()->getLocale() === 'ku')
                                                    {{ $city->name_ku ?: $city->name_ar }}
                                                @elseif(app()->getLocale() === 'ar')
                                                    {{ $city->name_ar }}
                                                @else
                                                    {{ $city->name_en }}
                                                @endif
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
                                    <label for="neighborhood">{{ __('admin.neighborhood') ?? 'Neighborhood' }}</label>
                                    <select name="neighborhood" id="neighborhood" class="form-control @error('neighborhood') is-invalid @enderror">
                                        <option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.neighborhood') ?? 'Neighborhood' }}</option>
                                        @foreach($neighborhoods as $neighborhood)
                                            <option value="{{ $neighborhood->id }}" 
                                                    data-city-id="{{ $neighborhood->city_id }}"
                                                    data-name-ar="{{ $neighborhood->name_ar }}" 
                                                    data-name-en="{{ $neighborhood->name_en }}"
                                                    data-name-ku="{{ $neighborhood->name_ku }}"
                                                    {{ old('neighborhood_id', $property->neighborhood_id) == $neighborhood->id ? 'selected' : '' }}>
                                                @if(app()->getLocale() === 'ku')
                                                    {{ $neighborhood->name_ku ?: $neighborhood->name_ar }}
                                                @elseif(app()->getLocale() === 'ar')
                                                    {{ $neighborhood->name_ar }}
                                                @else
                                                    {{ $neighborhood->name_en }}
                                                @endif
                                            </option>
                                        @endforeach
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

                        <!-- Advanced Property Details -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="floor_number">Floor Number</label>
                                    <input type="number" name="floor_number" id="floor_number" class="form-control @error('floor_number') is-invalid @enderror"
                                           value="{{ old('floor_number', $property->floor_number) }}" min="0" max="200">
                                    @error('floor_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_floors">Total Floors</label>
                                    <input type="number" name="total_floors" id="total_floors" class="form-control @error('total_floors') is-invalid @enderror"
                                           value="{{ old('total_floors', $property->total_floors) }}" min="1" max="200">
                                    @error('total_floors')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="balcony_count">Balcony Count</label>
                                    <input type="number" name="balcony_count" id="balcony_count" class="form-control @error('balcony_count') is-invalid @enderror"
                                           value="{{ old('balcony_count', $property->balcony_count ?? 0) }}" min="0" max="20">
                                    @error('balcony_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="orientation">{{ __('Property Orientation') }}</label>
                                    <select name="orientation" id="orientation" class="form-control @error('orientation') is-invalid @enderror">
                                        <option value="">{{ __('Select Orientation') }}</option>
                                        @if(isset($directions) && $directions->count() > 0)
                                            @foreach($directions as $direction)
                                                <option value="{{ $direction->value }}" {{ old('orientation', $property->orientation) === $direction->value ? 'selected' : '' }}>
                                                    {{ $direction->name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">No directions available</option>
                                        @endif
                                    </select>
                                    @error('orientation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="view_type">{{ __('View Type') }}</label>
                                    <select name="view_type" id="view_type" class="form-control @error('view_type') is-invalid @enderror">
                                        <option value="">{{ __('Select View Type') }}</option>
                                        @if(isset($viewTypes) && $viewTypes->count() > 0)
                                            @foreach($viewTypes as $viewType)
                                                <option value="{{ $viewType->value }}" {{ old('view_type', $property->view_type) === $viewType->value ? 'selected' : '' }}>
                                                    {{ $viewType->name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">No view types available</option>
                                        @endif
                                    </select>
                                    @error('view_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Building Details -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="building_age">Building Age (Years)</label>
                                    <input type="number" name="building_age" id="building_age" class="form-control @error('building_age') is-invalid @enderror"
                                           value="{{ old('building_age', $property->building_age) }}" min="0" max="200">
                                    @error('building_age')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="building_type_id">Building Type</label>
                                    <select name="building_type_id" id="building_type_id" class="form-control @error('building_type_id') is-invalid @enderror">
                                        <option value="">Select Building Type</option>
                                        @if(isset($buildingTypes) && $buildingTypes->count() > 0)
                                            @foreach($buildingTypes as $buildingType)
                                                <option value="{{ $buildingType->id }}" {{ old('building_type_id', $property->building_type_id) == $buildingType->id ? 'selected' : '' }}>
                                                    {{ $buildingType->name_ar }} - {{ $buildingType->name_en }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">No building types available</option>
                                        @endif
                                    </select>
                                    @error('building_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="floor_type_id">Floor Type</label>
                                    <select name="floor_type_id" id="floor_type_id" class="form-control @error('floor_type_id') is-invalid @enderror">
                                        <option value="">Select Floor Type</option>
                                        @if(isset($floorTypes) && $floorTypes->count() > 0)
                                            @foreach($floorTypes as $floorType)
                                                <option value="{{ $floorType->id }}" {{ old('floor_type_id', $property->floor_type_id) == $floorType->id ? 'selected' : '' }}>
                                                    {{ $floorType->name_ar }} - {{ $floorType->name_en }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">No floor types available</option>
                                        @endif
                                    </select>
                                    @error('floor_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="window_type_id">Window Type</label>
                                    <select name="window_type_id" id="window_type_id" class="form-control @error('window_type_id') is-invalid @enderror">
                                        <option value="">Select Window Type</option>
                                        @if(isset($windowTypes) && $windowTypes->count() > 0)
                                            @foreach($windowTypes as $windowType)
                                                <option value="{{ $windowType->id }}" {{ old('window_type_id', $property->window_type_id) == $windowType->id ? 'selected' : '' }}>
                                                    {{ $windowType->name_ar }} - {{ $windowType->name_en }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">No window types available</option>
                                        @endif
                                    </select>
                                    @error('window_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="maintenance_fee">Maintenance Fee</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="maintenance_fee" id="maintenance_fee" class="form-control @error('maintenance_fee') is-invalid @enderror"
                                               value="{{ old('maintenance_fee', $property->maintenance_fee) }}" min="0" step="0.01">
                                    </div>
                                    @error('maintenance_fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hoa_fees">HOA Fees</label>
                                    <input type="text" name="hoa_fees" id="hoa_fees" class="form-control @error('hoa_fees') is-invalid @enderror"
                                           value="{{ old('hoa_fees', $property->hoa_fees) }}" placeholder="e.g., $200/month">
                                    @error('hoa_fees')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deposit_amount">Deposit Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="deposit_amount" id="deposit_amount" class="form-control @error('deposit_amount') is-invalid @enderror"
                                               value="{{ old('deposit_amount', $property->deposit_amount) }}" min="0" step="0.01">
                                    </div>
                                    @error('deposit_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="annual_tax">Annual Tax</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="annual_tax" id="annual_tax" class="form-control @error('annual_tax') is-invalid @enderror"
                                               value="{{ old('annual_tax', $property->annual_tax) }}" min="0" step="0.01">
                                    </div>
                                    @error('annual_tax')
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
                                <div class="position-relative image-container">
                                    <img src="{{ $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : $media->getUrl() }}" class="img-thumbnail" style="height: 150px; width: 100%; object-fit: cover;">
                                    <div class="custom-control custom-checkbox position-absolute" style="top: 5px; right: 5px; background: rgba(255,255,255,0.9); border-radius: 3px; padding: 3px 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <input type="checkbox" name="remove_images[]" value="{{ $media->id }}" class="custom-control-input" id="remove_{{ $media->id }}">
                                        <label class="custom-control-label text-danger" for="remove_{{ $media->id }}" style="font-size: 11px; font-weight: bold; margin-bottom: 0;">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </label>
                                    </div>
                                    <!-- Image info overlay -->
                                    <div class="position-absolute" style="bottom: 5px; left: 5px; background: rgba(0,0,0,0.7); color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">
                                        #{{ $media->id }}
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

                <!-- Features & Utilities -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Features & Utilities</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Property Features</h5>
                                <div class="row">
                                    @if(isset($features) && $features->count() > 0)
                                        @foreach($features as $feature)
                                            <div class="col-md-6 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="features[]" value="{{ $feature->id }}"
                                                           id="feature_{{ $feature->id }}" class="custom-control-input"
                                                           {{ in_array($feature->id, old('features', $property->features->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="feature_{{ $feature->id }}">
                                                        {{ $feature->name_ar }} - {{ $feature->name_en }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <p class="text-muted">No features available</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Property Utilities</h5>
                                <div class="row">
                                    @if(isset($utilities) && $utilities->count() > 0)
                                        @foreach($utilities as $utility)
                                            <div class="col-md-6 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="utilities[]" value="{{ $utility->id }}"
                                                           id="utility_{{ $utility->id }}" class="custom-control-input"
                                                           {{ in_array($utility->id, old('utilities', $property->utilities->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="utility_{{ $utility->id }}">
                                                        {{ $utility->name_ar }} - {{ $utility->name_en }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <p class="text-muted">No utilities available</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
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
    // Function to update price type options based on listing type
    function updatePriceTypeOptions() {
        var listingType = $('#listing_type').val();
        var priceTypeSelect = $('#price_type');
        var rentOptions = priceTypeSelect.find('.rent-option');
        var saleOptions = priceTypeSelect.find('.sale-option');
        
        // Always show negotiation options
        priceTypeSelect.find('.negotiation-option').show();
        
        if (listingType === 'rent') {
            rentOptions.show();
            saleOptions.hide();
        } else if (listingType === 'sale') {
            rentOptions.hide();
            saleOptions.show();
        } else {
            rentOptions.show();
            saleOptions.show();
        }
    }
    
    // Handle listing type change
    $('#listing_type').on('change', updatePriceTypeOptions);
    
    // Initialize on page load
    updatePriceTypeOptions();

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
                        var img = $('<div class="col-md-4 mb-3">' +
                                   '<div class="position-relative">' +
                                   '<img src="' + e.target.result + '" class="img-thumbnail" style="height: 150px; width: 100%; object-fit: cover;">' +
                                   '<div class="position-absolute" style="top: 5px; left: 5px; background: rgba(40, 167, 69, 0.9); color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">' +
                                   '<i class="fas fa-plus"></i> New' +
                                   '</div>' +
                                   '</div>' +
                                   '</div>');
                        preview.append(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    });

    // Handle image removal checkboxes
    $('input[name="remove_images[]"]').on('change', function() {
        var container = $(this).closest('.image-container');
        var img = container.find('img');

        if ($(this).is(':checked')) {
            // Mark for removal
            img.css({
                'opacity': '0.5',
                'filter': 'grayscale(100%)'
            });
            container.addClass('marked-for-removal');

            // Add removed overlay
            if (!container.find('.removed-overlay').length) {
                container.append('<div class="removed-overlay position-absolute d-flex align-items-center justify-content-center" style="top: 0; left: 0; right: 0; bottom: 0; background: rgba(220, 53, 69, 0.8); color: white; font-weight: bold;"><i class="fas fa-trash-alt fa-2x"></i></div>');
            }
        } else {
            // Unmark for removal
            img.css({
                'opacity': '1',
                'filter': 'none'
            });
            container.removeClass('marked-for-removal');
            container.find('.removed-overlay').remove();
        }
    });

    // Store original options for filtering
    var originalCities = $('#city').html();
    var originalNeighborhoods = $('#neighborhood').html();
    
    // Handle governorate change to update cities
    $('#governorate').on('change', function() {
        var selectedGovernorateId = $(this).val();
        var citySelect = $('#city');
        var neighborhoodSelect = $('#neighborhood');
        
        // Update hidden field
        $('#governorate_id').val(selectedGovernorateId);
        
        // Clear city and neighborhood dropdowns
        citySelect.html('<option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.city') ?? 'City' }}</option>');
        neighborhoodSelect.html('<option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.neighborhood') ?? 'Neighborhood' }}</option>');
        $('#city_id').val('');
        $('#neighborhood_id').val('');
        
        if (selectedGovernorateId) {
            // Filter cities based on selected governorate
            $(originalCities).filter('option').each(function() {
                var cityGovernorateId = $(this).data('governorate-id');
                if (cityGovernorateId == selectedGovernorateId && $(this).val() !== '') {
                    var option = $(this).clone();
                    // Update option text based on current locale
                    var locale = '{{ app()->getLocale() }}';
                    var nameKu = $(this).data('name-ku');
                    var nameAr = $(this).data('name-ar');
                    var nameEn = $(this).data('name-en');
                    
                    if (locale === 'ku') {
                        option.text(nameKu || nameAr);
                    } else if (locale === 'ar') {
                        option.text(nameAr);
                    } else {
                        option.text(nameEn);
                    }
                    
                    citySelect.append(option);
                }
            });
        }
    });

    // Handle city change to update neighborhoods
    $('#city').on('change', function() {
        var selectedCityId = $(this).val();
        var neighborhoodSelect = $('#neighborhood');
        
        // Update hidden field
        $('#city_id').val(selectedCityId);
        
        // Clear neighborhood dropdown
        neighborhoodSelect.html('<option value="">{{ __('admin.select') ?? 'Select' }} {{ __('admin.neighborhood') ?? 'Neighborhood' }}</option>');
        $('#neighborhood_id').val('');
        
        if (selectedCityId) {
            // Filter neighborhoods based on selected city
            $(originalNeighborhoods).filter('option').each(function() {
                var neighborhoodCityId = $(this).data('city-id');
                if (neighborhoodCityId == selectedCityId && $(this).val() !== '') {
                    var option = $(this).clone();
                    // Update option text based on current locale
                    var locale = '{{ app()->getLocale() }}';
                    var nameKu = $(this).data('name-ku');
                    var nameAr = $(this).data('name-ar');
                    var nameEn = $(this).data('name-en');
                    
                    if (locale === 'ku') {
                        option.text(nameKu || nameAr);
                    } else if (locale === 'ar') {
                        option.text(nameAr);
                    } else {
                        option.text(nameEn);
                    }
                    
                    neighborhoodSelect.append(option);
                }
            });
        }
    });

    // Handle neighborhood change
    $('#neighborhood').on('change', function() {
        var selectedNeighborhoodId = $(this).val();
        $('#neighborhood_id').val(selectedNeighborhoodId);
    });

    // Initialize dropdowns on page load
    function initializeDropdowns() {
        var currentGovernorateId = $('#governorate_id').val();
        var currentCityId = $('#city_id').val();
        var currentNeighborhoodId = $('#neighborhood_id').val();
        
        if (currentGovernorateId) {
            $('#governorate').val(currentGovernorateId).trigger('change');
            
            setTimeout(function() {
                if (currentCityId) {
                    $('#city').val(currentCityId).trigger('change');
                    
                    setTimeout(function() {
                        if (currentNeighborhoodId) {
                            $('#neighborhood').val(currentNeighborhoodId);
                        }
                    }, 100);
                }
            }, 100);
        }
    }
    
    // Call initialization
    initializeDropdowns();

    // Handle state change to update cities (legacy support)
    $('#state').on('change', function() {
        var selectedState = $(this).val();
        // This can be used for additional state-based logic if needed
    });
});
</script>

<style>
.image-container {
    transition: all 0.3s ease;
}

.image-container:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.image-container img {
    transition: all 0.3s ease;
    border-radius: 6px;
}

.marked-for-removal {
    border: 2px solid #dc3545;
    border-radius: 6px;
}

.removed-overlay {
    border-radius: 6px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

#newImagePreview .col-md-4 {
    transition: all 0.3s ease;
}

#newImagePreview .col-md-4:hover {
    transform: scale(1.02);
}

.custom-control-label {
    cursor: pointer;
}

.custom-control-input:checked ~ .custom-control-label {
    color: #dc3545 !important;
    font-weight: bold;
}
</style>
@endpush