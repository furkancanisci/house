@extends('admin.layouts.app')

@section('title', 'Users & Agents Management')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users & Agents</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Users & Agents Management</h3>
                <div class="card-tools">
                    @can('create users')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" class="mb-3" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search users..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select name="role" class="form-control">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select name="email_verified" class="form-control">
                                    <option value="">All Verification</option>
                                    <option value="1" {{ request('email_verified') == '1' ? 'selected' : '' }}>Verified</option>
                                    <option value="0" {{ request('email_verified') == '0' ? 'selected' : '' }}>Unverified</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Users Table -->
                <div id="usersTable">
                    @include('admin.users.partials.table')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Role Assignment Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Role</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="roleForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modalRole">Select Role</label>
                        <select name="role" id="modalRole" class="form-control" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Role</button>
                </div>
            </form>
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
            $('#usersTable').html(data.html);
        });
    });

    // Toggle user status
    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();
        let userId = $(this).data('user');
        let currentStatus = $(this).data('status');
        let newStatus = currentStatus ? 'deactivate' : 'activate';
        
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${newStatus} this user?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, ${newStatus}!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/admin/users/${userId}/toggle-status`, {
                    _token: $('meta[name="csrf-token"]').attr('content')
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

    // Role assignment modal
    let currentUserId = null;
    $(document).on('click', '.assign-role', function(e) {
        e.preventDefault();
        currentUserId = $(this).data('user');
        let currentRole = $(this).data('role');
        
        $('#modalRole').val(currentRole);
        $('#roleModal').modal('show');
    });

    $('#roleForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!currentUserId) return;
        
        let formData = $(this).serialize();
        
        $.post(`/admin/users/${currentUserId}/assign-role`, formData)
        .done(function(response) {
            $('#roleModal').modal('hide');
            Swal.fire('Success', response.success, 'success');
            location.reload();
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
            Swal.fire('Error', error, 'error');
        });
    });

    // Delete confirmation
    $(document).on('click', '.delete-user', function(e) {
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

    // Impersonate confirmation
    $(document).on('click', '.impersonate-user', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        let userName = $(this).data('user-name');
        
        Swal.fire({
            title: 'Impersonate User?',
            text: `You will be logged in as ${userName}. You can return to your account anytime.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, impersonate!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush