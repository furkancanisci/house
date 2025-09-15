@extends('admin.layouts.app')

@section('title', 'Create Property Type')

@section('content-header', 'Create New Property Type')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.property-types.index') }}">Property Types</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Property Type Information</h3>
                </div>
                <form method="POST" action="{{ route('admin.property-types.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Property Type Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_ar">Arabic Name</label>
                            <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                                   value="{{ old('name_ar') }}" placeholder="اسم نوع العقار بالعربية">
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_ku">Kurdish Kurmanci Name</label>
                            <input type="text" name="name_ku" id="name_ku" class="form-control @error('name_ku') is-invalid @enderror"
                                   value="{{ old('name_ku') }}" placeholder="Navê celeb xaniyê bi kurdî">
                            @error('name_ku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="icon">Icon (FontAwesome class)</label>
                                    <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror"
                                           value="{{ old('icon') }}" placeholder="e.g., fas fa-home">
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
                                           value="{{ old('sort_order', 0) }}" min="0">
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
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
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
                                       value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">
                                Only active property types will be available for selection
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Property Type
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
            <!-- Help Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Help</h3>
                </div>
                <div class="card-body">
                    <h6>Property Type Guidelines</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Use clear, descriptive names</li>
                        <li><i class="fas fa-check text-success"></i> Keep descriptions concise</li>
                        <li><i class="fas fa-check text-success"></i> Use consistent naming</li>
                        <li><i class="fas fa-check text-success"></i> Set appropriate sort orders</li>
                    </ul>

                    <h6 class="mt-3">Icon Examples</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-home"></i> <code>fas fa-home</code> - House/Villa</li>
                        <li><i class="fas fa-building"></i> <code>fas fa-building</code> - Apartment</li>
                        <li><i class="fas fa-warehouse"></i> <code>fas fa-warehouse</code> - Commercial</li>
                        <li><i class="fas fa-industry"></i> <code>fas fa-industry</code> - Industrial</li>
                        <li><i class="fas fa-map"></i> <code>fas fa-map</code> - Land</li>
                        <li><i class="fas fa-door-open"></i> <code>fas fa-door-open</code> - Studio</li>
                    </ul>

                    <small class="text-muted">
                        Visit <a href="https://fontawesome.com/icons" target="_blank">FontAwesome Icons</a>
                        for more icon options.
                    </small>
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
                            <i class="fas fa-home fa-2x"></i>
                        </div>
                        <h5 id="previewName">Property Type Name</h5>
                        <p id="previewDescription" class="text-muted">Property type description will appear here</p>
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
        $('#previewName').text(name || 'Property Type Name');
    });

    // Update description preview
    $('#description').on('input', function() {
        var description = $(this).val();
        $('#previewDescription').text(description || 'Property type description will appear here');
    });

    // Update icon preview
    $('#icon').on('input', function() {
        var icon = $(this).val();
        if (icon) {
            // Remove existing classes and add new ones
            var iconElement = $('#previewIcon i');
            iconElement.attr('class', icon + ' fa-2x');
        } else {
            $('#previewIcon i').attr('class', 'fas fa-home fa-2x');
        }
    });
});
</script>
@endpush