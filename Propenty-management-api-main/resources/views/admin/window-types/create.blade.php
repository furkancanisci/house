@extends('admin.layouts.app')

@section('title', __('Add New Window Type'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Add New Window Type') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.window-types.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('admin.window-types.store') }}" method="POST" id="window-type-form">
                    @csrf
                    
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
                                           value="{{ old('name_en') }}" 
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
                                           value="{{ old('name_ar') }}" 
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
                            <!-- Value Field -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="value" class="required">{{ __('Value/Code') }}</label>
                                    <input type="text"
                                           class="form-control @error('value') is-invalid @enderror"
                                           id="value"
                                           name="value"
                                           value="{{ old('value') }}"
                                           placeholder="{{ __('Enter unique value/code (e.g., sliding, double_hung, casement)') }}"
                                           required>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Unique identifier for this window type (used internally, no spaces or special characters)') }}</small>
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
                                              placeholder="{{ __('Enter window type description in English (optional)') }}">{{ old('description_en') }}</textarea>
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
                                              dir="rtl">{{ old('description_ar') }}</textarea>
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
                                           value="{{ old('sort_order', 0) }}" 
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
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            <span class="switch-text-active">{{ __('Active') }}</span>
                                            <span class="switch-text-inactive">{{ __('Inactive') }}</span>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">{{ __('Only active window types will be available for selection') }}</small>
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
                                                    <strong id="preview-name-en">{{ __('Window Type Name') }}</strong>
                                                    <p id="preview-desc-en" class="text-muted">{{ __('Description will appear here') }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>{{ __('Arabic Version') }}</h6>
                                                <div class="preview-item" dir="rtl">
                                                    <strong id="preview-name-ar">{{ __('اسم نوع النافذة') }}</strong>
                                                    <p id="preview-desc-ar" class="text-muted">{{ __('سيظهر الوصف هنا') }}</p>
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
                                    <i class="fas fa-save"></i> {{ __('Create Window Type') }}
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> {{ __('Reset Form') }}
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
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
</style>
@endpush

@push('scripts')
<script>
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
        const value = $(this).val() || '{{ __('Description will appear here') }}';
        $('#preview-desc-en').text(value);
    });
    
    $('#description_ar').on('input', function() {
        const value = $(this).val() || '{{ __('سيظهر الوصف هنا') }}';
        $('#preview-desc-ar').text(value);
    });
    
    // Form validation
    $('#window-type-form').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const nameEn = $('#name_en').val().trim();
        const nameAr = $('#name_ar').val().trim();
        const value = $('#value').val().trim();

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

        if (!value) {
            $('#value').addClass('is-invalid');
            isValid = false;
        } else {
            $('#value').removeClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('{{ __('Please fill in all required fields') }}');
            return false;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> {{ __('Creating...') }}');
    });
    
    // Auto-generate sort order
    if ($('#sort_order').val() == '0') {
        // You could make an AJAX call here to get the next sort order
        // For now, we'll just use a default value
    }
});

// Reset form function
function resetForm() {
    Swal.fire({
        title: '{{ __('Reset Form') }}',
        text: '{{ __('Are you sure you want to reset all fields?') }}',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '{{ __('Yes, reset it!') }}',
        cancelButtonText: '{{ __('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('window-type-form').reset();
            
            // Reset preview
            $('#preview-name-en').text('{{ __('Window Type Name') }}');
            $('#preview-name-ar').text('{{ __('اسم نوع النافذة') }}');
            $('#preview-desc-en').text('{{ __('Description will appear here') }}');
            $('#preview-desc-ar').text('{{ __('سيظهر الوصف هنا') }}');
            
            // Remove validation classes
            $('.is-invalid').removeClass('is-invalid');
            
            toastr.success('{{ __('Form has been reset') }}');
        }
    });
}

// Auto-save draft functionality (optional)
let autoSaveTimer;
function autoSaveDraft() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        const formData = {
            name_en: $('#name_en').val(),
            name_ar: $('#name_ar').val(),
            value: $('#value').val(),
            description_en: $('#description_en').val(),
            description_ar: $('#description_ar').val(),
            sort_order: $('#sort_order').val(),
            is_active: $('#is_active').is(':checked')
        };
        
        // Save to localStorage
        localStorage.setItem('window_type_draft', JSON.stringify(formData));
    }, 2000);
}

// Bind auto-save to form inputs
$('#window-type-form input, #window-type-form textarea').on('input change', autoSaveDraft);

// Load draft on page load
$(document).ready(function() {
    const draft = localStorage.getItem('window_type_draft');
    if (draft && !$('#name_en').val()) {
        const data = JSON.parse(draft);
        
        Swal.fire({
            title: '{{ __('Draft Found') }}',
            text: '{{ __('Would you like to restore your previous draft?') }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ __('Yes, restore it') }}',
            cancelButtonText: '{{ __('No, start fresh') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#name_en').val(data.name_en).trigger('input');
                $('#name_ar').val(data.name_ar).trigger('input');
                $('#value').val(data.value);
                $('#description_en').val(data.description_en).trigger('input');
                $('#description_ar').val(data.description_ar).trigger('input');
                $('#sort_order').val(data.sort_order);
                $('#is_active').prop('checked', data.is_active);
                
                toastr.success('{{ __('Draft restored successfully') }}');
            } else {
                localStorage.removeItem('window_type_draft');
            }
        });
    }
});

// Clear draft on successful submission
$('#window-type-form').on('submit', function() {
    localStorage.removeItem('window_type_draft');
});
</script>
@endpush