@extends('admin.layouts.app')

@section('title', 'Properties')

@section('content-header', 'Properties Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Properties</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Properties</p>
                </div>
                <div class="icon">
                    <i class="fas fa-home"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active'] }}</h3>
                    <p>Active Properties</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Pending Approval</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['featured'] }}</h3>
                    <p>Featured</p>
                </div>
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Properties List</h3>
                    <div class="card-tools">
                        @can('create properties')
                        <a href="{{ route('admin.properties.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Property
                        </a>
                        @endcan
                        @can('export properties')
                        <a href="{{ route('admin.properties.export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                        @endcan
                        @can('import properties')
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#importModal">
                            <i class="fas fa-upload"></i> Import CSV
                        </button>
                        @endcan
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.properties.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="filter[title]" class="form-control form-control-sm" 
                                       placeholder="Search title..." value="{{ request('filter.title') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="filter[status]" class="form-control form-control-sm">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('filter.status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ request('filter.status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="rejected" {{ request('filter.status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="draft" {{ request('filter.status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="expired" {{ request('filter.status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="filter[listing_type]" class="form-control form-control-sm">
                                    <option value="">All Types</option>
                                    <option value="sale" {{ request('filter.listing_type') === 'sale' ? 'selected' : '' }}>For Sale</option>
                                    <option value="rent" {{ request('filter.listing_type') === 'rent' ? 'selected' : '' }}>For Rent</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="filter[city]" class="form-control form-control-sm">
                                    <option value="">All Cities</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ request('filter.city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="filter[user_id]" class="form-control form-control-sm">
                                    <option value="">All Owners</option>
                                    @foreach($users as $id => $name)
                                        <option value="{{ $id }}" {{ request('filter.user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Bulk Actions -->
                    @can('bulk manage properties')
                    <form id="bulkActionForm" method="POST" action="{{ route('admin.properties.bulk') }}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select name="action" class="form-control form-control-sm" required>
                                    <option value="">Select Action</option>
                                    @can('approve properties')
                                    <option value="approve">Approve Selected</option>
                                    <option value="reject">Reject Selected</option>
                                    @endcan
                                    @can('feature properties')
                                    <option value="feature">Feature Selected</option>
                                    <option value="unfeature">Unfeature Selected</option>
                                    @endcan
                                    @can('publish properties')
                                    <option value="publish">Publish Selected</option>
                                    <option value="unpublish">Unpublish Selected</option>
                                    @endcan
                                    @can('delete properties')
                                    <option value="delete">Delete Selected</option>
                                    @endcan
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="applyBulkAction" class="btn btn-warning btn-sm" disabled>
                                    <i class="fas fa-cogs"></i> Apply to Selected
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <small class="text-muted">
                                    Showing {{ $properties->firstItem() ?? 0 }} to {{ $properties->lastItem() ?? 0 }} 
                                    of {{ $properties->total() }} results
                                </small>
                            </div>
                        </div>
                    @endcan

                    <!-- Properties Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    @can('bulk manage properties')
                                    <th width="30">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    @endcan
                                    <th width="80">Image</th>
                                    <th>Property</th>
                                    <th width="100">Status</th>
                                    <th width="120">Type</th>
                                    <th width="120">Price</th>
                                    <th width="150">Owner</th>
                                    <th width="120">Created</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($properties as $property)
                                <tr>
                                    @can('bulk manage properties')
                                    <td>
                                        <input type="checkbox" name="property_ids[]" value="{{ $property->id }}" class="property-checkbox">
                                    </td>
                                    @endcan
                                    <td>
                                        @if($property->getFirstMediaUrl('images', 'small'))
                                            <img src="{{ $property->getFirstMediaUrl('images', 'small') }}" 
                                                 alt="Property" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-home text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ Str::limit($property->title, 40) }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> {{ $property->city }}, {{ $property->state }}
                                        </small>
                                        @if($property->is_featured)
                                            <span class="badge badge-warning badge-sm">Featured</span>
                                        @endif
                                        <br>
                                        <small>
                                            <i class="fas fa-bed"></i> {{ $property->bedrooms ?? 0 }}
                                            <i class="fas fa-bath ml-2"></i> {{ $property->bathrooms ?? 0 }}
                                            @if($property->square_feet)
                                                <i class="fas fa-ruler-combined ml-2"></i> {{ number_format($property->square_feet) }} sq ft
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $property->status === 'active' ? 'success' : ($property->status === 'pending' ? 'warning' : ($property->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                        <br>
                                        @if(!$property->is_available)
                                            <small class="text-muted">Not Available</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $property->listing_type === 'sale' ? 'info' : 'primary' }}">
                                            {{ ucfirst($property->listing_type) }}
                                        </span>
                                        <br>
                                        <small>{{ ucfirst($property->property_type) }}</small>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($property->price) }}</strong>
                                        @if($property->listing_type === 'rent' && $property->price_type)
                                            <br><small>/{{ \App\Models\PriceType::getTranslatedPriceType($property->price_type) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $property->user->full_name ?? 'Unknown' }}
                                        <br>
                                        <small class="text-muted">{{ $property->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        {{ $property->created_at->format('M j, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $property->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            @can('view properties')
                                            <a href="{{ route('admin.properties.show', $property) }}" 
                                               class="btn btn-info btn-xs" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('edit properties')
                                            <a href="{{ route('admin.properties.edit', $property) }}" 
                                               class="btn btn-primary btn-xs" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @if($property->status === 'pending')
                                                @can('approve properties')
                                                <form method="POST" action="{{ route('admin.properties.approve', $property) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ can('bulk manage properties') ? '9' : '8' }}" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No properties found</h5>
                                            <p class="text-muted">Try adjusting your filters or add a new property.</p>
                                            @can('create properties')
                                            <a href="{{ route('admin.properties.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add First Property
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @can('bulk manage properties')
                    </form>
                    @endcan

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            {{ $properties->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    @can('import properties')
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.properties.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Properties</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="csv_file">CSV File</label>
                            <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                            <small class="form-text text-muted">
                                Upload a CSV file with property data. Required columns: Title, Type, {{ __('admin.listing_type') }}, Price, City, State.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="rejectForm" method="POST">
                    @csrf
                    <input type="hidden" name="reject_reason" id="rejectReason">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Properties</h5>
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
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#selectAll').on('change', function() {
        $('.property-checkbox').prop('checked', this.checked);
        toggleBulkActionButton();
    });

    $('.property-checkbox').on('change', function() {
        var totalCheckboxes = $('.property-checkbox').length;
        var checkedCheckboxes = $('.property-checkbox:checked').length;
        
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        toggleBulkActionButton();
    });

    function toggleBulkActionButton() {
        var checkedBoxes = $('.property-checkbox:checked').length;
        $('#applyBulkAction').prop('disabled', checkedBoxes === 0);
    }

    // Bulk action handler
    $('#applyBulkAction').on('click', function() {
        var action = $('select[name="action"]').val();
        var checkedBoxes = $('.property-checkbox:checked');
        
        if (!action) {
            alert('Please select an action');
            return;
        }
        
        if (checkedBoxes.length === 0) {
            alert('Please select at least one property');
            return;
        }

        if (action === 'reject') {
            // Show reject modal
            $('#rejectModal').modal('show');
            $('#rejectForm').attr('action', '{{ route("admin.properties.bulk") }}');
            
            $('#rejectForm').off('submit').on('submit', function(e) {
                $('#rejectReason').val($('#reason').val());
                // Add selected property IDs to form
                checkedBoxes.each(function() {
                    $('#rejectForm').append('<input type="hidden" name="property_ids[]" value="' + $(this).val() + '">');
                });
                $('select[name="action"]').clone().appendTo('#rejectForm');
            });
            return;
        }

        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected properties? This action cannot be undone.')) {
                return;
            }
        }

        $('#bulkActionForm').submit();
    });
});
</script>
@endpush