@extends('admin.layouts.app')

@section('title', 'Manage Neighborhoods')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.cities.index') }}">Cities</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.cities.show', $city) }}">{{ $city->name }}</a></li>
    <li class="breadcrumb-item active">Neighborhoods</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Neighborhoods in {{ $city->name }}</h3>
            </div>

            <div class="card-body">
                @if($neighborhoods->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>English</th>
                                <th>Arabic</th>
                                <th>Slug</th>
                                <th>Properties</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($neighborhoods as $neighborhood)
                            <tr>
                                <td>{{ $neighborhood->id }}</td>
                                <td>{{ $neighborhood->name }}</td>
                                <td>{{ $neighborhood->name_en }}</td>
                                <td dir="rtl">{{ $neighborhood->name_ar }}</td>
                                <td><code>{{ $neighborhood->slug }}</code></td>
                                <td>
                                    <span class="badge badge-info">{{ $neighborhood->properties_count }}</span>
                                </td>
                                <td>
                                    @if($neighborhood->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($neighborhood->properties_count == 0)
                                    <form action="{{ route('admin.neighborhoods.destroy', $neighborhood) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm delete-neighborhood" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-muted" title="Cannot delete - has properties">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($neighborhoods->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $neighborhoods->links() }}
                </div>
                @endif
                @else
                <div class="text-center py-4">
                    <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                    <h5>No neighborhoods yet</h5>
                    <p class="text-muted">Add neighborhoods to help organize properties in {{ $city->name }}.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Add New Neighborhood Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add New Neighborhood</h3>
            </div>

            <form action="{{ route('admin.cities.neighborhoods.store', $city) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Display Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name_en">English Name *</label>
                        <input type="text" name="name_en" id="name_en" class="form-control @error('name_en') is-invalid @enderror" 
                               value="{{ old('name_en') }}" required>
                        @error('name_en')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name_ar">Arabic Name *</label>
                        <input type="text" name="name_ar" id="name_ar" class="form-control @error('name_ar') is-invalid @enderror" 
                               value="{{ old('name_ar') }}" required dir="rtl">
                        @error('name_ar')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slug">URL Slug *</label>
                        <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" 
                               value="{{ old('slug') }}" required>
                        @error('slug')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">URL-friendly version</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus"></i> Add Neighborhood
                    </button>
                </div>
            </form>
        </div>

        <!-- City Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">City Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>City:</th>
                        <td>{{ $city->name }}</td>
                    </tr>
                    <tr>
                        <th>Total Neighborhoods:</th>
                        <td>{{ $city->neighborhoods()->count() }}</td>
                    </tr>
                    <tr>
                        <th>Active Neighborhoods:</th>
                        <td>{{ $city->neighborhoods()->where('is_active', true)->count() }}</td>
                    </tr>
                    <tr>
                        <th>Total Properties:</th>
                        <td>{{ $city->properties()->count() }}</td>
                    </tr>
                </table>

                <div class="mt-3">
                    <a href="{{ route('admin.cities.show', $city) }}" class="btn btn-info btn-block">
                        <i class="fas fa-eye"></i> View City Details
                    </a>
                    <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-list"></i> Back to Cities
                    </a>
                </div>
            </div>
        </div>

        <!-- Guidelines -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Use specific neighborhood names</li>
                    <li><i class="fas fa-check text-success"></i> Keep names consistent with local usage</li>
                    <li><i class="fas fa-check text-success"></i> Provide both English and Arabic names</li>
                    <li><i class="fas fa-check text-success"></i> Make slugs URL-friendly</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate slug from English name
    $('#name_en').on('input', function() {
        let slug = $(this).val()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
        $('#slug').val(slug);
    });

    // Copy display name from English name if empty
    $('#name_en').on('input', function() {
        if ($('#name').val() === '') {
            $('#name').val($(this).val());
        }
    });

    // Delete confirmation
    $('.delete-neighborhood').on('click', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush