@extends('admin.layouts.app')

@section('title', 'Create Amenity')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.amenities.index') }}">Amenities</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create New Amenity</h3>
            </div>

            <form action="{{ route('admin.amenities.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Display Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">The main display name for the amenity</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name_en">English Name *</label>
                                <input type="text" name="name_en" id="name_en" class="form-control @error('name_en') is-invalid @enderror" 
                                       value="{{ old('name_en') }}" required>
                                @error('name_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name_ar">Arabic Name *</label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror" 
                                       value="{{ old('name_ar') }}" required dir="rtl">
                                @error('name_ar')
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
                        <small class="form-text text-muted">URL-friendly version (e.g., swimming-pool, parking)</small>
                    </div>

                    <div class="form-group">
                        <label for="icon">Font Awesome Icon</label>
                        <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror" 
                               value="{{ old('icon') }}" placeholder="e.g., fas fa-swimming-pool">
                        @error('icon')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">FontAwesome icon class (optional)</small>
                        <div id="iconPreview" class="mt-2"></div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Optional description for the amenity</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                        <small class="form-text text-muted">Only active amenities will be visible to users</small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Amenity
                    </button>
                    <a href="{{ route('admin.amenities.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Common Icons</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center mb-3">
                        <i class="fas fa-swimming-pool fa-2x text-primary"></i>
                        <br><small><code>fas fa-swimming-pool</code></small>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <i class="fas fa-car fa-2x text-primary"></i>
                        <br><small><code>fas fa-car</code></small>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <i class="fas fa-wifi fa-2x text-primary"></i>
                        <br><small><code>fas fa-wifi</code></small>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <i class="fas fa-dumbbell fa-2x text-primary"></i>
                        <br><small><code>fas fa-dumbbell</code></small>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <i class="fas fa-shield-alt fa-2x text-primary"></i>
                        <br><small><code>fas fa-shield-alt</code></small>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <i class="fas fa-tree fa-2x text-primary"></i>
                        <br><small><code>fas fa-tree</code></small>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <i class="fas fa-snowflake fa-2x text-primary"></i>
                        <br><small><code>fas fa-snowflake</code></small>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <i class="fas fa-fire fa-2x text-primary"></i>
                        <br><small><code>fas fa-fire</code></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Use clear, descriptive names</li>
                    <li><i class="fas fa-check text-success"></i> Provide both English and Arabic names</li>
                    <li><i class="fas fa-check text-success"></i> Choose appropriate FontAwesome icons</li>
                    <li><i class="fas fa-check text-success"></i> Keep slugs lowercase and URL-friendly</li>
                    <li><i class="fas fa-check text-success"></i> Add helpful descriptions when needed</li>
                </ul>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Tip:</strong> Amenities help users filter properties based on their preferences and needs.
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

    // Icon preview
    $('#icon').on('input', function() {
        let iconClass = $(this).val().trim();
        let preview = $('#iconPreview');
        
        if (iconClass) {
            preview.html('<i class="' + iconClass + ' fa-2x text-primary"></i> <span class="ml-2">Preview</span>');
        } else {
            preview.html('');
        }
    });

    // Click on example icons to use them
    $('.card-body i[class*="fa-"]').on('click', function() {
        let iconClass = $(this).attr('class').replace(' fa-2x text-primary', '');
        $('#icon').val(iconClass).trigger('input');
    });
});
</script>
@endpush