@extends('admin.layouts.app')

@section('title', 'Edit Category')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Category: {{ $category->name }}</h3>
            </div>

            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $category->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug', $category->slug) }}" 
                                       placeholder="Auto-generated if empty">
                                @error('slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">URL-friendly version of the name</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name_ar">Arabic Name</label>
                                <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                       id="name_ar" name="name_ar" value="{{ old('name_ar', $category->name_ar) }}" 
                                       placeholder="اسم الفئة بالعربية">
                                @error('name_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name_ku">Kurdish Kurmanci Name</label>
                                <input type="text" class="form-control @error('name_ku') is-invalid @enderror" 
                                       id="name_ku" name="name_ku" value="{{ old('name_ku', $category->name_ku) }}" 
                                       placeholder="Navê kategoriyê bi kurdî">
                                @error('name_ku')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_id">Parent Category</label>
                                <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                    <option value="">-- Root Category --</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}" 
                                                {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->getPreferredName() }}
                                            @if($parent->getPreferredName() !== $parent->name && $parent->name)
                                                ({{ $parent->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Optional: Select a parent category</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon">Icon Class</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                       id="icon" name="icon" value="{{ old('icon', $category->icon) }}" 
                                       placeholder="e.g., fas fa-home, fas fa-building">
                                @error('icon')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Optional: FontAwesome icon class</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
                                @error('sort_order')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Lower numbers appear first</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" 
                                           value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="form-text text-muted">
                                    Only active categories will be available for selection
                                </small>
                                @error('is_active')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Optional description for this category">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Category Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>{{ $category->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Current Slug:</strong></td>
                        <td><code>{{ $category->slug }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $category->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $category->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Properties:</strong></td>
                        <td>
                            <span class="badge badge-secondary">{{ $category->properties()->count() }}</span>
                            @if($category->properties()->count() > 0)
                                <small class="text-muted d-block">This category is used by properties</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Child Categories:</strong></td>
                        <td>
                            <span class="badge badge-info">{{ $category->children()->count() }}</span>
                            @if($category->children()->count() > 0)
                                <small class="text-muted d-block">Has subcategories</small>
                            @endif
                        </td>
                    </tr>
                </table>

                @if($category->icon)
                <div class="mt-3">
                    <strong>Current Icon:</strong><br>
                    <i class="{{ $category->icon }} fa-2x"></i>
                    <small class="d-block text-muted">{{ $category->icon }}</small>
                </div>
                @endif

                @if($category->parent)
                <div class="mt-3">
                    <strong>Parent Category:</strong><br>
                    <a href="{{ route('admin.categories.show', $category->parent) }}" class="badge badge-info">
                        {{ $category->parent->getPreferredName() }}
                        @if($category->parent->getPreferredName() !== $category->parent->name && $category->parent->name)
                            <small class="text-muted">({{ $category->parent->name }})</small>
                        @endif
                    </a>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Help</h3>
            </div>
            <div class="card-body">
                <h6>Category Guidelines:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Use clear, descriptive names</li>
                    <li><i class="fas fa-check text-success"></i> Slug is auto-generated if empty</li>
                    <li><i class="fas fa-check text-success"></i> Parent categories create hierarchy</li>
                    <li><i class="fas fa-check text-success"></i> Use FontAwesome icons for better UI</li>
                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Cannot delete categories with properties</li>
                </ul>

                <h6 class="mt-3">Common Categories:</h6>
                <div class="d-flex flex-wrap">
                    <span class="badge badge-info mr-1 mb-1">Residential</span>
                    <span class="badge badge-info mr-1 mb-1">Commercial</span>
                    <span class="badge badge-info mr-1 mb-1">Industrial</span>
                    <span class="badge badge-info mr-1 mb-1">Land</span>
                    <span class="badge badge-info mr-1 mb-1">Apartment</span>
                    <span class="badge badge-info mr-1 mb-1">Villa</span>
                </div>

                <h6 class="mt-3">Icon Examples:</h6>
                <div class="small">
                    <div><code>fas fa-home</code> - <i class="fas fa-home"></i> Residential</div>
                    <div><code>fas fa-building</code> - <i class="fas fa-building"></i> Commercial</div>
                    <div><code>fas fa-industry</code> - <i class="fas fa-industry"></i> Industrial</div>
                    <div><code>fas fa-map</code> - <i class="fas fa-map"></i> Land</div>
                </div>
            </div>
        </div>

        @if($category->children()->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Child Categories ({{ $category->children()->count() }})</h3>
            </div>
            <div class="card-body">
                @foreach($category->children as $child)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <a href="{{ route('admin.categories.show', $child) }}" class="font-weight-bold">
                            {{ $child->getPreferredName() }}
                            @if($child->getPreferredName() !== $child->name && $child->name)
                                <small class="text-muted">({{ $child->name }})</small>
                            @endif
                        </a>
                        <small class="text-muted d-block">{{ $child->properties()->count() }} properties</small>
                    </div>
                    <div>
                        @if($child->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
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
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
        
        if (!$('#slug').is(':focus')) {
            $('#slug').val(slug);
        }
    });

    // Icon preview
    $('#icon').on('input', function() {
        var iconClass = $(this).val();
        var preview = $('#iconPreview');
        
        if (iconClass) {
            if (preview.length === 0) {
                $(this).after('<div id="iconPreview" class="mt-1"><small>Preview: <i class="' + iconClass + '"></i></small></div>');
            } else {
                preview.html('<small>Preview: <i class="' + iconClass + '"></i></small>');
            }
        } else {
            preview.remove();
        }
    });

    // Trigger icon preview on page load if there's a value
    if ($('#icon').val()) {
        $('#icon').trigger('input');
    }
});
</script>
@endpush