@extends('admin.layouts.app')

@section('title', 'Amenity Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.amenities.index') }}">Amenities</a></li>
    <li class="breadcrumb-item active">{{ $amenity->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    @if($amenity->icon)
                        <i class="{{ $amenity->icon }} mr-2"></i>
                    @endif
                    Amenity Details: {{ $amenity->name }}
                </h3>
                <div class="card-tools">
                    @can('edit amenities')
                    <a href="{{ route('admin.amenities.edit', $amenity) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">ID:</th>
                                <td>{{ $amenity->id }}</td>
                            </tr>
                            <tr>
                                <th>Display Name:</th>
                                <td>{{ $amenity->name }}</td>
                            </tr>
                            <tr>
                                <th>English Name:</th>
                                <td>{{ $amenity->name_en }}</td>
                            </tr>
                            <tr>
                                <th>Arabic Name:</th>
                                <td dir="rtl">{{ $amenity->name_ar }}</td>
                            </tr>
                            <tr>
                                <th>URL Slug:</th>
                                <td><code>{{ $amenity->slug }}</code></td>
                            </tr>
                            <tr>
                                <th>Icon:</th>
                                <td>
                                    @if($amenity->icon)
                                        <i class="{{ $amenity->icon }} fa-lg text-primary mr-2"></i>
                                        <code>{{ $amenity->icon }}</code>
                                    @else
                                        <span class="text-muted">No icon set</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Status:</th>
                                <td>
                                    @if($amenity->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ $amenity->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Updated:</th>
                                <td>{{ $amenity->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Properties:</th>
                                <td>{{ $amenity->properties->count() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($amenity->description)
                <div class="row mt-3">
                    <div class="col-12">
                        <h5>Description</h5>
                        <p class="text-muted">{{ $amenity->description }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Properties using this amenity -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Properties with this Amenity ({{ $amenity->properties->count() }})</h3>
            </div>

            <div class="card-body">
                @if($amenity->properties->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>City</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($amenity->properties as $property)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($property->getFirstMediaUrl('images'))
                                            <img src="{{ $property->getFirstMediaUrl('images', 'thumb') }}" 
                                                 class="img-circle mr-2" width="40" height="40" alt="Property">
                                        @endif
                                        <div>
                                            <strong>{{ $property->title }}</strong>
                                            <br><small class="text-muted">{{ Str::limit($property->description, 50) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $property->city->name ?? 'N/A' }}</td>
                                <td>{{ $property->type->name ?? 'N/A' }}</td>
                                <td>
                                    @if($property->price)
                                        <strong>${{ number_format($property->price) }}</strong>
                                        @if($property->price_type)
                                            <br><small class="text-muted">{{ $property->price_type }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Price on request</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($property->status)
                                        @case('published')
                                            <span class="badge badge-success">Published</span>
                                            @break
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
                                            @break
                                        @case('draft')
                                            <span class="badge badge-secondary">Draft</span>
                                            @break
                                        @default
                                            <span class="badge badge-info">{{ ucfirst($property->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @can('view properties')
                                    <a href="{{ route('admin.properties.show', $property) }}" 
                                       class="btn btn-info btn-sm" title="View Property">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($amenity->properties->count() > 10)
                <div class="text-center mt-3">
                    <a href="{{ route('admin.properties.index', ['amenity' => $amenity->id]) }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> View All Properties with this Amenity
                    </a>
                </div>
                @endif
                @else
                <div class="text-center py-4">
                    <i class="fas fa-home fa-3x text-muted mb-3"></i>
                    <h5>No properties yet</h5>
                    <p class="text-muted">This amenity is not associated with any properties yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-home"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Properties</span>
                        <span class="info-box-number">{{ $amenity->properties->count() }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $amenity->properties->where('status', 'published')->count() }} published
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                @can('view properties')
                <a href="{{ route('admin.properties.index', ['amenity' => $amenity->id]) }}" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-home"></i> View Properties
                </a>
                @endcan

                @can('edit amenities')
                <a href="{{ route('admin.amenities.edit', $amenity) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit Amenity
                </a>

                @if($amenity->properties->count() == 0)
                <hr>
                <form action="{{ route('admin.amenities.destroy', $amenity) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block delete-amenity">
                        <i class="fas fa-trash"></i> Delete Amenity
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Activity</h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    <div class="time-label">
                        <span class="bg-success">{{ $amenity->created_at->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-plus bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $amenity->created_at->format('H:i') }}</span>
                            <h3 class="timeline-header">Amenity Created</h3>
                            <div class="timeline-body">
                                {{ $amenity->name }} was added to the system.
                            </div>
                        </div>
                    </div>
                    @if($amenity->updated_at != $amenity->created_at)
                    <div>
                        <i class="fas fa-edit bg-yellow"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $amenity->updated_at->format('H:i') }}</span>
                            <h3 class="timeline-header">Last Updated</h3>
                            <div class="timeline-body">
                                Amenity information was last updated on {{ $amenity->updated_at->format('M d, Y') }}.
                            </div>
                        </div>
                    </div>
                    @endif
                    <div>
                        <i class="far fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-amenity').on('click', function(e) {
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