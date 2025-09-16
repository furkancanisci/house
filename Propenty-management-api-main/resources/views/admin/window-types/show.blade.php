@extends('admin.layouts.app')

@section('title', __('Window Type Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Window Type Details') }}: {{ $windowType->name_en }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.window-types.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <a href="{{ route('admin.window-types.edit', $windowType) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Statistics Row -->
                    <div class="row mb-4">
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
                                <span class="info-box-icon"><i class="fas fa-sort-numeric-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Sort Order') }}</span>
                                    <span class="info-box-number">{{ $windowType->sort_order }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Created') }}</span>
                                    <span class="info-box-number">{{ $windowType->created_at->format('M d, Y') }}</span>
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
                    
                    <!-- Details Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('English Details') }}</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">{{ __('Name') }}:</th>
                                            <td>{{ $windowType->name_en }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Description') }}:</th>
                                            <td>{{ $windowType->description_en ?: __('No description provided') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Arabic Details') }}</h3>
                                </div>
                                <div class="card-body" dir="rtl">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">{{ __('Name') }}:</th>
                                            <td>{{ $windowType->name_ar }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Description') }}:</th>
                                            <td>{{ $windowType->description_ar ?: __('لا يوجد وصف') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('System Information') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th width="40%">{{ __('ID') }}:</th>
                                                    <td>{{ $windowType->id }}</td>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('Sort Order') }}:</th>
                                                    <td><span class="badge badge-secondary">{{ $windowType->sort_order }}</span></td>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('Status') }}:</th>
                                                    <td>
                                                        <span class="badge badge-{{ $windowType->is_active ? 'success' : 'danger' }}">
                                                            <i class="fas fa-{{ $windowType->is_active ? 'check' : 'times' }}"></i>
                                                            {{ $windowType->is_active ? __('Active') : __('Inactive') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th width="40%">{{ __('Created At') }}:</th>
                                                    <td>{{ $windowType->created_at->format('Y-m-d H:i:s') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('Updated At') }}:</th>
                                                    <td>{{ $windowType->updated_at->format('Y-m-d H:i:s') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('Properties Count') }}:</th>
                                                    <td><span class="badge badge-info">{{ $windowType->properties_count ?? 0 }}</span></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Properties Using This Window Type -->
                    @if($windowType->properties_count > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        {{ __('Properties Using This Window Type') }} 
                                        <span class="badge badge-info">{{ $windowType->properties_count }}</span>
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
                                                    <th>{{ __('Price') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Created') }}</th>
                                                    <th>{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($properties ?? [] as $property)
                                                <tr>
                                                    <td>{{ $property->id }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.properties.show', $property) }}" class="text-decoration-none">
                                                            {{ Str::limit($property->title_en, 30) }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary">{{ $property->type }}</span>
                                                    </td>
                                                    <td>
                                                        <strong>${{ number_format($property->price) }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $property->status === 'active' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($property->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $property->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-info btn-sm" title="{{ __('View') }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-primary btn-sm" title="{{ __('Edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">{{ __('No properties found using this window type') }}</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    @if(isset($properties) && $properties->hasPages())
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $properties->links() }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Actions') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.window-types.edit', $windowType) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i> {{ __('Edit Window Type') }}
                                        </a>
                                        
                                        <form action="{{ route('admin.window-types.toggle-status', $windowType) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-{{ $windowType->is_active ? 'warning' : 'success' }}">
                                                <i class="fas fa-{{ $windowType->is_active ? 'pause' : 'play' }}"></i>
                                                {{ $windowType->is_active ? __('Deactivate') : __('Activate') }}
                                            </button>
                                        </form>
                                        
                                        @if($windowType->properties_count == 0)
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                            <i class="fas fa-trash"></i> {{ __('Delete') }}
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-danger" disabled title="{{ __('Cannot delete: Window type is used by properties') }}">
                                            <i class="fas fa-trash"></i> {{ __('Delete') }}
                                        </button>
                                        @endif
                                        
                                        <a href="{{ route('admin.window-types.create') }}" class="btn btn-success">
                                            <i class="fas fa-plus"></i> {{ __('Add New Window Type') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
.info-box {
    margin-bottom: 0;
}

.card-outline {
    border-top: 3px solid;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.875rem;
}

.btn-group .btn {
    margin-right: 5px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 5px;
    }
    
    .btn-group .btn:last-child {
        margin-bottom: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
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
                document.getElementById('delete-form').submit();
            }
        });
    @endif
}

// Status toggle confirmation
$('form[action*="toggle-status"]').on('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const isActive = {{ $windowType->is_active ? 'true' : 'false' }};
    const action = isActive ? '{{ __('deactivate') }}' : '{{ __('activate') }}';
    const warning = isActive && {{ $windowType->properties_count ?? 0 }} > 0 ? 
        '{{ __('This will affect :count properties using this window type.', ['count' => $windowType->properties_count ?? 0]) }}' : '';
    
    Swal.fire({
        title: '{{ __('Confirm Status Change') }}',
        text: '{{ __('Are you sure you want to :action this window type?', ['action' => ':action']) }}'.replace(':action', action) + (warning ? '\n\n' + warning : ''),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '{{ __('Yes, :action it!', ['action' => ':action']) }}'.replace(':action', action),
        cancelButtonText: '{{ __('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

// Refresh properties list (if needed)
function refreshPropertiesList() {
    // You could implement AJAX refresh here
    location.reload();
}

// Print functionality
function printDetails() {
    window.print();
}

// Export functionality (optional)
function exportDetails() {
    // You could implement export functionality here
    toastr.info('{{ __('Export functionality coming soon') }}');
}

// Add keyboard shortcuts
$(document).on('keydown', function(e) {
    // Ctrl+E for edit
    if (e.ctrlKey && e.key === 'e') {
        e.preventDefault();
        window.location.href = '{{ route('admin.window-types.edit', $windowType) }}';
    }
    
    // Ctrl+B for back to list
    if (e.ctrlKey && e.key === 'b') {
        e.preventDefault();
        window.location.href = '{{ route('admin.window-types.index') }}';
    }
});

// Tooltip initialization
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush