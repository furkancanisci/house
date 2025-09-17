@extends('admin.layouts.app')

@section('title', __('Edit Building Type'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Edit Building Type') }}: {{ $buildingType->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.building-types.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <a href="{{ route('admin.building-types.show', $buildingType) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> {{ __('View') }}
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('admin.building-types.update', $buildingType) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_en">{{ __('Name (English)') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" name="name_en" value="{{ old('name_en', $buildingType->name_en) }}" required>
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_ar">{{ __('Name (Arabic)') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar', $buildingType->name_ar) }}" required dir="rtl">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Value Field -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="value">{{ __('Value/Code') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('value') is-invalid @enderror"
                                           id="value"
                                           name="value"
                                           value="{{ old('value', $buildingType->value) }}"
                                           placeholder="{{ __('Enter unique value/code (e.g., apartment, villa, house)') }}"
                                           required>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('Unique identifier for this building type (used internally)') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description_en">{{ __('Description (English)') }}</label>
                                    <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                              id="description_en" name="description_en" rows="3">{{ old('description_en', $buildingType->description_en) }}</textarea>
                                    @error('description_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description_ar">{{ __('Description (Arabic)') }}</label>
                                    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                              id="description_ar" name="description_ar" rows="3" dir="rtl">{{ old('description_ar', $buildingType->description_ar) }}</textarea>
                                    @error('description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">{{ __('Sort Order') }}</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $buildingType->sort_order) }}" min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', $buildingType->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">{{ __('Active') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{ __('Statistics') }}</label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon"><i class="fas fa-home"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">{{ __('Properties') }}</span>
                                                    <span class="info-box-number">{{ $buildingType->properties_count ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-success">
                                                <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">{{ __('Created') }}</span>
                                                    <span class="info-box-number">{{ $buildingType->created_at->format('Y-m-d') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">{{ __('Updated') }}</span>
                                                    <span class="info-box-number">{{ $buildingType->updated_at->format('Y-m-d') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box {{ $buildingType->is_active ? 'bg-success' : 'bg-danger' }}">
                                                <span class="info-box-icon"><i class="fas fa-{{ $buildingType->is_active ? 'check' : 'times' }}"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">{{ __('Status') }}</span>
                                                    <span class="info-box-number">{{ $buildingType->is_active ? __('Active') : __('Inactive') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('Update Building Type') }}
                        </button>
                        <a href="{{ route('admin.building-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('Cancel') }}
                        </a>
                        <div class="float-right">
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="{{ route('admin.building-types.destroy', $buildingType) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmDelete() {
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
            document.getElementById('delete-form').submit();
        }
    });
}

$(document).ready(function() {
    // Form validation
    $('form').on('submit', function(e) {
        var nameEn = $('#name_en').val().trim();
        var nameAr = $('#name_ar').val().trim();
        
        if (!nameEn || !nameAr) {
            e.preventDefault();
            toastr.error('{{ __('Please fill in both English and Arabic names') }}');
            return false;
        }
    });
});
</script>
@endpush