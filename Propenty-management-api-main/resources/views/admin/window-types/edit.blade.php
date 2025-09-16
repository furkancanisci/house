@extends('admin.layouts.app')

@section('title', __('Edit Window Type'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Edit Window Type') }}: {{ $windowType->name_en }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.window-types.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <a href="{{ route('admin.window-types.show', $windowType) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> {{ __('View Details') }}
                        </a>
                    </div>
                </div>
                
                <!-- Statistics Row -->
                <div class="card-body border-bottom">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-home"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Properties') }}</span>
                                    <span class="info-box-number">{{ $windowType->properties_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Created') }}</span>
                                    <span class="info-box-number">{{ $windowType->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Last Updated') }}</span>
                                    <span class="info-box-number">{{ $windowType->updated_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-{{ $windowType->is_active ? 'success' : 'danger' }}">
                                <span class="info-box-icon"><i class="fas fa-{{ $windowType->is_active ? 'check' : 'times' }}"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Status') }}</span>
                                    <span class="info-box-number">{{ $windowType->is_active ? __('Active') : __('Inactive') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('admin.window-types.update', $windowType) }}" method="POST" id="window-type-form">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6><i class="fas fa-exclamation-triangle"></i> {{ __('Please fix the following errors:') }}</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        
                        <div class="row">
                            <!-- English Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_en" class="required">{{ __('Name (English)') }}</label>
                                    <input type="text" 
                                           class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" 
                                           name="name_en" 
                                           value="{{ old('name_en', $windowType->name_en) }}" 
                                           placeholder="{{ __('Enter window type name in English') }}"
                                           required>
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Enter the window type name in English') }}</small>
                                </div>
                            </div>
                            
                            <!-- Arabic Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_ar" class="required">{{ __('Name (Arabic)') }}</label>
                                    <input type="text" 
                                           class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" 
                                           name="name_ar" 
                                           value="{{ old('name_ar', $windowType->name_ar) }}" 
                                           placeholder="{{ __('Enter window type name in Arabic') }}"
                                           dir="rtl"
                                           required>
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Enter the window type name in Arabic') }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- English Description -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description_en">{{ __('Description (English)') }}</label>
                                    <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                              id="description_en" 
                                              name="description_en" 
                                              rows="4"
                                              placeholder="{{ __('Enter window type description in English (optional)') }}">{{ old('description_en', $windowType->description_en) }}</textarea>
                                    @error('description_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Optional description in English') }}</small>
                                </div>
                            </div>
                            
                            <!-- Arabic Description -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description_ar">{{ __('Description (Arabic)') }}</label>
                                    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                              id="description_ar" 
                                              name="description_ar" 
                                              rows="4"
                                              placeholder="{{ __('Enter window type description in Arabic (optional)') }}"
                                              dir="rtl">{{ old('description_ar', $windowType->description_ar) }}</textarea>
                                    @error('description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Optional description in Arabic') }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Sort Order -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">{{ __('Sort Order') }}</label>
                                    <input type="number" 
                                           class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" 
                                           name="sort_order" 
                                           value="{{ old('sort_order', $windowType->sort_order) }}" 
                                           min="0"
                                           step="1">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Lower numbers appear first in lists') }}</small>
                                </div>
                            </div>
                            
                            <!-- Status -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">{{ __('Status') }}</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', $windowType->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            <span class="switch-text-active">{{ __('Active') }}</span>
                                            <span class="switch-text-inactive">{{ __('Inactive') }}</span>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">{{ __('Only active window types will be available for selection') }}</small>
                                    @if($windowType->properties_count > 0 && $windowType->is_active)
                                        <div class="alert alert-warning mt-2">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ __('Warning: This window type is used by :count properties. Deactivating it may affect existing properties.', ['count' => $windowType->properties_count]) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">{{ __('Preview') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>{{ __('English Version') }}</h6>
                                                <div class="preview-item">
                                                    <strong id="preview-name-en">{{ $windowType->name_en }}</strong>
                                                    <p id="preview-desc-en" class="text-muted">{{ $windowType->description_en ?: __('No description') }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>{{ __('Arabic Version') }}</h6>
                                                <div class="preview-item" dir="rtl">
                                                    <strong id="preview-name-ar">{{ $windowType->name_ar }}</strong>
                                                    <p id="preview-desc-ar" class="text-muted">{{ $windowType->description_ar ?: __('لا يوجد وصف') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Update Window Type') }}
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> {{ __('Reset Changes') }}
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i> {{ __('Delete') }}
                                </button>
                                <a href="{{ route('admin.window-types.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> {{ __('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="{{ route('admin.window-types.destroy', $windowType) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
.required::after {
    content: ' *';
    color: red;
}

.preview-item {
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    background-color: #f8f9fa;
    min-height: 80px;
}

.switch-text-active,
.switch-text-inactive {
    font-size: 0.875rem;
}

.custom-control-input:checked ~ .custom-control-label .switch-text-inactive,
.custom-control-input:not(:checked) ~ .custom-control-label .switch-text-active {
    display: none;
}

.info-box {
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
<script>
// Store original values for reset functionality
const originalValues = {
    name_en: '{{ $windowType->name_en }}',
    name_ar: '{{ $windowType->name_ar }}',
    description_en: '{{ $windowType->description_en }}',
    description_ar: '{{ $windowType->description_ar }}',
    sort_order: '{{ $windowType->sort_order }}',
    is_active: {{ $windowType->is_active ? 'true' : 'false' }}
};

$(document).ready(function() {
    // Real-time preview updates
    $('#name_en').on('input', function() {
        const value = $(this).val() || '{{ __('Window Type Name') }}';
        $('#preview-name-en').text(value);
    });
    
    $('#name_ar').on('input', function() {
        const value = $(this).val() || '{{ __('اسم نوع النافذة') }}';
        $('#preview-name-ar').text(value);
    });
    
    $('#description_en').on('input', function() {
        const value = $(this).val() || '{{ __('No description') }}';
        $('#preview-desc-en').text(value);
    });
    
    $('#description_ar').on('input', function() {
        const value = $(this).val() || '{{ __('لا يوجد وصف') }}';
        $('#preview-desc-ar').text(value);
    });
    
    // Form validation
    $('#window-type-form').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const nameEn = $('#name_en').val().trim();
        const nameAr = $('#name_ar').val().trim();
        
        if (!nameEn) {
            $('#name_en').addClass('is-invalid');
            isValid = false;
        } else {
            $('#name_en').removeClass('is-invalid');
        }
        
        if (!nameAr) {
            $('#name_ar').addClass('is-invalid');
            isValid = false;
        } else {
            $('#name_ar').removeClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('{{ __('Please fill in all required fields') }}');
            return false;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> {{ __('Updating...') }}');
    });
    
    // Check for unsaved changes
    let hasUnsavedChanges = false;
    
    $('#window-type-form input, #window-type-form textarea').on('input change', function() {
        hasUnsavedChanges = true;
    });
    
    // Warn before leaving page with unsaved changes
    $(window).on('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            const message = '{{ __('You have unsaved changes. Are you sure you want to leave?') }}';
            e.returnValue = message;
            return message;
        }
    });
    
    // Remove warning when form is submitted
    $('#window-type-form').on('submit', function() {
        hasUnsavedChanges = false;
    });
});

// Reset form function
function resetForm() {
    Swal.fire({
        title: '{{ __('Reset Changes') }}',
        text: '{{ __('Are you sure you want to reset all changes to their original values?') }}',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '{{ __('Yes, reset changes!') }}',
        cancelButtonText: '{{ __('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            // Reset to original values
            $('#name_en').val(originalValues.name_en).trigger('input');
            $('#name_ar').val(originalValues.name_ar).trigger('input');
            $('#description_en').val(originalValues.description_en).trigger('input');
            $('#description_ar').val(originalValues.description_ar).trigger('input');
            $('#sort_order').val(originalValues.sort_order);
            $('#is_active').prop('checked', originalValues.is_active);
            
            // Remove validation classes
            $('.is-invalid').removeClass('is-invalid');
            
            hasUnsavedChanges = false;
            toastr.success('{{ __('Changes have been reset') }}');
        }
    });
}

// Delete confirmation
function confirmDelete() {
    @if($windowType->properties_count > 0)
        Swal.fire({
            title: '{{ __('Cannot Delete') }}',
            text: '{{ __('This window type is used by :count properties and cannot be deleted. Please remove it from all properties first.', ['count' => $windowType->properties_count]) }}',
            icon: 'error',
            confirmButtonText: '{{ __('OK') }}'
        });
    @else
        Swal.fire({
            title: '{{ __('Are you sure?') }}',
            text: '{{ __('This action cannot be undone!') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{ __('Yes, delete it!') }}',
            cancelButtonText: '{{ __('Cancel') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                hasUnsavedChanges = false;
                document.getElementById('delete-form').submit();
            }
        });
    @endif
}

// Auto-save functionality (optional)
let autoSaveTimer;
function autoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        // You could implement auto-save to drafts here
        console.log('Auto-save triggered');
    }, 5000);
}

// Bind auto-save to form inputs
$('#window-type-form input, #window-type-form textarea').on('input change', autoSave);
</script>
@endpush