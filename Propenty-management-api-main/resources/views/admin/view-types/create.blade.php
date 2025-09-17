@extends('admin.layouts.app')

@section('title', __('Add New View Type'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Add New View Type') }} / {{ __('إضافة نوع إطلالة جديد') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.view-types.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.view-types.store') }}" method="POST" id="view-type-form">
                    @csrf
                    <div class="card-body">
                        <!-- Success/Error Messages -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> {{ __('Please correct the following errors:') }}
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="row">
                            <!-- English Fields -->
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-flag-usa"></i> {{ __('English Information') }}
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="name_en" class="required">{{ __('Name (English)') }}</label>
                                            <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                                   id="name_en" name="name_en" value="{{ old('name_en') }}"
                                                   placeholder="{{ __('Enter view type name in English') }}" required>
                                            @error('name_en')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                {{ __('This will be displayed to English-speaking users') }}
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="description_en">{{ __('Description (English)') }}</label>
                                            <textarea class="form-control @error('description_en') is-invalid @enderror"
                                                      id="description_en" name="description_en" rows="4"
                                                      placeholder="{{ __('Enter detailed description in English') }}">{{ old('description_en') }}</textarea>
                                            @error('description_en')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <span id="description_en_count">0</span>/500 {{ __('characters') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Arabic Fields -->
                            <div class="col-md-6">
                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-flag"></i> {{ __('Arabic Information') }}
                                        </h3>
                                    </div>
                                    <div class="card-body" dir="rtl">
                                        <div class="form-group">
                                            <label for="name_ar" class="required">{{ __('Name (Arabic)') }}</label>
                                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                                   id="name_ar" name="name_ar" value="{{ old('name_ar') }}"
                                                   placeholder="{{ __('أدخل اسم نوع الإطلالة بالعربية') }}" required dir="rtl">
                                            @error('name_ar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                {{ __('سيتم عرض هذا للمستخدمين الناطقين بالعربية') }}
                                            </small>
                                        </div>

                                        <div class="form-group">
                                            <label for="description_ar">{{ __('Description (Arabic)') }}</label>
                                            <textarea class="form-control @error('description_ar') is-invalid @enderror"
                                                      id="description_ar" name="description_ar" rows="4"
                                                      placeholder="{{ __('أدخل وصف مفصل بالعربية') }}" dir="rtl">{{ old('description_ar') }}</textarea>
                                            @error('description_ar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <span id="description_ar_count">0</span>/500 {{ __('حرف') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Value Field -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card card-outline card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-code"></i> {{ __('System Information') }}
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="value" class="required">{{ __('Value/Code') }}</label>
                                            <input type="text"
                                                   class="form-control @error('value') is-invalid @enderror"
                                                   id="value"
                                                   name="value"
                                                   value="{{ old('value') }}"
                                                   placeholder="{{ __('Enter unique value/code (e.g., sea_view, mountain_view, garden_view)') }}"
                                                   required>
                                            @error('value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">{{ __('Unique identifier for this view type (used internally, no spaces or special characters)') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-cogs"></i> {{ __('Settings') }}
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="sort_order">{{ __('Sort Order') }}</label>
                                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                                                           min="0" max="999" step="1">
                                                    @error('sort_order')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        {{ __('Lower numbers appear first. Use 0 for highest priority.') }}
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="is_active">{{ __('Status') }}</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                               id="is_active" name="is_active" value="1"
                                                               {{ old('is_active', 1) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_active">
                                                            <span class="switch-text">{{ __('Active') }}</span>
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        {{ __('Only active view types will be available for selection') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card card-outline card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-eye"></i> {{ __('Preview') }}
                                        </h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5>{{ __('English Preview') }}</h5>
                                                <div class="preview-card">
                                                    <div class="mb-2">
                                                        <i class="fas fa-eye text-primary mr-2"></i>
                                                        <h6 id="preview_name_en" class="d-inline text-primary">{{ __('View Type Name') }}</h6>
                                                    </div>
                                                    <p id="preview_description_en" class="text-muted">{{ __('View type description will appear here') }}</p>
                                                    <small class="text-info">
                                                        <i class="fas fa-code mr-1"></i>
                                                        {{ __('Code') }}: <span id="preview_value" class="badge badge-secondary">{{ __('value') }}</span>
                                                    </small>
                                                    <br>
                                                    <small class="text-info">
                                                        <i class="fas fa-sort-numeric-up mr-1"></i>
                                                        {{ __('Order') }}: <span id="preview_sort_order">0</span>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>{{ __('Arabic Preview') }}</h5>
                                                <div class="preview-card" dir="rtl">
                                                    <div class="mb-2">
                                                        <i class="fas fa-eye text-success ml-2"></i>
                                                        <h6 id="preview_name_ar" class="d-inline text-success">{{ __('اسم نوع الإطلالة') }}</h6>
                                                    </div>
                                                    <p id="preview_description_ar" class="text-muted">{{ __('سيظهر وصف نوع الإطلالة هنا') }}</p>
                                                    <small class="text-info">
                                                        <i class="fas fa-code ml-1"></i>
                                                        {{ __('الرمز') }}: <span id="preview_value_ar" class="badge badge-secondary">{{ __('value') }}</span>
                                                    </small>
                                                    <br>
                                                    <small class="text-info">
                                                        <i class="fas fa-sort-numeric-up ml-1"></i>
                                                        {{ __('الترتيب') }}: <span id="preview_sort_order_ar">0</span>
                                                    </small>
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
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> {{ __('Create View Type') }}
                                </button>
                                <button type="button" class="btn btn-success btn-lg ml-2" onclick="saveAndAddAnother()">
                                    <i class="fas fa-plus"></i> {{ __('Save & Add Another') }}
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> {{ __('Reset Form') }}
                                </button>
                                <a href="{{ route('admin.view-types.index') }}" class="btn btn-outline-secondary ml-2">
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

<!-- Hidden form for save and add another -->
<form id="save-and-add-form" action="{{ route('admin.view-types.store') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="save_and_add" value="1">
</form>
@endsection

@push('styles')
<style>
.required::after {
    content: ' *';
    color: red;
}

.preview-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.switch-text {
    margin-left: 0.5rem;
}

.custom-control-input:checked ~ .custom-control-label .switch-text::before {
    content: '{{ __('Active') }} - ';
    color: #28a745;
    font-weight: bold;
}

.custom-control-input:not(:checked) ~ .custom-control-label .switch-text::before {
    content: '{{ __('Inactive') }} - ';
    color: #dc3545;
    font-weight: bold;
}

.card-outline {
    border-top: 3px solid;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.character-count {
    font-size: 0.875rem;
}

.character-count.warning {
    color: #ffc107;
}

.character-count.danger {
    color: #dc3545;
}

@media (max-width: 768px) {
    .card-footer .row {
        flex-direction: column;
    }

    .card-footer .text-right {
        text-align: left !important;
        margin-top: 1rem;
    }

    .btn-lg {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Form validation and preview updates
$(document).ready(function() {
    // Update preview on input change
    $('#name_en').on('input', function() {
        const value = $(this).val() || '{{ __('View Type Name') }}';
        $('#preview_name_en').text(value);
    });

    $('#name_ar').on('input', function() {
        const value = $(this).val() || '{{ __('اسم نوع الإطلالة') }}';
        $('#preview_name_ar').text(value);
    });

    $('#description_en').on('input', function() {
        const value = $(this).val() || '{{ __('View type description will appear here') }}';
        $('#preview_description_en').text(value);
        updateCharacterCount('description_en');
    });

    $('#description_ar').on('input', function() {
        const value = $(this).val() || '{{ __('سيظهر وصف نوع الإطلالة هنا') }}';
        $('#preview_description_ar').text(value);
        updateCharacterCount('description_ar');
    });

    $('#value').on('input', function() {
        const value = $(this).val() || '{{ __('value') }}';
        $('#preview_value').text(value);
        $('#preview_value_ar').text(value);
    });

    $('#sort_order').on('input', function() {
        const value = $(this).val() || '0';
        $('#preview_sort_order').text(value);
        $('#preview_sort_order_ar').text(value);
    });

    // Initialize character counts
    updateCharacterCount('description_en');
    updateCharacterCount('description_ar');

    // Form validation
    $('#view-type-form').on('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
    });

    // Auto-save functionality (optional)
    let autoSaveTimer;
    $('input, textarea').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            saveFormData();
        }, 2000); // Auto-save after 2 seconds of inactivity
    });

    // Load saved form data
    loadFormData();
});

// Character count update
function updateCharacterCount(fieldId) {
    const field = document.getElementById(fieldId);
    const countElement = document.getElementById(fieldId + '_count');
    const count = field.value.length;
    const maxLength = 500;

    countElement.textContent = count;

    // Update styling based on character count
    countElement.className = 'character-count';
    if (count > maxLength * 0.8) {
        countElement.classList.add('warning');
    }
    if (count > maxLength * 0.95) {
        countElement.classList.remove('warning');
        countElement.classList.add('danger');
    }
}

// Form validation
function validateForm() {
    let isValid = true;
    const errors = [];

    // Required fields validation
    const nameEn = document.getElementById('name_en').value.trim();
    const nameAr = document.getElementById('name_ar').value.trim();
    const value = document.getElementById('value').value.trim();

    if (!nameEn) {
        errors.push('{{ __('English name is required') }}');
        isValid = false;
    }

    if (!nameAr) {
        errors.push('{{ __('Arabic name is required') }}');
        isValid = false;
    }

    if (!value) {
        errors.push('{{ __('Value/Code is required') }}');
        isValid = false;
    }

    // Length validation
    if (nameEn.length > 100) {
        errors.push('{{ __('English name must not exceed 100 characters') }}');
        isValid = false;
    }

    if (nameAr.length > 100) {
        errors.push('{{ __('Arabic name must not exceed 100 characters') }}');
        isValid = false;
    }

    if (value.length > 50) {
        errors.push('{{ __('Value/Code must not exceed 50 characters') }}');
        isValid = false;
    }

    // Value format validation (no spaces, special chars)
    const valuePattern = /^[a-z0-9_-]+$/i;
    if (value && !valuePattern.test(value)) {
        errors.push('{{ __('Value/Code can only contain letters, numbers, hyphens, and underscores') }}');
        isValid = false;
    }

    // Description length validation
    const descEn = document.getElementById('description_en').value;
    const descAr = document.getElementById('description_ar').value;

    if (descEn.length > 500) {
        errors.push('{{ __('English description must not exceed 500 characters') }}');
        isValid = false;
    }

    if (descAr.length > 500) {
        errors.push('{{ __('Arabic description must not exceed 500 characters') }}');
        isValid = false;
    }

    // Sort order validation
    const sortOrder = parseInt(document.getElementById('sort_order').value);
    if (isNaN(sortOrder) || sortOrder < 0 || sortOrder > 999) {
        errors.push('{{ __('Sort order must be between 0 and 999') }}');
        isValid = false;
    }

    if (!isValid) {
        Swal.fire({
            title: '{{ __('Validation Error') }}',
            html: '<ul style="text-align: left;"><li>' + errors.join('</li><li>') + '</li></ul>',
            icon: 'error',
            confirmButtonText: '{{ __('OK') }}'
        });
    }

    return isValid;
}

// Reset form
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
            document.getElementById('view-type-form').reset();

            // Reset previews
            $('#preview_name_en').text('{{ __('View Type Name') }}');
            $('#preview_name_ar').text('{{ __('اسم نوع الإطلالة') }}');
            $('#preview_description_en').text('{{ __('View type description will appear here') }}');
            $('#preview_description_ar').text('{{ __('سيظهر وصف نوع الإطلالة هنا') }}');
            $('#preview_value').text('{{ __('value') }}');
            $('#preview_value_ar').text('{{ __('value') }}');
            $('#preview_sort_order').text('0');
            $('#preview_sort_order_ar').text('0');

            // Reset character counts
            updateCharacterCount('description_en');
            updateCharacterCount('description_ar');

            // Clear saved form data
            localStorage.removeItem('view_type_form_data');

            toastr.success('{{ __('Form has been reset') }}');
        }
    });
}

// Save and add another
function saveAndAddAnother() {
    if (!validateForm()) {
        return false;
    }

    // Copy form data to hidden form
    const mainForm = document.getElementById('view-type-form');
    const hiddenForm = document.getElementById('save-and-add-form');

    // Copy all form fields
    const formData = new FormData(mainForm);
    for (let [key, value] of formData.entries()) {
        if (key !== '_token') {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            hiddenForm.appendChild(input);
        }
    }

    hiddenForm.submit();
}

// Auto-save functionality
function saveFormData() {
    const formData = {
        name_en: document.getElementById('name_en').value,
        name_ar: document.getElementById('name_ar').value,
        value: document.getElementById('value').value,
        description_en: document.getElementById('description_en').value,
        description_ar: document.getElementById('description_ar').value,
        sort_order: document.getElementById('sort_order').value,
        is_active: document.getElementById('is_active').checked
    };

    localStorage.setItem('view_type_form_data', JSON.stringify(formData));
}

function loadFormData() {
    const savedData = localStorage.getItem('view_type_form_data');
    if (savedData) {
        const formData = JSON.parse(savedData);

        // Only load if form is empty
        if (!document.getElementById('name_en').value) {
            document.getElementById('name_en').value = formData.name_en || '';
            document.getElementById('name_ar').value = formData.name_ar || '';
            document.getElementById('value').value = formData.value || '';
            document.getElementById('description_en').value = formData.description_en || '';
            document.getElementById('description_ar').value = formData.description_ar || '';
            document.getElementById('sort_order').value = formData.sort_order || '0';
            document.getElementById('is_active').checked = formData.is_active || false;

            // Trigger input events to update previews
            $('#name_en, #name_ar, #value, #description_en, #description_ar, #sort_order').trigger('input');
        }
    }
}

// Clear auto-save data on successful submit
window.addEventListener('beforeunload', function() {
    // Only clear if form was successfully submitted
    if (document.querySelector('.alert-success')) {
        localStorage.removeItem('view_type_form_data');
    }
});

// Keyboard shortcuts
$(document).on('keydown', function(e) {
    // Ctrl+S to save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        $('#view-type-form').submit();
    }

    // Ctrl+R to reset
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        resetForm();
    }

    // Escape to cancel
    if (e.key === 'Escape') {
        window.location.href = '{{ route('admin.view-types.index') }}';
    }
});
</script>
@endpush