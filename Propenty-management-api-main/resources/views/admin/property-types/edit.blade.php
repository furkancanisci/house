@extends('admin.layouts.app')

@section('title', 'Edit Property Type')

@section('content-header', 'Edit Property Type')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.property-types.index') }}">Property Types</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Property Type: {{ $propertyType->name }}</h3>
                </div>
                <form method="POST" action="{{ route('admin.property-types.update', $propertyType) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Property Type Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $propertyType->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_ar">Arabic Name</label>
                            <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                                   value="{{ old('name_ar', $propertyType->name_ar) }}" placeholder="اسم نوع العقار بالعربية">
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_ku">Kurdish Kurmanci Name</label>
                            <input type="text" name="name_ku" id="name_ku" class="form-control @error('name_ku') is-invalid @enderror"
                                   value="{{ old('name_ku', $propertyType->name_ku) }}" placeholder="Navê celeb xaniyê bi kurdî">
                            @error('name_ku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $propertyType->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="icon">Icon (FontAwesome class)</label>
                                    <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror"
                                           value="{{ old('icon', $propertyType->icon) }}" placeholder="e.g., fas fa-home">
                                    <small class="form-text text-muted">
                                        FontAwesome icon class (e.g., fas fa-home, fas fa-building)
                                    </small>
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                           value="{{ old('sort_order', $propertyType->sort_order) }}" min="0">
                                    <small class="form-text text-muted">
                                        Lower numbers appear first in listings
                                    </small>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($parentTypes->count() > 0)
                        <div class="form-group">
                            <label for="parent_id">Parent Type</label>
                            <select name="parent_id" id="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                                <option value="">None (Root Category)</option>
                                @foreach($parentTypes as $parent)
                                    <option value="{{ $parent->id }}"
                                            {{ old('parent_id', $propertyType->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->getPreferredName() }}
                                        @if($parent->getPreferredName() !== $parent->name && $parent->name)
                                            ({{ $parent->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Select a parent type to create a subcategory
                            </small>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input"
                                       value="1" {{ old('is_active', $propertyType->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">
                                Only active property types will be available for selection
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Property Type
                        </button>
                        <a href="{{ route('admin.property-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Current Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Current Information</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        {!! $propertyType->icon_html !!}
                        <h5>{{ $propertyType->name }}</h5>
                        @if($propertyType->description)
                            <p class="text-muted">{{ $propertyType->description }}</p>
                        @endif
                        <small class="text-info">{{ $propertyType->slug }}</small>
                    </div>

                    <hr>

                    <strong>Properties Count:</strong> {{ $propertyType->properties()->count() }}<br>
                    <strong>Children Count:</strong> {{ $propertyType->children()->count() }}<br>
                    <strong>Status:</strong>
                    <span class="badge badge-{{ $propertyType->is_active ? 'success' : 'secondary' }}">
                        {{ $propertyType->is_active ? 'Active' : 'Inactive' }}
                    </span><br>
                    <strong>Sort Order:</strong> {{ $propertyType->sort_order }}<br>
                    @if($propertyType->parent)
                        <strong>Parent:</strong> {{ $propertyType->parent->name }}<br>
                    @endif
                    <strong>Created:</strong> {{ $propertyType->created_at->format('M j, Y') }}
                </div>
            </div>

            <!-- Preview Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Preview</h3>
                </div>
                <div class="card-body">
                    <div id="propertyTypePreview" class="text-center">
                        <div id="previewIcon" class="mb-2">
                            <i class="{{ $propertyType->icon ?: 'fas fa-home' }} fa-2x"></i>
                        </div>
                        <h5 id="previewName">{{ $propertyType->name }}</h5>
                        <p id="previewDescription" class="text-muted">{{ $propertyType->description ?: 'Property type description will appear here' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update preview name
    $('#name').on('input', function() {
        var name = $(this).val();
        $('#previewName').text(name || '{{ $propertyType->name }}');
    });

    // Update description preview
    $('#description').on('input', function() {
        var description = $(this).val();
        $('#previewDescription').text(description || '{{ $propertyType->description ?: "Property type description will appear here" }}');
    });

    // Update icon preview
    $('#icon').on('input', function() {
        var icon = $(this).val();
        if (icon) {
            // Remove existing classes and add new ones
            var iconElement = $('#previewIcon i');
            iconElement.attr('class', icon + ' fa-2x');
        } else {
            $('#previewIcon i').attr('class', '{{ $propertyType->icon ?: "fas fa-home" }} fa-2x');
        }
    });
});
</script>
@endpush