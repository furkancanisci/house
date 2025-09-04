@extends('admin.layouts.app')

@section('title', 'Edit Utility')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.utilities.index') }}">Utilities</a></li>
    <li class="breadcrumb-item active">Edit</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Utility: {{ $utility->name_en }}</h3>
            </div>

            <form method="POST" action="{{ route('admin.utilities.update', $utility) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name_en">Name (English) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name_en') is-invalid @enderror" 
                                       id="name_en" name="name_en" value="{{ old('name_en', $utility->name_en) }}" required>
                                @error('name_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name_ar">Name (Arabic) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                       id="name_ar" name="name_ar" value="{{ old('name_ar', $utility->name_ar) }}" required dir="rtl">
                                @error('name_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name_ku">Name (Kurdish) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name_ku') is-invalid @enderror" 
                                       id="name_ku" name="name_ku" value="{{ old('name_ku', $utility->name_ku) }}" required>
                                @error('name_ku')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                       id="category" name="category" value="{{ old('category', $utility->category) }}" 
                                       placeholder="e.g., Basic, Premium, Infrastructure">
                                @error('category')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Optional: Group similar utilities together</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon">Icon Class</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                       id="icon" name="icon" value="{{ old('icon', $utility->icon) }}" 
                                       placeholder="e.g., fas fa-bolt, fas fa-water">
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
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $utility->sort_order ?? 0) }}" min="0">
                                @error('sort_order')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Lower numbers appear first</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-control @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', $utility->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $utility->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="description_en">Description (English)</label>
                                <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                          id="description_en" name="description_en" rows="3" 
                                          placeholder="Optional description in English">{{ old('description_en', $utility->description_en) }}</textarea>
                                @error('description_en')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="description_ar">Description (Arabic)</label>
                                <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                          id="description_ar" name="description_ar" rows="3" 
                                          placeholder="Optional description in Arabic" dir="rtl">{{ old('description_ar', $utility->description_ar) }}</textarea>
                                @error('description_ar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="description_ku">Description (Kurdish)</label>
                                <textarea class="form-control @error('description_ku') is-invalid @enderror" 
                                          id="description_ku" name="description_ku" rows="3" 
                                          placeholder="Optional description in Kurdish">{{ old('description_ku', $utility->description_ku) }}</textarea>
                                @error('description_ku')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Utility
                    </button>
                    <a href="{{ route('admin.utilities.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <a href="{{ route('admin.utilities.show', $utility) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Utility Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>{{ $utility->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $utility->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $utility->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Properties:</strong></td>
                        <td>
                            <span class="badge badge-secondary">{{ $utility->properties()->count() }}</span>
                            @if($utility->properties()->count() > 0)
                                <small class="text-muted d-block">This utility is used by properties</small>
                            @endif
                        </td>
                    </tr>
                </table>

                @if($utility->icon)
                <div class="mt-3">
                    <strong>Current Icon:</strong><br>
                    <i class="{{ $utility->icon }} fa-2x"></i>
                    <small class="d-block text-muted">{{ $utility->icon }}</small>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Help</h3>
            </div>
            <div class="card-body">
                <h6>Utility Guidelines:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> English, Arabic, and Kurdish names are required</li>
                    <li><i class="fas fa-check text-success"></i> Use clear, descriptive names</li>
                    <li><i class="fas fa-check text-success"></i> Group similar utilities with categories</li>
                    <li><i class="fas fa-check text-success"></i> Use FontAwesome icons for better UI</li>
                    <li><i class="fas fa-check text-success"></i> Slug is auto-generated from English name</li>
                </ul>

                <h6 class="mt-3">Popular Categories:</h6>
                <div class="d-flex flex-wrap">
                    <span class="badge badge-info mr-1 mb-1">Basic</span>
                    <span class="badge badge-info mr-1 mb-1">Premium</span>
                    <span class="badge badge-info mr-1 mb-1">Infrastructure</span>
                    <span class="badge badge-info mr-1 mb-1">Services</span>
                    <span class="badge badge-info mr-1 mb-1">Connectivity</span>
                </div>

                <h6 class="mt-3">Icon Examples:</h6>
                <div class="small">
                    <div><code>fas fa-bolt</code> - <i class="fas fa-bolt"></i> Electricity</div>
                    <div><code>fas fa-water</code> - <i class="fas fa-water"></i> Water</div>
                    <div><code>fas fa-fire</code> - <i class="fas fa-fire"></i> Gas</div>
                    <div><code>fas fa-wifi</code> - <i class="fas fa-wifi"></i> Internet</div>
                    <div><code>fas fa-phone</code> - <i class="fas fa-phone"></i> Phone</div>
                    <div><code>fas fa-tv</code> - <i class="fas fa-tv"></i> Cable TV</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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