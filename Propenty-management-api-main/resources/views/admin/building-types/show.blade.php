@extends('admin.layouts.app')

@section('title', __('Building Type Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Building Type Details') }}: {{ $buildingType->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.building-types.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                        </a>
                        <a href="{{ route('admin.building-types.edit', $buildingType) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Name (English)') }}</label>
                                        <p class="form-control-static">{{ $buildingType->name_en }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Name (Arabic)') }}</label>
                                        <p class="form-control-static" dir="rtl">{{ $buildingType->name_ar }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Description (English)') }}</label>
                                        <p class="form-control-static">{{ $buildingType->description_en ?: __('No description') }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Description (Arabic)') }}</label>
                                        <p class="form-control-static" dir="rtl">{{ $buildingType->description_ar ?: __('No description') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Sort Order') }}</label>
                                        <p class="form-control-static">{{ $buildingType->sort_order }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Status') }}</label>
                                        <p class="form-control-static">
                                            <span class="badge badge-{{ $buildingType->is_active ? 'success' : 'danger' }}">
                                                <i class="fas fa-{{ $buildingType->is_active ? 'check' : 'times' }}"></i>
                                                {{ $buildingType->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Created At') }}</label>
                                        <p class="form-control-static">{{ $buildingType->created_at->format('Y-m-d H:i:s') }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Updated At') }}</label>
                                        <p class="form-control-static">{{ $buildingType->updated_at->format('Y-m-d H:i:s') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Statistics') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-home"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ __('Total Properties') }}</span>
                                            <span class="info-box-number">{{ $buildingType->properties_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ __('Active Properties') }}</span>
                                            <span class="info-box-number">{{ $buildingType->active_properties_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ __('Pending Properties') }}</span>
                                            <span class="info-box-number">{{ $buildingType->pending_properties_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card card-secondary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Actions') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group-vertical w-100">
                                        <a href="{{ route('admin.building-types.edit', $buildingType) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> {{ __('Edit Building Type') }}
                                        </a>
                                        
                                        <form action="{{ route('admin.building-types.toggle-status', $buildingType) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-{{ $buildingType->is_active ? 'warning' : 'success' }} btn-sm w-100">
                                                <i class="fas fa-{{ $buildingType->is_active ? 'eye-slash' : 'eye' }}"></i>
                                                {{ $buildingType->is_active ? __('Deactivate') : __('Activate') }}
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete()">
                                            <i class="fas fa-trash"></i> {{ __('Delete Building Type') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if($buildingType->properties_count > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Properties using this Building Type') }}</h3>
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
                                @forelse($buildingType->properties()->latest()->take(10)->get() as $property)
                                <tr>
                                    <td>{{ $property->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.properties.show', $property) }}" class="text-decoration-none">
                                            {{ Str::limit($property->title, 50) }}
                                        </a>
                                    </td>
                                    <td>{{ $property->category->name ?? __('N/A') }}</td>
                                    <td>{{ number_format($property->price) }} {{ $property->currency }}</td>
                                    <td>
                                        <span class="badge badge-{{ $property->status === 'active' ? 'success' : ($property->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $property->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No properties found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($buildingType->properties_count > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.properties.index', ['building_type' => $buildingType->id]) }}" class="btn btn-primary">
                            {{ __('View All Properties') }} ({{ $buildingType->properties_count }})
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
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
</script>
@endpush