@extends('admin.layouts.app')

@section('title', 'Permissions Management')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Permissions</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permissions Management</h3>
                @can('manage permissions')
                <div class="card-tools">
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create Permission
                    </a>
                </div>
                @endcan
            </div>
            <div class="card-body">
                @if($permissions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Guard</th>
                                <th>Roles Count</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $permission)
                            <tr>
                                <td>{{ $permission->id }}</td>
                                <td>
                                    <strong>{{ $permission->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $permission->guard_name }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ $permission->roles->count() }}</span>
                                </td>
                                <td>{{ $permission->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @can('manage permissions')
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.permissions.show', $permission) }}" class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger delete-permission-btn" title="Delete" 
                                                data-permission-id="{{ $permission->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $permissions->links() }}
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-key fa-3x text-muted mb-3"></i>
                    <h5>No permissions found</h5>
                    <p class="text-muted">Get started by creating your first permission.</p>
                    @can('manage permissions')
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Permission
                    </a>
                    @endcan
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($groupedPermissions->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permissions by Category</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($groupedPermissions as $category => $categoryPermissions)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h5 class="card-title text-capitalize">{{ $category }}</h5>
                                <span class="badge badge-primary float-right">{{ $categoryPermissions->count() }}</span>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    @foreach($categoryPermissions as $permission)
                                    <li>
                                        <i class="fas fa-key text-muted mr-2"></i>
                                        {{ $permission->name }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Delete Permission Modal -->
<div class="modal fade" id="deletePermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete Permission</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this permission? This action cannot be undone.</p>
                <p class="text-danger"><strong>Warning:</strong> Make sure no roles are using this permission before deleting.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deletePermissionForm" method="POST" style="display: inline;">
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
$(document).ready(function() {
    $('.delete-permission-btn').on('click', function() {
        const permissionId = $(this).data('permission-id');
        deletePermission(permissionId);
    });
});

function deletePermission(permissionId) {
    const form = document.getElementById('deletePermissionForm');
    form.action = `{{ route('admin.permissions.index') }}/${permissionId}`;
    $('#deletePermissionModal').modal('show');
}
</script>
@endpush