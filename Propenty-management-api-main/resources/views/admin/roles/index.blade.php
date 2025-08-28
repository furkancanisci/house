@extends('admin.layouts.app')

@section('title', 'Roles Management')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Roles</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Roles Management</h3>
                @can('manage roles')
                <div class="card-tools">
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create Role
                    </a>
                </div>
                @endcan
            </div>
            <div class="card-body">
                @if($roles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Guard</th>
                                <th>Permissions Count</th>
                                <th>Users Count</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                    @if($role->name === 'SuperAdmin')
                                        <span class="badge badge-danger">System Role</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $role->guard_name }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ $role->permissions->count() }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $role->users->count() }}</span>
                                </td>
                                <td>{{ $role->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @can('manage roles')
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($role->name !== 'SuperAdmin')
                                        <button type="button" class="btn btn-danger delete-role-btn" title="Delete" 
                                                data-role-id="{{ $role->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                    <h5>No roles found</h5>
                    <p class="text-muted">Get started by creating your first role.</p>
                    @can('manage roles')
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Role
                    </a>
                    @endcan
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Role Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete Role</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                <p class="text-danger"><strong>Warning:</strong> Make sure no users are assigned to this role before deleting.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteRoleForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Role</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.delete-role-btn').on('click', function() {
        const roleId = $(this).data('role-id');
        deleteRole(roleId);
    });
});

function deleteRole(roleId) {
    const form = document.getElementById('deleteRoleForm');
    form.action = `{{ route('admin.roles.index') }}/${roleId}`;
    $('#deleteRoleModal').modal('show');
}
</script>
@endpush