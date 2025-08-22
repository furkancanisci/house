@extends('admin.layouts.app')

@section('title', 'Edit Amenity')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.amenities.index') }}">Amenities</a></li>
    <li class="breadcrumb-item active">Edit {{ $amenity->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Amenity: {{ $amenity->name }}</h3>
            </div>

            <form action="{{ route('admin.amenities.update', $amenity) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Display Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $amenity->name) }}" required>
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
                                       value="{{ old('name_en', $amenity->name_en) }}" required>
                                @error('name_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name_ar">Arabic Name *</label>
                                <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror" 
                                       value="{{ old('name_ar', $amenity->name_ar) }}" required dir="rtl">
                                @error('name_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="slug">URL Slug *</label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" 
                               value="{{ old('slug', $amenity->slug) }}" required>
                        @error('slug')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">URL-friendly version (e.g., swimming-pool, parking)</small>
                    </div>

                    <div class="form-group">
                        <label for="icon">Font Awesome Icon</label>
                        <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror" 
                               value="{{ old('icon', $amenity->icon) }}" placeholder="e.g., fas fa-swimming-pool">
                        @error('icon')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">FontAwesome icon class (optional)</small>
                        <div id="iconPreview" class="mt-2">
                            @if($amenity->icon)
                                <i class="{{ $amenity->icon }} fa-2x text-primary"></i> <span class="ml-2">Current Preview</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $amenity->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Optional description for the amenity</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $amenity->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                        <small class="form-text text-muted">Only active amenities will be visible to users</small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Amenity
                    </button>
                    <a href="{{ route('admin.amenities.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <a href="{{ route('admin.amenities.show', $amenity) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Amenity Statistics</h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-home"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Properties</span>
                        <span class="info-box-number">{{ $amenity->properties()->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Created</span>
                        <span class="info-box-number">{{ $amenity->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

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
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                @if($amenity->properties()->count() == 0)
                <form action="{{ route('admin.amenities.destroy', $amenity) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block delete-amenity">
                        <i class="fas fa-trash"></i> Delete Amenity
                    </button>
                </form>
                @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Cannot delete amenity that is associated with {{ $amenity->properties()->count() }} properties.
                </div>
                @endif
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

    // Delete confirmation
    $('.delete-amenity').on('click', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush