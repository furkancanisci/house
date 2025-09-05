@extends('admin.layouts.app')

@section('title', 'Permission Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active">{{ $permission->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Permission Details: {{ $permission->name }}
                </h3>
                <div class="card-tools">
                    @can('manage permissions')
                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Permission
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Permission Name:</th>
                                <td>{{ $permission->name }}</td>
                            </tr>
                            <tr>
                                <th>Guard Name:</th>
                                <td><span class="badge badge-info">{{ $permission->guard_name }}</span></td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>
                                    @if($currentCategory)
                                        <span class="badge badge-secondary">{{ ucfirst($currentCategory) }}</span>
                                    @else
                                        <span class="text-muted">No category</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Total Roles:</th>
                                <td><span class="badge badge-primary">{{ $permission->roles->count() }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Created:</th>
                                <td>{{ $permission->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Updated:</th>
                                <td>{{ $permission->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Age:</th>
                                <td>{{ $permission->created_at->diffForHumans() }}</td>
                            </tr>
                            <tr>
                                <th>Total Users:</th>
                                <td>
                                    <span class="badge badge-success">
                                        {{ $permission->roles->sum(function($role) { return $role->users->count(); }) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($permission->description)
                <div class="mb-4">
                    <h5><i class="fas fa-info-circle"></i> Description</h5>
                    <div class="alert alert-info">
                        {{ $permission->description }}
                    </div>
                </div>
                @endif

                <!-- Roles Section -->
                <div class="roles-section">
                    <h4 class="mb-3">
                        <i class="fas fa-user-shield"></i> Roles with this Permission
                        <span class="badge badge-primary">{{ $permission->roles->count() }}</span>
                    </h4>
                    
                    @if($permission->roles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Guard</th>
                                        <th>Users</th>
                                        <th>Total Permissions</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permission->roles as $role)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $role->name == 'SuperAdmin' ? 'danger' : ($role->name == 'Admin' ? 'warning' : ($role->name == 'Agent' ? 'info' : 'secondary')) }}">
                                                {{ $role->name }}
                                            </span>
                                            @if($role->name === 'SuperAdmin')
                                                <small class="text-danger">(System Role)</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $role->guard_name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $role->users->count() }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">{{ $role->permissions->count() }}</span>
                                        </td>
                                        <td>{{ $role->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-info" title="View Role">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('manage roles')
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-warning" title="Edit Role">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            This permission is not currently assigned to any roles.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Usage Analytics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Usage Analytics
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-user-shield"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Assigned Roles</span>
                                <span class="info-box-number">{{ $permission->roles->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-users"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Users</span>
                                <span class="info-box-number">
                                    {{ $permission->roles->sum(function($role) { return $role->users->count(); }) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-percentage"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Role Coverage</span>
                                <span class="info-box-number">
                                    {{ $totalRoles > 0 ? round(($permission->roles->count() / $totalRoles) * 100, 1) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($permission->roles->count() > 0)
                <h6>Role Distribution</h6>
                <div class="progress-group">
                    @foreach($permission->roles as $role)
                    @php
                        $percentage = $totalUsers > 0 ? ($role->users->count() / $totalUsers) * 100 : 0;
                    @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>{{ $role->name }}</span>
                            <span>{{ $role->users->count() }} users</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-{{ $role->name == 'SuperAdmin' ? 'danger' : ($role->name == 'Admin' ? 'warning' : 'primary') }}" 
                                 style="width: {{ max($percentage, 5) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                @can('manage permissions')
                <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit This Permission
                </a>
                @endcan
                
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Back to Permissions
                </a>

                @can('manage permissions')
                <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Delete Permission
                </button>
                @endcan
            </div>
        </div>

        @if($relatedPermissions->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Related Permissions</h3>
            </div>
            <div class="card-body">
                <small class="text-muted">Other permissions in the "{{ $currentCategory }}" category:</small>
                <div class="mt-2">
                    @foreach($relatedPermissions as $relatedPermission)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small">{{ $relatedPermission->name }}</span>
                        <a href="{{ route('admin.permissions.show', $relatedPermission) }}" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Impact Assessment</h3>
            </div>
            <div class="card-body">
                @if($permission->roles->count() > 0)
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Deletion Impact</h6>
                    <p class="small mb-2">Deleting this permission will affect:</p>
                    <ul class="small mb-0">
                        <li>{{ $permission->roles->count() }} role(s)</li>
                        <li>{{ $permission->roles->sum(function($role) { return $role->users->count(); }) }} user(s) across all roles</li>
                    </ul>
                </div>
                @else
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle"></i> Safe to Delete</h6>
                    <p class="small mb-0">This permission is not assigned to any roles and can be safely deleted.</p>
                </div>
                @endif

                @if($permission->roles->contains('name', 'SuperAdmin'))
                <div class="alert alert-danger">
                    <i class="fas fa-shield-alt"></i>
                    <strong>System Protected:</strong> This permission is used by SuperAdmin role and should not be deleted.
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission History</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-green">{{ $permission->created_at->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-plus bg-blue"></i>
                        <div class="timeline-item">
                            <h6 class="timeline-header">Permission Created</h6>
                            <div class="timeline-body">
                                Permission "{{ $permission->name }}" was created with {{ $permission->guard_name }} guard.
                            </div>
                        </div>
                    </div>

                    @if($permission->updated_at != $permission->created_at)
                    <div class="time-label">
                        <span class="bg-yellow">{{ $permission->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-edit bg-yellow"></i>
                        <div class="timeline-item">
                            <h6 class="timeline-header">Last Updated</h6>
                            <div class="timeline-body">
                                Permission details were last modified {{ $permission->updated_at->diffForHumans() }}.
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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
                <p>Are you sure you want to delete the permission <strong>{{ $permission->name }}</strong>?</p>
                @if($permission->roles->count() > 0)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This permission is currently used by {{ $permission->roles->count() }} role(s) affecting {{ $permission->roles->sum(function($role) { return $role->users->count(); }) }} user(s).
                </div>
                @endif
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Permission</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    $('#deleteModal').modal('show');
}
</script>
@endpush