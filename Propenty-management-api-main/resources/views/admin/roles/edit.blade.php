@extends('admin.layouts.app')

@section('title', 'Edit Role')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">Edit {{ $role->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Role: {{ $role->name }}</h3>
                @if($role->name === 'SuperAdmin')
                    <span class="badge badge-danger float-right">System Role</span>
                @endif
            </div>

            <form action="{{ route('admin.roles.update', $role) }}" method="POST" id="roleForm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Role Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $role->name) }}" 
                               {{ $role->name === 'SuperAdmin' ? 'readonly' : 'required' }}>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        @if($role->name === 'SuperAdmin')
                            <small class="form-text text-muted">System role name cannot be changed</small>
                        @else
                            <small class="form-text text-muted">Role name should be unique and descriptive</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="guard_name">Guard Name *</label>
                        <select name="guard_name" id="guard_name" class="form-control @error('guard_name') is-invalid @enderror" 
                                {{ $role->name === 'SuperAdmin' ? 'disabled' : 'required' }}>
                            <option value="web" {{ old('guard_name', $role->guard_name) == 'web' ? 'selected' : '' }}>Web</option>
                        </select>
                        @if($role->name === 'SuperAdmin')
                            <input type="hidden" name="guard_name" value="{{ $role->guard_name }}">
                        @endif
                        @error('guard_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Assign Permissions</label>
                        <div class="permissions-container">
                            @if($groupedPermissions->count() > 0)
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="selectAll" 
                                                   {{ $role->name === 'SuperAdmin' ? 'disabled checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="selectAll">
                                                Select All Permissions
                                                @if($role->name === 'SuperAdmin')
                                                    <span class="badge badge-info">Auto-assigned</span>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                @if($role->name === 'SuperAdmin')
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        SuperAdmin role automatically has all permissions and cannot be modified.
                                    </div>
                                @endif
                                
                                <div class="row">
                                    @foreach($groupedPermissions as $category => $categoryPermissions)
                                    <div class="col-md-6 mb-4">
                                        <div class="card card-outline card-primary">
                                            <div class="card-header p-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input category-checkbox" 
                                                           id="category-{{ Str::slug($category) }}"
                                                           {{ $role->name === 'SuperAdmin' ? 'disabled checked' : '' }}>
                                                    <label class="custom-control-label font-weight-bold" for="category-{{ Str::slug($category) }}">
                                                        {{ ucfirst($category) }}
                                                        <span class="badge badge-secondary">{{ $categoryPermissions->count() }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body p-2">
                                                @foreach($categoryPermissions as $permission)
                                                <div class="custom-control custom-checkbox mb-1">
                                                    <input type="checkbox" class="custom-control-input permission-checkbox" 
                                                           name="permissions[]" value="{{ $permission->id }}" 
                                                           id="permission-{{ $permission->id }}"
                                                           data-category="{{ Str::slug($category) }}"
                                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                                           {{ $role->name === 'SuperAdmin' ? 'disabled' : '' }}>
                                                    <label class="custom-control-label" for="permission-{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if($role->name === 'SuperAdmin')
                                    @foreach($permissions as $permission)
                                        <input type="hidden" name="permissions[]" value="{{ $permission->id }}">
                                    @endforeach
                                @endif
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No permissions available. Create some permissions first.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Role
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    @if($role->name !== 'SuperAdmin')
                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Role Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Role Name:</th>
                        <td>{{ $role->name }}</td>
                    </tr>
                    <tr>
                        <th>Guard:</th>
                        <td><span class="badge badge-info">{{ $role->guard_name }}</span></td>
                    </tr>
                    <tr>
                        <th>Users:</th>
                        <td><span class="badge badge-primary">{{ $role->users->count() }}</span></td>
                    </tr>
                    <tr>
                        <th>Permissions:</th>
                        <td><span class="badge badge-success">{{ $role->permissions->count() }}</span></td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $role->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Updated:</th>
                        <td>{{ $role->updated_at->format('M d, Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($role->users->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Users with this Role</h3>
            </div>
            <div class="card-body">
                @foreach($role->users->take(10) as $user)
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) }}" 
                         class="img-circle mr-2" width="32" height="32" alt="User">
                    <div>
                        <div class="font-weight-bold">{{ $user->full_name }}</div>
                        <small class="text-muted">{{ $user->email }}</small>
                    </div>
                </div>
                @endforeach
                @if($role->users->count() > 10)
                <small class="text-muted">... and {{ $role->users->count() - 10 }} more users</small>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Test permission changes carefully</li>
                    <li><i class="fas fa-check text-success"></i> Consider impact on existing users</li>
                    <li><i class="fas fa-check text-success"></i> Document any major changes</li>
                    <li><i class="fas fa-check text-success"></i> Follow principle of least privilege</li>
                </ul>
                
                @if($role->name === 'SuperAdmin')
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> SuperAdmin role has all permissions automatically assigned.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($role->name !== 'SuperAdmin')
    // Select all functionality
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.permission-checkbox, .category-checkbox').prop('checked', isChecked);
    });

    // Category select all functionality
    $('.category-checkbox').on('change', function() {
        const category = $(this).attr('id').replace('category-', '');
        const isChecked = $(this).is(':checked');
        
        $(`.permission-checkbox[data-category="${category}"]`).prop('checked', isChecked);
        
        updateSelectAllState();
    });

    // Individual permission change
    $('.permission-checkbox').on('change', function() {
        const category = $(this).data('category');
        const totalInCategory = $(`.permission-checkbox[data-category="${category}"]`).length;
        const checkedInCategory = $(`.permission-checkbox[data-category="${category}"]:checked`).length;
        
        // Update category checkbox
        $(`#category-${category}`).prop('checked', checkedInCategory === totalInCategory);
        
        updateSelectAllState();
    });

    function updateSelectAllState() {
        const totalPermissions = $('.permission-checkbox').length;
        const checkedPermissions = $('.permission-checkbox:checked').length;
        
        $('#selectAll').prop('checked', checkedPermissions === totalPermissions);
    }

    // Initialize category checkboxes based on current selections
    $('.category-checkbox').each(function() {
        const category = $(this).attr('id').replace('category-', '');
        const totalInCategory = $(`.permission-checkbox[data-category="${category}"]`).length;
        const checkedInCategory = $(`.permission-checkbox[data-category="${category}"]:checked`).length;
        
        $(this).prop('checked', checkedInCategory === totalInCategory);
    });

    updateSelectAllState();

    // Form validation
    $('#roleForm').on('submit', function(e) {
        const roleName = $('#name').val().trim();
        const checkedPermissions = $('.permission-checkbox:checked').length;
        
        if (!roleName) {
            e.preventDefault();
            alert('Please enter a role name.');
            $('#name').focus();
            return false;
        }
        
        if (checkedPermissions === 0) {
            e.preventDefault();
            alert('Please select at least one permission for this role.');
            return false;
        }
        
        return true;
    });
    @endif
});
</script>
@endpush