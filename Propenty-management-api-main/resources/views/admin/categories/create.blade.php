@extends('admin.layouts.app')

@section('title', 'Create Category')

@section('content-header', 'Create New Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Category Information</h3>
                </div>
                <form method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_ar">Arabic Name</label>
                            <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror" 
                                   value="{{ old('name_ar') }}" placeholder="اسم الفئة بالعربية">
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name_ku">Kurdish Kurmanci Name</label>
                            <input type="text" name="name_ku" id="name_ku" class="form-control @error('name_ku') is-invalid @enderror" 
                                   value="{{ old('name_ku') }}" placeholder="Navê kategoriyê bi kurdî">
                            @error('name_ku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" 
                                   value="{{ old('slug') }}" placeholder="Auto-generated from name if left empty">
                            <small class="form-text text-muted">
                                URL-friendly version of the name. Leave empty to auto-generate.
                            </small>
                            @error('slug')
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

                        @if($parentCategories->count() > 0)
                        <div class="form-group">
                            <label for="parent_id">Parent Category</label>
                            <select name="parent_id" id="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                                <option value="">None (Root Category)</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->getPreferredName() }}
                                        @if($parent->getPreferredName() !== $parent->name && $parent->name)
                                            <small class="text-muted">({{ $parent->name }})</small>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Select a parent category to create a subcategory
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
                                Only active categories will be available for selection
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Category
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
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
                    <h6>Category Guidelines</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Use clear, descriptive names</li>
                        <li><i class="fas fa-check text-success"></i> Keep descriptions concise</li>
                        <li><i class="fas fa-check text-success"></i> Use consistent naming</li>
                        <li><i class="fas fa-check text-success"></i> Set appropriate sort orders</li>
                    </ul>

                    <h6 class="mt-3">Icon Examples</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-home"></i> <code>fas fa-home</code> - House</li>
                        <li><i class="fas fa-building"></i> <code>fas fa-building</code> - Apartment</li>
                        <li><i class="fas fa-warehouse"></i> <code>fas fa-warehouse</code> - Commercial</li>
                        <li><i class="fas fa-mountain"></i> <code>fas fa-mountain</code> - Land</li>
                        <li><i class="fas fa-city"></i> <code>fas fa-city</code> - Condo</li>
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
                    <div id="categoryPreview" class="text-center">
                        <div id="previewIcon" class="mb-2">
                            <i class="fas fa-home fa-2x"></i>
                        </div>
                        <h5 id="previewName">Category Name</h5>
                        <p id="previewDescription" class="text-muted">Category description will appear here</p>
                        <small id="previewSlug" class="text-info">category-slug</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate slug from name
    $('#name').on('input', function() {
        var name = $(this).val();
        var slug = name.toLowerCase()
            .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-') // collapse dashes
            .replace(/^-+/, '') // trim - from start of text
            .replace(/-+$/, ''); // trim - from end of text
        
        if ($('#slug').val() === '' || $('#slug').data('auto') !== false) {
            $('#slug').val(slug).data('auto', true);
        }
        
        // Update preview
        $('#previewName').text(name || 'Category Name');
        $('#previewSlug').text(slug || 'category-slug');
    });

    // Mark slug as manually edited
    $('#slug').on('input', function() {
        $(this).data('auto', false);
    });

    // Update description preview
    $('#description').on('input', function() {
        var description = $(this).val();
        $('#previewDescription').text(description || 'Category description will appear here');
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