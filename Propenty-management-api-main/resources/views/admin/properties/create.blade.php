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
                                        <option value="retail" {{ old('property_type') === 'retail' ? 'selected' : '' }}>Retail</option>
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
                                        <option value="sale" {{ old('listing_type') === 'sale' ? 'selected' : '' }}>{{ __('admin.for_sale') }}</option>
                                        <option value="rent" {{ old('listing_type') === 'rent' ? 'selected' : '' }}>{{ __('admin.for_rent') }}</option>
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
                                    <label for="document_type_id">Document Type <span class="text-danger">*</span></label>
                                    <select name="document_type_id" id="document_type_id" class="form-control @error('document_type_id') is-invalid @enderror" required>
                                        <option value="">Select Document Type</option>
                                        @if(isset($documentTypes) && $documentTypes->count() > 0)
                                            @foreach($documentTypes as $documentType)
                                                <option value="{{ $documentType->id }}" {{ old('document_type_id') == $documentType->id ? 'selected' : '' }}>
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
                                           value="{{ old('pet_policy') }}" placeholder="e.g., Cats and small dogs allowed">
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
                                               value="{{ old('price') }}" step="0.01" min="0" required>
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
                                                {{ old('price_type') === $priceType->key ? 'selected' : '' }} 
                                                class="{{ $cssClass }}">
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
                            <!-- State field removed - using governorate instead for better data integrity -->
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

                        <!-- Map Location -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-weight-bold mb-3">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        Select Property Location on Map
                                    </label>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <input type="text" id="mapSearch" class="form-control" placeholder="Search for address or click on map...">
                                            <div class="input-group-append">
                                                <button type="button" id="searchButton" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Search
                                                </button>
                                                <button type="button" id="getCurrentLocation" class="btn btn-success">
                                                    <i class="fas fa-crosshairs"></i> My Location
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="propertyMap" style="height: 400px; width: 100%; border: 2px solid #ddd; border-radius: 8px; position: relative; z-index: 1;"></div>
                                    <div id="selectedLocation" class="mt-3" style="display: none;">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i>
                                            <strong>Location Selected:</strong>
                                            <span id="locationText"></span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
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

                        <!-- Advanced Property Details -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="floor_number">Floor Number</label>
                                    <input type="number" name="floor_number" id="floor_number" class="form-control @error('floor_number') is-invalid @enderror"
                                           value="{{ old('floor_number') }}" min="0" max="200">
                                    @error('floor_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_floors">Total Floors</label>
                                    <input type="number" name="total_floors" id="total_floors" class="form-control @error('total_floors') is-invalid @enderror"
                                           value="{{ old('total_floors') }}" min="1" max="200">
                                    @error('total_floors')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="balcony_count">Balcony Count</label>
                                    <input type="number" name="balcony_count" id="balcony_count" class="form-control @error('balcony_count') is-invalid @enderror"
                                           value="{{ old('balcony_count', 0) }}" min="0" max="20">
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
                                                <option value="{{ $direction->value }}" {{ old('orientation') === $direction->value ? 'selected' : '' }}>
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
                                                <option value="{{ $viewType->value }}" {{ old('view_type') === $viewType->value ? 'selected' : '' }}>
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
                                           value="{{ old('building_age') }}" min="0" max="200">
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
                                                <option value="{{ $buildingType->id }}" {{ old('building_type_id') == $buildingType->id ? 'selected' : '' }}>
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
                                                <option value="{{ $floorType->id }}" {{ old('floor_type_id') == $floorType->id ? 'selected' : '' }}>
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
                                                <option value="{{ $windowType->id }}" {{ old('window_type_id') == $windowType->id ? 'selected' : '' }}>
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
                                               value="{{ old('maintenance_fee') }}" min="0" step="0.01">
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
                                           value="{{ old('hoa_fees') }}" placeholder="e.g., $200/month">
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
                                               value="{{ old('deposit_amount') }}" min="0" step="0.01">
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
                                               value="{{ old('annual_tax') }}" min="0" step="0.01">
                                    </div>
                                    @error('annual_tax')
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
                                                           {{ in_array($feature->id, old('features', [])) ? 'checked' : '' }}>
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
                                                           {{ in_array($utility->id, old('utilities', [])) ? 'checked' : '' }}>
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
    /* Card container constraints */
    .card {
        overflow: hidden !important;
        position: relative;
    }
    
    .card-body {
        overflow: hidden !important;
        position: relative;
        contain: layout style paint;
    }

</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
$(document).ready(function() {
    // Show price type for both rent and sale listings
    $('#listing_type').on('change', function() {
        var listingType = $(this).val();
        if (listingType === 'rent' || listingType === 'sale') {
            $('#price_type').closest('.form-group').show();
            $('#price_type').prop('required', true);
            updatePriceTypeOptions();
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
                        var img = $('<div class="col-md-4 mb-3">' +
                                   '<div class="position-relative image-preview">' +
                                   '<img src="' + e.target.result + '" class="img-thumbnail" style="height: 150px; width: 100%; object-fit: cover;">' +
                                   '<div class="position-absolute" style="top: 5px; left: 5px; background: rgba(40, 167, 69, 0.9); color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">' +
                                   '<i class="fas fa-image"></i> Preview' +
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

    // Initialize Leaflet map
    let propertyMap = null;
    let marker = null;

    // Syria center coordinates and bounds
    const SYRIA_CENTER = [35.0, 38.0];
    const SYRIA_BOUNDS = [[32.0, 35.5], [37.5, 42.5]]; // Southwest to Northeast
    const DEFAULT_ZOOM = 7;
    const DETAIL_ZOOM = 16;

    // Custom marker icon function
    function createMarkerIcon() {
        return L.divIcon({
            html: `
                <div style="
                    background-color: #dc3545;
                    width: 24px;
                    height: 24px;
                    border-radius: 50% 50% 50% 0;
                    transform: rotate(-45deg);
                    border: 2px solid white;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <div style="
                        width: 8px;
                        height: 8px;
                        background-color: white;
                        border-radius: 50%;
                        transform: rotate(45deg);
                    "></div>
                </div>
            `,
            className: 'custom-location-marker',
            iconSize: [24, 24],
            iconAnchor: [12, 24],
            popupAnchor: [0, -24]
        });
    }

    function initializeMap() {
        try {
            // Create map with Syria bounds restriction
            propertyMap = L.map('propertyMap', {
                center: SYRIA_CENTER,
                zoom: DEFAULT_ZOOM,
                maxBounds: SYRIA_BOUNDS,
                maxBoundsViscosity: 1.0,
                minZoom: 6,
                maxZoom: 18
            });

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors | Syria Property Map',
                maxZoom: 18,
                bounds: SYRIA_BOUNDS
            }).addTo(propertyMap);

            // Fit map to Syria bounds
            propertyMap.fitBounds(SYRIA_BOUNDS);
        } catch (error) {
            console.error('Map initialization error:', error);
            alert('خطأ في تحميل الخريطة / Map loading error. Please refresh the page.');
            return;
        }

        // Handle map clicks (only within Syria bounds)
        propertyMap.on('click', async function(e) {
            const { lat, lng } = e.latlng;

            // Check if click is within Syria bounds
            if (lat >= SYRIA_BOUNDS[0][0] && lat <= SYRIA_BOUNDS[1][0] &&
                lng >= SYRIA_BOUNDS[0][1] && lng <= SYRIA_BOUNDS[1][1]) {
                await setMarkerPosition(lat, lng);
            } else {
                alert('يرجى تحديد موقع داخل سوريا فقط / Please select a location within Syria only');
            }
        });

        // Load existing coordinates if available
        const existingLat = $('#latitude').val();
        const existingLng = $('#longitude').val();
        if (existingLat && existingLng) {
            setMarkerPosition(parseFloat(existingLat), parseFloat(existingLng));
        }
    }

    async function setMarkerPosition(lat, lng) {
        // Remove existing marker
        if (marker) {
            propertyMap.removeLayer(marker);
        }

        // Add new marker
        marker = L.marker([lat, lng], {
            icon: createMarkerIcon(),
            draggable: true
        }).addTo(propertyMap);

        // Handle marker drag (keep within Syria bounds)
        marker.on('dragend', function(e) {
            const newPos = e.target.getLatLng();
            const lat = newPos.lat;
            const lng = newPos.lng;

            // Check if dragged position is within Syria bounds
            if (lat >= SYRIA_BOUNDS[0][0] && lat <= SYRIA_BOUNDS[1][0] &&
                lng >= SYRIA_BOUNDS[0][1] && lng <= SYRIA_BOUNDS[1][1]) {
                updateCoordinates(lat, lng);
            } else {
                // Reset marker to previous valid position if dragged outside Syria
                alert('يرجى إبقاء الموقع داخل سوريا / Please keep the location within Syria');
                const currentLat = $('#latitude').val();
                const currentLng = $('#longitude').val();
                if (currentLat && currentLng) {
                    marker.setLatLng([parseFloat(currentLat), parseFloat(currentLng)]);
                } else {
                    // If no previous position, reset to Syria center
                    marker.setLatLng(SYRIA_CENTER);
                    updateCoordinates(SYRIA_CENTER[0], SYRIA_CENTER[1]);
                }
            }
        });

        // Update coordinates
        updateCoordinates(lat, lng);

        // Center map on marker
        propertyMap.setView([lat, lng], DETAIL_ZOOM);

        // Reverse geocode to get address
        try {
            const address = await reverseGeocode(lat, lng);
            if (address) {
                $('#mapSearch').val(address);
            }
        } catch (error) {
            console.error('Reverse geocoding failed:', error);
        }
    }

    function updateCoordinates(lat, lng) {
        $('#latitude').val(lat);
        $('#longitude').val(lng);
        $('#locationText').text(`${lat.toFixed(6)}, ${lng.toFixed(6)}`);
        $('#selectedLocation').show();
    }

    // Reverse geocoding using Nominatim
    async function reverseGeocode(lat, lng) {
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
            );
            if (response.ok) {
                const data = await response.json();
                return data.display_name || null;
            }
        } catch (error) {
            console.error('Reverse geocoding error:', error);
        }
        return null;
    }

    // Forward geocoding using Nominatim
    async function forwardGeocode(query) {
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&addressdetails=1&countrycodes=sy`
            );
            if (response.ok) {
                const data = await response.json();
                if (data.length > 0) {
                    const result = data[0];
                    return {
                        lat: parseFloat(result.lat),
                        lng: parseFloat(result.lon),
                        address: result.display_name
                    };
                }
            }
        } catch (error) {
            console.error('Forward geocoding error:', error);
        }
        return null;
    }

    // Search functionality
    $('#searchButton').on('click', async function() {
        const query = $('#mapSearch').val().trim();
        if (!query) {
            alert('Please enter an address to search');
            return;
        }

        try {
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Searching...');

            const result = await forwardGeocode(query);
            if (result) {
                await setMarkerPosition(result.lat, result.lng);
                $('#mapSearch').val(result.address);
            } else {
                alert('Address not found. Please try a different search term.');
            }
        } catch (error) {
            console.error('Search error:', error);
            alert('Search failed. Please try again.');
        } finally {
            $(this).prop('disabled', false).html('<i class="fas fa-search"></i> Search');
        }
    });

    // Get current location
    $('#getCurrentLocation').on('click', function() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by this browser');
            return;
        }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Getting Location...');

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                await setMarkerPosition(lat, lng);
                $('#getCurrentLocation').prop('disabled', false).html('<i class="fas fa-crosshairs"></i> My Location');
            },
            function(error) {
                console.error('Geolocation error:', error);
                alert('Unable to get your location. Please use the search or click on the map.');
                $('#getCurrentLocation').prop('disabled', false).html('<i class="fas fa-crosshairs"></i> My Location');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    });

    // Allow search on Enter key
    $('#mapSearch').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#searchButton').click();
        }
    });

    // Initialize map when page loads - wait for DOM, CSS and Leaflet to be ready
    function tryInitializeMap(attempts = 0) {
        if (typeof L === 'undefined') {
            if (attempts < 10) {
                setTimeout(() => tryInitializeMap(attempts + 1), 200);
            } else {
                console.error('Leaflet library failed to load');
                alert('خطأ في تحميل مكتبة الخرائط / Map library failed to load');
            }
            return;
        }

        initializeMap();
        // Force map to refresh its size after initialization
        setTimeout(function() {
            if (propertyMap) {
                propertyMap.invalidateSize();
            }
        }, 200);
    }

    setTimeout(tryInitializeMap, 500);

    // Cascading dropdowns for location
    $('#governorate').on('change', function() {
        var governorateId = $(this).val();
        var citySelect = $('#city');
        var neighborhoodSelect = $('#neighborhood');
        
        // Reset city and neighborhood dropdowns
        citySelect.html('<option value="">Loading...</option>').prop('disabled', true);
        neighborhoodSelect.html('<option value="">Select state first</option>').prop('disabled', true);
        
        if (governorateId) {
            $.ajax({
                url: '{{ route('admin.properties.cities-by-state') }}',
                type: 'GET',
                data: { governorate_id: governorateId },
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
            citySelect.html('<option value="">Select governorate first</option>').prop('disabled', true);
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

    // Handle price_type options based on listing_type
    function updatePriceTypeOptions() {
        var listingType = $('#listing_type').val();
        var priceTypeSelect = $('#price_type');
        
        // Hide all options first
        priceTypeSelect.find('option').hide();
        
        // Always show the default option
        priceTypeSelect.find('option[value=""]').show();
        
        // Always show negotiation options for all listing types
        priceTypeSelect.find('.negotiation-option').show();
        
        if (listingType === 'rent') {
            // Show rent options
            priceTypeSelect.find('.rent-option').show();
            // Set default to monthly if no value selected
            if (!priceTypeSelect.val()) {
                priceTypeSelect.val('monthly');
            }
        } else if (listingType === 'sale') {
            // Show sale options
            priceTypeSelect.find('.sale-option').show();
            // Set default to total if no value selected
            if (!priceTypeSelect.val()) {
                priceTypeSelect.val('total');
            }
        }
    }
    
    // Initialize price type options on page load
    updatePriceTypeOptions();

    // Handle form validation for dependent dropdowns
    $('form').on('submit', function(e) {
        var governorateId = $('#governorate_id').val();
        var city = $('#city').val();
        var listingType = $('#listing_type').val();

        // Check listing type
        if (!listingType) {
            e.preventDefault();
            alert('Please select a listing type (For Sale or For Rent)');
            $('#listing_type').focus();
            return false;
        }

        // Check governorate and city dependency
        if (governorateId && !city) {
            e.preventDefault();
            alert('Please select a city');
            $('#city').focus();
            return false;
        }
        
        // Check price type is selected
        if (!$('#price_type').val()) {
            e.preventDefault();
            alert('Please select a price type');
            $('#price_type').focus();
            return false;
        }
        
        // Debug log before submission
        console.log('Form submission data:', {
            listing_type: listingType,
            price_type: $('#price_type').val(),
            state: state,
            city: city
        });
    });


});
</script>

<style>
.image-preview {
    transition: all 0.3s ease;
}

.image-preview:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.image-preview img {
    transition: all 0.3s ease;
    border-radius: 6px;
}

#imagePreview .col-md-4 {
    transition: all 0.3s ease;
}

#imagePreview .col-md-4:hover {
    transform: scale(1.02);
}

/* Map styling */
#propertyMap {
    border-radius: 8px !important;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.leaflet-container {
    background-color: #f8f9fa;
    border-radius: 8px;
}

.leaflet-control-zoom {
    border-radius: 6px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
}

.leaflet-control-attribution {
    background-color: rgba(255,255,255,0.9) !important;
    font-size: 10px !important;
}

.custom-location-marker {
    cursor: pointer;
}

.custom-location-marker:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}
</style>
@endpush