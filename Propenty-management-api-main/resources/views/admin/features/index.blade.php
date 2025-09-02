@extends('admin.layouts.app')

@section('title', 'Features Management')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Features</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Features Management</h3>
                <div class="card-tools">
                    @can('create features')
                    <a href="{{ route('admin.features.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Feature
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" class="mb-3" id="filterForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search features..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select name="is_active" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name="category" class="form-control" 
                                       placeholder="Filter by category..." 
                                       value="{{ request('category') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.features.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Bulk Actions -->
                @can('edit features')
                <form method="POST" action="{{ route('admin.features.bulk') }}" id="bulkForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <select name="bulk_action" class="form-control" required>
                                    <option value="">Select Action</option>
                                    <option value="activate">Activate Selected</option>
                                    <option value="deactivate">Deactivate Selected</option>
                                    @can('delete features')
                                    <option value="delete">Delete Selected</option>
                                    @endcan
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-warning" id="bulkActionBtn" disabled>
                                        <i class="fas fa-cogs"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                <!-- Features Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                @can('edit features')
                                <th width="30">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                @endcan
                                <th>Name (EN)</th>
                                <th>Name (AR)</th>
                                <th>Category</th>
                                <th>Icon</th>
                                <th>Sort Order</th>
                                <th>Properties Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($features as $feature)
                            <tr>
                                @can('edit features')
                                <td>
                                    <input type="checkbox" name="selected_features[]" value="{{ $feature->id }}" class="feature-checkbox">
                                </td>
                                @endcan
                                <td>{{ $feature->name_en }}</td>
                                <td>{{ $feature->name_ar }}</td>
                                <td>
                                    @if($feature->category)
                                        <span class="badge badge-info">{{ $feature->category }}</span>
                                    @else
                                        <span class="text-muted">No Category</span>
                                    @endif
                                </td>
                                <td>
                                    @if($feature->icon)
                                        <i class="{{ $feature->icon }}"></i> {{ $feature->icon }}
                                    @else
                                        <span class="text-muted">No Icon</span>
                                    @endif
                                </td>
                                <td>{{ $feature->sort_order ?? 0 }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ $feature->properties_count }}</span>
                                </td>
                                <td>
                                    @if($feature->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @can('view features')
                                        <a href="{{ route('admin.features.show', $feature) }}" class="btn btn-info btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        @can('edit features')
                                        <a href="{{ route('admin.features.edit', $feature) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete features')
                                        <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this feature?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete" {{ $feature->properties_count > 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No features found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('edit features')
                </form>
                @endcan

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $features->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select All functionality
    $('#selectAll').change(function() {
        $('.feature-checkbox').prop('checked', this.checked);
        toggleBulkActionBtn();
    });

    // Individual checkbox change
    $('.feature-checkbox').change(function() {
        toggleBulkActionBtn();
        
        // Update select all checkbox
        var totalCheckboxes = $('.feature-checkbox').length;
        var checkedCheckboxes = $('.feature-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Toggle bulk action button
    function toggleBulkActionBtn() {
        var checkedCount = $('.feature-checkbox:checked').length;
        $('#bulkActionBtn').prop('disabled', checkedCount === 0);
    }

    // Bulk form submission
    $('#bulkForm').submit(function(e) {
        var checkedFeatures = $('.feature-checkbox:checked');
        if (checkedFeatures.length === 0) {
            e.preventDefault();
            alert('Please select at least one feature.');
            return false;
        }

        var action = $('select[name="bulk_action"]').val();
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected features?')) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
@endpush