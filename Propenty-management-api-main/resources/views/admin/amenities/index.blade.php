@extends('admin.layouts.app')

@section('title', 'Amenities Management')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Amenities</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Amenities Management</h3>
                <div class="card-tools">
                    @can('create amenities')
                    <a href="{{ route('admin.amenities.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Amenity
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
                                       placeholder="Search amenities..." 
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
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.amenities.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Bulk Actions -->
                @can('edit amenities')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <select id="bulkAction" class="form-control">
                                <option value="">Bulk Actions</option>
                                <option value="activate">Activate Selected</option>
                                <option value="deactivate">Deactivate Selected</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <div class="input-group-append">
                                <button type="button" id="applyBulkAction" class="btn btn-warning">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

                <!-- Amenities Table -->
                <div id="amenitiesTable">
                    @include('admin.amenities.partials.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });

    // AJAX pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        
        $.get(url, function(data) {
            $('#amenitiesTable').html(data.html);
        });
    });

    // Select all checkbox
    $(document).on('change', '#selectAll', function() {
        $('.amenity-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Update select all when individual checkboxes change
    $(document).on('change', '.amenity-checkbox', function() {
        const total = $('.amenity-checkbox').length;
        const checked = $('.amenity-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
    });

    // Bulk actions
    $('#applyBulkAction').on('click', function() {
        const action = $('#bulkAction').val();
        const selected = $('.amenity-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!action) {
            Swal.fire('Error', 'Please select an action', 'error');
            return;
        }

        if (selected.length === 0) {
            Swal.fire('Error', 'Please select at least one amenity', 'error');
            return;
        }

        let confirmText = 'Are you sure you want to ' + action + ' the selected amenities?';
        
        Swal.fire({
            title: 'Confirm Action',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("admin.amenities.bulk") }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    action: action,
                    amenities: selected
                })
                .done(function(response) {
                    Swal.fire('Success', response.success, 'success');
                    location.reload();
                })
                .fail(function(xhr) {
                    const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                    Swal.fire('Error', error, 'error');
                });
            }
        });
    });

    // Delete confirmation
    $(document).on('click', '.delete-amenity', function(e) {
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