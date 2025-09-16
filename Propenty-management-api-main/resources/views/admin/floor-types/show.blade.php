@extends('admin.layouts.app')

@section('title', __('Floor Type Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Floor Type Details') }}: {{ $floorType->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.floor-types.edit', $floorType) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                        </a>
                        <a href="{{ route('admin.floor-types.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                    </div>
                </div>
                
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
                    
                    <!-- Statistics Row -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-home"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Properties') }}</span>
                                    <span class="info-box-number">{{ $floorType->properties_count ?? 0 }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ min(($floorType->properties_count ?? 0) * 10, 100) }}%"></div>
                                    </div>
                                    <span class="progress-description">{{ __('Using this floor type') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Created') }}</span>
                                    <span class="info-box-number">{{ $floorType->created_at->format('M d') }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">{{ $floorType->created_at->format('Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Last Updated') }}</span>
                                    <span class="info-box-number">{{ $floorType->updated_at->format('M d') }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">{{ $floorType->updated_at->format('Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box {{ $floorType->is_active ? 'bg-success' : 'bg-danger' }}">
                                <span class="info-box-icon">
                                    <i class="fas {{ $floorType->is_active ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Status') }}</span>
                                    <span class="info-box-number">{{ $floorType->is_active ? __('Active') : __('Inactive') }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $floorType->is_active ? '100' : '0' }}%"></div>
                                    </div>
                                    <span class="progress-description">{{ $floorType->is_active ? __('Available for use') : __('Not available') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- English Details -->
                        <div class="col-md-6">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-flag-usa"></i> {{ __('English Information') }}
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ __('Name (English)') }}</label>
                                        <p class="form-control-static">{{ $floorType->name_en }}</p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ __('Description (English)') }}</label>
                                        <div class="form-control-static">
                                            @if($floorType->description_en)
                                                <p class="text-justify">{{ $floorType->description_en }}</p>
                                            @else
                                                <p class="text-muted font-italic">{{ __('No description provided') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ __('Character Count') }}</label>
                                        <p class="form-control-static">
                                            <span class="badge badge-info">{{ strlen($floorType->description_en ?? '') }}/500</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Arabic Details -->
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-flag"></i> {{ __('Arabic Information') }}
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" dir="rtl">
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ __('Name (Arabic)') }}</label>
                                        <p class="form-control-static">{{ $floorType->name_ar }}</p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ __('Description (Arabic)') }}</label>
                                        <div class="form-control-static">
                                            @if($floorType->description_ar)
                                                <p class="text-justify">{{ $floorType->description_ar }}</p>
                                            @else
                                                <p class="text-muted font-italic">{{ __('لا يوجد وصف') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ __('Character Count') }}</label>
                                        <p class="form-control-static">
                                            <span class="badge badge-info">{{ strlen($floorType->description_ar ?? '') }}/500</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Information -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-cogs"></i> {{ __('System Information') }}
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('ID') }}</label>
                                                <p class="form-control-static">
                                                    <span class="badge badge-secondary">#{{ $floorType->id }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('Sort Order') }}</label>
                                                <p class="form-control-static">
                                                    <span class="badge badge-primary">{{ $floorType->sort_order }}</span>
                                                    <small class="text-muted d-block">{{ __('Lower numbers appear first') }}</small>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('Status') }}</label>
                                                <p class="form-control-static">
                                                    <span class="badge {{ $floorType->is_active ? 'badge-success' : 'badge-danger' }}">
                                                        <i class="fas {{ $floorType->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                                        {{ $floorType->is_active ? __('Active') : __('Inactive') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('Usage Count') }}</label>
                                                <p class="form-control-static">
                                                    <span class="badge badge-info">{{ $floorType->properties_count ?? 0 }} {{ __('properties') }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('Created At') }}</label>
                                                <p class="form-control-static">
                                                    <i class="fas fa-calendar-plus text-success"></i>
                                                    {{ $floorType->created_at->format('F j, Y \a\t g:i A') }}
                                                    <small class="text-muted d-block">{{ $floorType->created_at->diffForHumans() }}</small>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('Last Updated') }}</label>
                                                <p class="form-control-static">
                                                    <i class="fas fa-calendar-edit text-warning"></i>
                                                    {{ $floorType->updated_at->format('F j, Y \a\t g:i A') }}
                                                    <small class="text-muted d-block">{{ $floorType->updated_at->diffForHumans() }}</small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Properties Using This Floor Type -->
                    @if(isset($floorType->properties) && $floorType->properties->count() > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-home"></i> {{ __('Properties Using This Floor Type') }}
                                        <span class="badge badge-warning ml-2">{{ $floorType->properties->count() }}</span>
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('ID') }}</th>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Type') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Created') }}</th>
                                                    <th>{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($floorType->properties->take(10) as $property)
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-secondary">#{{ $property->id }}</span>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $property->title }}</strong>
                                                        @if($property->featured)
                                                            <span class="badge badge-warning ml-1">{{ __('Featured') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $property->type ?? __('N/A') }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $property->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                                            {{ ucfirst($property->status ?? 'inactive') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $property->created_at->format('M j, Y') }}</small>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-sm btn-outline-primary" title="{{ __('View Property') }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-sm btn-outline-warning" title="{{ __('Edit Property') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        
                                        @if($floorType->properties->count() > 10)
                                            <div class="text-center mt-3">
                                                <p class="text-muted">{{ __('Showing first 10 properties.') }} 
                                                    <a href="{{ route('admin.properties.index', ['floor_type' => $floorType->id]) }}" class="btn btn-sm btn-outline-primary">
                                                        {{ __('View All') }} ({{ $floorType->properties->count() }})
                                                    </a>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('admin.floor-types.edit', $floorType) }}" class="btn btn-warning btn-lg">
                                <i class="fas fa-edit"></i> {{ __('Edit Floor Type') }}
                            </a>
                            <button type="button" class="btn btn-info btn-lg ml-2" onclick="toggleStatus()">
                                <i class="fas {{ $floorType->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                {{ $floorType->is_active ? __('Deactivate') : __('Activate') }}
                            </button>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> {{ __('Delete Floor Type') }}
                            </button>
                            <a href="{{ route('admin.floor-types.create') }}" class="btn btn-success ml-2">
                                <i class="fas fa-plus"></i> {{ __('Add New Floor Type') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="{{ route('admin.floor-types.destroy', $floorType) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Toggle Status Form -->
<form id="toggle-status-form" action="{{ route('admin.floor-types.toggle-status', $floorType) }}" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>
@endsection

@push('styles')
<style>
.form-control-static {
    padding-top: 7px;
    padding-bottom: 7px;
    margin-bottom: 0;
    min-height: 34px;
}

.info-box {
    border-radius: 0.375rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 1rem;
}

.info-box-icon {
    border-radius: 0.375rem 0 0 0.375rem;
}

.card-outline {
    border-top: 3px solid;
}

.badge {
    font-size: 0.875em;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.table-responsive {
    border-radius: 0.375rem;
}

.progress {
    height: 3px;
    margin-bottom: 5px;
}

.progress-description {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.8);
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
    
    .info-box {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}

.text-justify {
    text-align: justify;
}

.font-italic {
    font-style: italic;
}

.card-body .form-group:last-child {
    margin-bottom: 0;
}

.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-refresh statistics every 30 seconds
    setInterval(function() {
        // You can implement AJAX refresh here if needed
    }, 30000);
});

// Confirm delete
function confirmDelete() {
    const propertiesCount = {{ $floorType->properties_count ?? 0 }};
    
    let warningText = '{{ __('This action cannot be undone!') }}';
    if (propertiesCount > 0) {
        warningText = '{{ __('This floor type is used by') }} ' + propertiesCount + ' {{ __('properties. Deleting it may affect those properties. This action cannot be undone!') }}';
    }
    
    Swal.fire({
        title: '{{ __('Delete Floor Type') }}',
        text: warningText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '{{ __('Yes, delete it!') }}',
        cancelButtonText: '{{ __('Cancel') }}',
        input: propertiesCount > 0 ? 'checkbox' : null,
        inputPlaceholder: propertiesCount > 0 ? '{{ __('I understand the consequences') }}' : null,
        inputValidator: propertiesCount > 0 ? (result) => {
            return !result && '{{ __('You must confirm that you understand the consequences') }}';
        } : null
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}

// Toggle status confirmation
function toggleStatus() {
    const currentStatus = {{ $floorType->is_active ? 'true' : 'false' }};
    const newStatus = currentStatus ? '{{ __('deactivate') }}' : '{{ __('activate') }}';
    const propertiesCount = {{ $floorType->properties_count ?? 0 }};
    
    let warningText = '{{ __('Are you sure you want to') }} ' + newStatus + ' {{ __('this floor type?') }}';
    if (!currentStatus && propertiesCount > 0) {
        warningText += ' {{ __('This will make it available for selection in property forms.') }}';
    } else if (currentStatus && propertiesCount > 0) {
        warningText += ' {{ __('This may affect') }} ' + propertiesCount + ' {{ __('properties that are currently using this floor type.') }}';
    }
    
    Swal.fire({
        title: '{{ __('Toggle Status') }}',
        text: warningText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: currentStatus ? '#d33' : '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __('Yes') }}, ' + newStatus + '!',
        cancelButtonText: '{{ __('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('toggle-status-form').submit();
        }
    });
}

// Keyboard shortcuts
$(document).on('keydown', function(e) {
    // E to edit
    if (e.key === 'e' || e.key === 'E') {
        if (!e.ctrlKey && !e.altKey && !$(e.target).is('input, textarea')) {
            window.location.href = '{{ route('admin.floor-types.edit', $floorType) }}';
        }
    }
    
    // D to delete
    if (e.key === 'd' || e.key === 'D') {
        if (!e.ctrlKey && !e.altKey && !$(e.target).is('input, textarea')) {
            confirmDelete();
        }
    }
    
    // T to toggle status
    if (e.key === 't' || e.key === 'T') {
        if (!e.ctrlKey && !e.altKey && !$(e.target).is('input, textarea')) {
            toggleStatus();
        }
    }
    
    // N for new
    if (e.key === 'n' || e.key === 'N') {
        if (!e.ctrlKey && !e.altKey && !$(e.target).is('input, textarea')) {
            window.location.href = '{{ route('admin.floor-types.create') }}';
        }
    }
    
    // Escape to go back
    if (e.key === 'Escape') {
        window.location.href = '{{ route('admin.floor-types.index') }}';
    }
});

// Show keyboard shortcuts help
$(document).ready(function() {
    // Add keyboard shortcuts info to the page
    const shortcutsHtml = `
        <div class="alert alert-info alert-dismissible fade show" role="alert" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; max-width: 300px;">
            <h6><i class="fas fa-keyboard"></i> {{ __('Keyboard Shortcuts') }}</h6>
            <small>
                <strong>E</strong> - {{ __('Edit') }}<br>
                <strong>D</strong> - {{ __('Delete') }}<br>
                <strong>T</strong> - {{ __('Toggle Status') }}<br>
                <strong>N</strong> - {{ __('New Floor Type') }}<br>
                <strong>Esc</strong> - {{ __('Back to List') }}
            </small>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    // Show shortcuts after 3 seconds, hide after 10 seconds
    setTimeout(function() {
        $('body').append(shortcutsHtml);
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 7000);
    }, 3000);
});
</script>
@endpush