@extends('admin.layouts.app')

@section('title', 'City Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.cities.index') }}">Cities</a></li>
    <li class="breadcrumb-item active">{{ $city->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">City Details: {{ $city->name }}</h3>
                <div class="card-tools">
                    @can('manage cities')
                    <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-warning btn-sm">
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
                                <td>{{ $city->id }}</td>
                            </tr>
                            <tr>
                                <th>Display Name:</th>
                                <td>{{ $city->name }}</td>
                            </tr>
                            <tr>
                                <th>English Name:</th>
                                <td>{{ $city->name_en }}</td>
                            </tr>
                            <tr>
                                <th>Arabic Name:</th>
                                <td dir="rtl">{{ $city->name_ar }}</td>
                            </tr>
                            <tr>
                                <th>URL Slug:</th>
                                <td><code>{{ $city->slug }}</code></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($city->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Created:</th>
                                <td>{{ $city->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Updated:</th>
                                <td>{{ $city->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Neighborhoods:</th>
                                <td>{{ $city->neighborhoods->count() }}</td>
                            </tr>
                            <tr>
                                <th>Total Properties:</th>
                                <td>{{ $city->properties->count() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Neighborhoods List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Neighborhoods in {{ $city->name }}</h3>
                <div class="card-tools">
                    @can('manage neighborhoods')
                    <a href="{{ route('admin.cities.neighborhoods', $city) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-cog"></i> Manage Neighborhoods
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                @if($city->neighborhoods->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>English</th>
                                <th>Arabic</th>
                                <th>Properties</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($city->neighborhoods as $neighborhood)
                            <tr>
                                <td>{{ $neighborhood->name }}</td>
                                <td>{{ $neighborhood->name_en }}</td>
                                <td dir="rtl">{{ $neighborhood->name_ar }}</td>
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
                                <td>{{ $neighborhood->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                    <h5>No neighborhoods yet</h5>
                    <p class="text-muted">Add neighborhoods to help organize properties in this city.</p>
                    @can('manage neighborhoods')
                    <a href="{{ route('admin.cities.neighborhoods', $city) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Neighborhood
                    </a>
                    @endcan
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
                    <span class="info-box-icon bg-info"><i class="fas fa-map-marker-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Neighborhoods</span>
                        <span class="info-box-number">{{ $city->neighborhoods->count() }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $city->neighborhoods->where('is_active', true)->count() }} active
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-home"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Properties</span>
                        <span class="info-box-number">{{ $city->properties->count() }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $city->properties->where('status', 'published')->count() }} published
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
                @can('manage neighborhoods')
                <a href="{{ route('admin.cities.neighborhoods', $city) }}" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-map-marker-alt"></i> Manage Neighborhoods
                </a>
                @endcan

                @can('view properties')
                <a href="{{ route('admin.properties.index', ['city' => $city->id]) }}" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-home"></i> View Properties
                </a>
                @endcan

                @can('manage cities')
                <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit City
                </a>

                @if($city->properties->count() == 0)
                <hr>
                <form action="{{ route('admin.cities.destroy', $city) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block delete-city">
                        <i class="fas fa-trash"></i> Delete City
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Activity</h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    <div class="time-label">
                        <span class="bg-success">{{ $city->created_at->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-city bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> {{ $city->created_at->format('H:i') }}</span>
                            <h3 class="timeline-header">City Created</h3>
                            <div class="timeline-body">
                                {{ $city->name }} was added to the system.
                            </div>
                        </div>
                    </div>
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
    $('.delete-city').on('click', function(e) {
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