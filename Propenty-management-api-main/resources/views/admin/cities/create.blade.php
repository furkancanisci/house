@extends('admin.layouts.app')

@section('title', 'Create City')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.cities.index') }}">Cities</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create New City</h3>
            </div>

            <form action="{{ route('admin.cities.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Display Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">The main display name for the city</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name_en">English Name *</label>
                                <input type="text" name="name_en" id="name_en" class="form-control @error('name_en') is-invalid @enderror" 
                                       value="{{ old('name_en') }}" required>
                                @error('name_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name_ar">Arabic Name *</label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror" 
                                       value="{{ old('name_ar') }}" required dir="rtl">
                                @error('name_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name_ku">Kurdish Name</label>
                                <input type="text" name="name_ku" id="name_ku" class="form-control @error('name_ku') is-invalid @enderror" 
                                       value="{{ old('name_ku') }}">
                                @error('name_ku')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="slug">URL Slug *</label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" 
                               value="{{ old('slug') }}" required>
                        @error('slug')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">URL-friendly version (e.g., new-york, london)</small>
                    </div>

                    <div class="form-group">
                        <label for="governorate_id">Governorate</label>
                        <select name="governorate_id" id="governorate_id" class="form-control @error('governorate_id') is-invalid @enderror">
                            <option value="">Select Governorate (Optional)</option>
                            @foreach($governorates as $governorate)
                                <option value="{{ $governorate->id }}" 
                                        {{ old('governorate_id', $selectedGovernorate) == $governorate->id ? 'selected' : '' }}>
                                    {{ $governorate->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('governorate_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Select the governorate this city belongs to</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="latitude">Latitude</label>
                                <input type="number" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" 
                                       value="{{ old('latitude') }}" step="any" min="-90" max="90">
                                @error('latitude')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Geographic latitude (-90 to 90)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="longitude">Longitude</label>
                                <input type="number" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" 
                                       value="{{ old('longitude') }}" step="any" min="-180" max="180">
                                @error('longitude')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Geographic longitude (-180 to 180)</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                        <small class="form-text text-muted">Only active cities will be visible to users</small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create City
                    </button>
                    <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Use clear, recognizable city names</li>
                    <li><i class="fas fa-check text-success"></i> Provide both English and Arabic names</li>
                    <li><i class="fas fa-info text-info"></i> Kurdish name is optional for multilingual support</li>
                    <li><i class="fas fa-check text-success"></i> Keep slugs lowercase and URL-friendly</li>
                    <li><i class="fas fa-check text-success"></i> Verify uniqueness before saving</li>
                </ul>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Tip:</strong> After creating a city, you can add neighborhoods to organize properties more precisely.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate slug from English name
    $('#name_en').on('input', function() {
        let slug = $(this).val()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
        $('#slug').val(slug);
    });

    // Copy display name from English name if empty
    $('#name_en').on('input', function() {
        if ($('#name').val() === '') {
            $('#name').val($(this).val());
        }
    });
});
</script>
@endpush