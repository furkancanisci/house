@extends('admin.layouts.app')

@section('title', 'Cities Management')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Cities</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cities Management</h3>
                <div class="card-tools">
                    @can('manage cities')
                    <a href="{{ route('admin.cities.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New City
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
                                       placeholder="Search cities..." 
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
                            <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Cities Table -->
                <div id="citiesTable">
                    @include('admin.cities.partials.table')
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
            $('#citiesTable').html(data.html);
        });
    });

    // Delete confirmation
    $(document).on('click', '.delete-city', function(e) {
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