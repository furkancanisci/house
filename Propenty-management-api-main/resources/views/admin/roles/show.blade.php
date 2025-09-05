@extends('admin.layouts.app')

@section('title', 'Role Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">{{ $role->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Role Details: {{ $role->name }}
                    @if($role->name === 'SuperAdmin')
                        <span class="badge badge-danger ml-2">System Role</span>
                    @endif
                </h3>
                <div class="card-tools">
                    @can('manage roles')
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Role
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Role Name:</th>
                                <td>{{ $role->name }}</td>
                            </tr>
                            <tr>
                                <th>Guard Name:</th>
                                <td><span class="badge badge-info">{{ $role->guard_name }}</span></td>
                            </tr>
                            <tr>
                                <th>Total Users:</th>
                                <td><span class="badge badge-primary">{{ $role->users->count() }}</span></td>
                            </tr>
                            <tr>
                                <th>Total Permissions:</th>
                                <td><span class="badge badge-success">{{ $role->permissions->count() }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Created:</th>
                                <td>{{ $role->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Updated:</th>
                                <td>{{ $role->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Age:</th>
                                <td>{{ $role->created_at->diffForHumans() }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge badge-success">Active</span>
                                    @if($role->name === 'SuperAdmin')
                                        <span class="badge badge-warning">Protected</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Permissions Section -->
                <div class="permissions-section">
                    <h4 class="mb-3">
                        <i class="fas fa-key"></i> Assigned Permissions
                        <span class="badge badge-success">{{ $role->permissions->count() }}</span>
                    </h4>
                    
                    @if($groupedPermissions->count() > 0)
                        <div class="row">
                            @foreach($groupedPermissions as $category => $categoryPermissions)
                            <div class="col-md-6 mb-4">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-{{ $category == 'dashboard' ? 'tachometer-alt' : ($category == 'users' ? 'users' : ($category == 'properties' ? 'home' : 'cog')) }}"></i>
                                            {{ ucfirst($category) }}
                                            <span class="badge badge-primary float-right">
                                                {{ $categoryPermissions->where(function($p) use ($role) { return $role->hasPermissionTo($p->name); })->count() }}/{{ $categoryPermissions->count() }}
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($categoryPermissions as $permission)
                                        <div class="permission-item mb-2">
                                            @if($role->hasPermissionTo($permission->name))
                                                <i class="fas fa-check-circle text-success"></i>
                                                <span class="text-dark">{{ $permission->name }}</span>
                                            @else
                                                <i class="fas fa-times-circle text-muted"></i>
                                                <span class="text-muted">{{ $permission->name }}</span>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No permissions have been assigned to this role.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Users with this role -->
        @if($role->users->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i> Users with {{ $role->name }} Role
                    <span class="badge badge-primary">{{ $role->users->count() }}</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Properties</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($role->users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) }}" 
                                             class="img-circle mr-2" width="32" height="32" alt="User">
                                        <div>
                                            <div class="font-weight-bold">{{ $user->full_name }}</div>
                                            <small class="text-muted">#{{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                    @if($user->email_verified_at)
                                        <span class="badge badge-info">Verified</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-success">{{ $user->properties->count() }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info" title="View User">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit users')
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Stats</h3>
            </div>
            <div class="card-body">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Users</span>
                        <span class="info-box-number">{{ $role->users->count() }}</span>
                    </div>
                </div>

                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-key"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Permissions</span>
                        <span class="info-box-number">{{ $role->permissions->count() }}</span>
                    </div>
                </div>

                <div class="info-box mb-3">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-home"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Properties</span>
                        <span class="info-box-number">{{ $role->users->sum(function($user) { return $user->properties->count(); }) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission Summary</h3>
            </div>
            <div class="card-body">
                @foreach($groupedPermissions as $category => $categoryPermissions)
                @php
                    $hasPermissions = $categoryPermissions->where(function($p) use ($role) { return $role->hasPermissionTo($p->name); })->count();
                    $totalPermissions = $categoryPermissions->count();
                    $percentage = $totalPermissions > 0 ? ($hasPermissions / $totalPermissions) * 100 : 0;
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>{{ ucfirst($category) }}</span>
                        <span>{{ $hasPermissions }}/{{ $totalPermissions }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-{{ $percentage == 100 ? 'success' : ($percentage > 50 ? 'warning' : 'danger') }}" 
                             style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                @can('manage roles')
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit This Role
                </a>
                @endcan
                
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Back to Roles
                </a>

                @if($role->name !== 'SuperAdmin')
                @can('manage roles')
                <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Delete Role
                </button>
                @endcan
                @endif
            </div>
        </div>
    </div>
</div>

@if($role->name !== 'SuperAdmin')
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm Deletion</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the role <strong>{{ $role->name }}</strong>?</p>
                @if($role->users->count() > 0)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This role is currently assigned to {{ $role->users->count() }} user(s).
                    They will lose all permissions associated with this role.
                </div>
                @endif
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Role</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function confirmDelete() {
    $('#deleteModal').modal('show');
}
</script>
@endpush