@extends('admin.layouts.app')

@section('title', 'Property Details')

@section('content-header', 'Property Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">Properties</a></li>
    <li class="breadcrumb-item active">{{ $property->title }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Property Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $property->title }}</h3>
                    <div class="card-tools">
                        @can('edit properties')
                        <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Property
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Property Images -->
                    @if($property->getMedia('images')->count() > 0)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div id="propertyCarousel" class="carousel slide" data-ride="carousel">
                                <div class="carousel-inner">
                                    @foreach($property->getMedia('images') as $index => $media)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $media->getUrl() }}" class="d-block w-100" alt="Property Image" style="height: 400px; object-fit: cover;">
                                    </div>
                                    @endforeach
                                </div>
                                @if($property->getMedia('images')->count() > 1)
                                <a class="carousel-control-prev" href="#propertyCarousel" role="button" data-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </a>
                                <a class="carousel-control-next" href="#propertyCarousel" role="button" data-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Property Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Property Type:</strong></td>
                                    <td>{{ ucfirst($property->property_type) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('admin.listing_type') }}:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $property->listing_type === 'sale' ? 'info' : 'primary' }}">
                                            {{ $property->listing_type === 'sale' ? __('admin.for_sale') : __('admin.for_rent') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('admin.price') }}:</strong></td>
                                    <td>
                                        <strong class="text-success">${{ number_format($property->price) }}</strong>
                                        @if($property->price_type)
                                            <small class="text-muted">
                                                ({{ __('admin.' . $property->price_type) }})
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $property->status === 'active' ? 'success' : ($property->status === 'pending' ? 'warning' : ($property->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Available:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $property->is_available ? 'success' : 'secondary' }}">
                                            {{ $property->is_available ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Featured:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $property->is_featured ? 'warning' : 'secondary' }}">
                                            {{ $property->is_featured ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Property Features</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Bedrooms:</strong></td>
                                    <td>{{ $property->bedrooms ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Bathrooms:</strong></td>
                                    <td>{{ $property->bathrooms ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Square Feet:</strong></td>
                                    <td>{{ $property->square_feet ? number_format($property->square_feet) . ' sq ft' : 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Lot Size:</strong></td>
                                    <td>{{ $property->lot_size ? number_format($property->lot_size) . ' sq ft' : 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Year Built:</strong></td>
                                    <td>{{ $property->year_built ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Parking:</strong></td>
                                    <td>
                                        {{ $property->parking_type ?? 'Not specified' }}
                                        @if($property->parking_spaces)
                                            ({{ $property->parking_spaces }} spaces)
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Description</h5>
                            <div class="bg-light p-3 rounded">
                                {{ $property->description ?: 'No description provided.' }}
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Location</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td>{{ $property->street_address }}</td>
                                </tr>
                                <tr>
                                    <td><strong>City:</strong></td>
                                    <td>{{ $property->city }}</td>
                                </tr>
                                <tr>
                                    <td><strong>State:</strong></td>
                                    <td>{{ $property->state }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Neighborhood:</strong></td>
                                    <td>{{ $property->neighborhood ?? 'Not specified' }}</td>
                                </tr>
                                @if($property->latitude && $property->longitude)
                                <tr>
                                    <td><strong>Coordinates:</strong></td>
                                    <td>{{ $property->latitude }}, {{ $property->longitude }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>


                    <!-- Nearby Places -->
                    @if($property->nearby_places && is_array($property->nearby_places) && count($property->nearby_places) > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Nearby Places</h5>
                            <div class="row">
                                @foreach($property->nearby_places as $place)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <span class="badge badge-info">
                                        <i class="fas fa-map-marker-alt"></i> {{ $place }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Contact Information -->
                    @if($property->contact_name || $property->contact_phone || $property->contact_email)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Contact Information</h5>
                            <table class="table table-bordered">
                                @if($property->contact_name)
                                <tr>
                                    <td><strong>Contact Name:</strong></td>
                                    <td>{{ $property->contact_name }}</td>
                                </tr>
                                @endif
                                @if($property->contact_phone)
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $property->contact_phone }}</td>
                                </tr>
                                @endif
                                @if($property->contact_email)
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $property->contact_email }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    @if($property->status === 'pending')
                        @can('approve properties')
                        <form method="POST" action="{{ route('admin.properties.approve', $property) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> Approve Property
                            </button>
                        </form>
                        @endcan
                        @can('reject properties')
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                            <i class="fas fa-times"></i> Reject Property
                        </button>
                        @endcan
                    @endif

                    @can('feature properties')
                    <form method="POST" action="{{ route('admin.properties.feature', $property) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $property->is_featured ? 'warning' : 'outline-warning' }} btn-block">
                            <i class="fas fa-star"></i> {{ $property->is_featured ? 'Unfeature' : 'Feature' }} Property
                        </button>
                    </form>
                    @endcan

                    @can('publish properties')
                    <form method="POST" action="{{ route('admin.properties.publish', $property) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $property->is_available ? 'secondary' : 'info' }} btn-block">
                            <i class="fas fa-{{ $property->is_available ? 'eye-slash' : 'eye' }}"></i> {{ $property->is_available ? 'Unpublish' : 'Publish' }} Property
                        </button>
                    </form>
                    @endcan

                    @can('edit properties')
                    <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Property
                    </a>
                    @endcan

                    @can('delete properties')
                    <form method="POST" action="{{ route('admin.properties.destroy', $property) }}" class="mt-2" 
                          onsubmit="return confirm('Are you sure you want to delete this property?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Property
                        </button>
                    </form>
                    @endcan

                    <hr>
                    <a href="{{ url('/properties/' . $property->slug) }}" target="_blank" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-external-link-alt"></i> View on Site
                    </a>
                </div>
            </div>

            <!-- Property Owner -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Property Owner</h3>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img src="{{ $property->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($property->user->full_name ?? 'User') }}" 
                             class="img-circle elevation-2 mb-3" alt="User Image" style="width: 80px; height: 80px;">
                        <h5>{{ $property->user->full_name ?? 'Unknown User' }}</h5>
                        <p class="text-muted">{{ $property->user->email ?? '' }}</p>
                        @if($property->user->phone)
                        <p><i class="fas fa-phone"></i> {{ $property->user->phone }}</p>
                        @endif
                        @can('view users')
                        <a href="{{ route('admin.users.show', $property->user) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Property Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <h5 class="description-header">{{ $property->views->count() }}</h5>
                                <span class="description-text">VIEWS</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <span class="description-percentage text-warning">
                                    <i class="fas fa-heart"></i>
                                </span>
                                <h5 class="description-header">{{ $property->favoritedByUsers->count() }}</h5>
                                <span class="description-text">FAVORITES</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-info">
                                    <i class="fas fa-images"></i>
                                </span>
                                <h5 class="description-header">{{ $property->getMedia('images')->count() }}</h5>
                                <span class="description-text">IMAGES</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <span class="description-percentage text-primary">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <h5 class="description-header">{{ $property->created_at->diffInDays() }}</h5>
                                <span class="description-text">DAYS OLD</span>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <p class="text-muted">
                        <strong>Created:</strong> {{ $property->created_at->format('M j, Y g:i A') }}<br>
                        <strong>Updated:</strong> {{ $property->updated_at->format('M j, Y g:i A') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    @can('reject properties')
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.properties.reject', $property) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Property</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="reason">Rejection Reason</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" required 
                                      placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection