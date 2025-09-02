@extends('admin.layouts.app')

@section('title', 'Utilities Management')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Utilities</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Utilities Management</h3>
                <div class="card-tools">
                    @can('create utilities')
                    <a href="{{ route('admin.utilities.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Utility
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
                                       placeholder="Search utilities..." 
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
                            <a href="{{ route('admin.utilities.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Bulk Actions -->
                @can('edit utilities')
                <form method="POST" action="{{ route('admin.utilities.bulk') }}" id="bulkForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <select name="bulk_action" class="form-control" required>
                                    <option value="">Select Action</option>
                                    <option value="activate">Activate Selected</option>
                                    <option value="deactivate">Deactivate Selected</option>
                                    @can('delete utilities')
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

                <!-- Utilities Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                @can('edit utilities')
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
                            @forelse($utilities as $utility)
                            <tr>
                                @can('edit utilities')
                                <td>
                                    <input type="checkbox" name="selected_utilities[]" value="{{ $utility->id }}" class="utility-checkbox">
                                </td>
                                @endcan
                                <td>{{ $utility->name_en }}</td>
                                <td>{{ $utility->name_ar }}</td>
                                <td>
                                    @if($utility->category)
                                        <span class="badge badge-info">{{ $utility->category }}</span>
                                    @else
                                        <span class="text-muted">No Category</span>
                                    @endif
                                </td>
                                <td>
                                    @if($utility->icon)
                                        <i class="{{ $utility->icon }}"></i> {{ $utility->icon }}
                                    @else
                                        <span class="text-muted">No Icon</span>
                                    @endif
                                </td>
                                <td>{{ $utility->sort_order ?? 0 }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ $utility->properties_count }}</span>
                                </td>
                                <td>
                                    @if($utility->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @can('view utilities')
                                        <a href="{{ route('admin.utilities.show', $utility) }}" class="btn btn-info btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        @can('edit utilities')
                                        <a href="{{ route('admin.utilities.edit', $utility) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete utilities')
                                        <form method="POST" action="{{ route('admin.utilities.destroy', $utility) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this utility?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete" {{ $utility->properties_count > 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No utilities found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('edit utilities')
                </form>
                @endcan

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $utilities->appends(request()->query())->links() }}
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
        $('.utility-checkbox').prop('checked', this.checked);
        toggleBulkActionBtn();
    });

    // Individual checkbox change
    $('.utility-checkbox').change(function() {
        toggleBulkActionBtn();
        
        // Update select all checkbox
        var totalCheckboxes = $('.utility-checkbox').length;
        var checkedCheckboxes = $('.utility-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Toggle bulk action button
    function toggleBulkActionBtn() {
        var checkedCount = $('.utility-checkbox:checked').length;
        $('#bulkActionBtn').prop('disabled', checkedCount === 0);
    }

    // Bulk form submission
    $('#bulkForm').submit(function(e) {
        var checkedUtilities = $('.utility-checkbox:checked');
        if (checkedUtilities.length === 0) {
            e.preventDefault();
            alert('Please select at least one utility.');
            return false;
        }

        var action = $('select[name="bulk_action"]').val();
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected utilities?')) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
@endpush