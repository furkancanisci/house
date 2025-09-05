@extends('admin.layouts.app')

@section('title', 'Edit Permission')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active">Edit {{ $permission->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Permission: {{ $permission->name }}</h3>
            </div>

            <form action="{{ route('admin.permissions.update', $permission) }}" method="POST" id="permissionForm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Permission Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $permission->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            Use descriptive names like "view users", "create properties", "manage settings"
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="guard_name">Guard Name *</label>
                        <select name="guard_name" id="guard_name" class="form-control @error('guard_name') is-invalid @enderror" required>
                            <option value="web" {{ old('guard_name', $permission->guard_name) == 'web' ? 'selected' : '' }}>Web</option>
                        </select>
                        @error('guard_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Guard defines which authentication system this permission applies to</small>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <div class="row">
                            <div class="col-md-6">
                                <select id="categorySelect" class="form-control">
                                    <option value="">Select existing category...</option>
                                    @foreach($existingCategories as $category)
                                        <option value="{{ $category }}" {{ $currentCategory == $category ? 'selected' : '' }}>
                                            {{ ucfirst($category) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="customCategory" class="form-control" 
                                       value="{{ $currentCategory }}"
                                       placeholder="Or enter new category...">
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Current category: <strong>{{ $currentCategory ?: 'None' }}</strong>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Describe what this permission allows users to do...">{{ old('description', $permission->description ?? '') }}</textarea>
                        <small class="form-text text-muted">
                            Helps other administrators understand the purpose of this permission
                        </small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Permission
                    </button>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <a href="{{ route('admin.permissions.show', $permission) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Name:</th>
                        <td>{{ $permission->name }}</td>
                    </tr>
                    <tr>
                        <th>Guard:</th>
                        <td><span class="badge badge-info">{{ $permission->guard_name }}</span></td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td><span class="badge badge-secondary">{{ $currentCategory ?: 'None' }}</span></td>
                    </tr>
                    <tr>
                        <th>Roles:</th>
                        <td><span class="badge badge-primary">{{ $permission->roles->count() }}</span></td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $permission->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Updated:</th>
                        <td>{{ $permission->updated_at->format('M d, Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($permission->roles->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Roles with this Permission
                    <span class="badge badge-primary">{{ $permission->roles->count() }}</span>
                </h3>
            </div>
            <div class="card-body">
                @foreach($permission->roles as $role)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="badge badge-{{ $role->name == 'SuperAdmin' ? 'danger' : ($role->name == 'Admin' ? 'warning' : 'info') }}">
                            {{ $role->name }}
                        </span>
                        <small class="text-muted">({{ $role->users->count() }} users)</small>
                    </div>
                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Impact Analysis</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Changing this permission will affect:</h6>
                    <ul class="mb-0">
                        <li><strong>{{ $permission->roles->count() }}</strong> roles</li>
                        <li><strong>{{ $permission->roles->sum(function($role) { return $role->users->count(); }) }}</strong> users (total across all roles)</li>
                    </ul>
                </div>
                
                @if($permission->roles->contains('name', 'SuperAdmin'))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> This permission is assigned to SuperAdmin role and will be automatically maintained.
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Test changes in development first</li>
                    <li><i class="fas fa-check text-success"></i> Consider impact on existing users</li>
                    <li><i class="fas fa-check text-success"></i> Keep permission names consistent</li>
                    <li><i class="fas fa-check text-success"></i> Document any breaking changes</li>
                </ul>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> Changing permission names may affect application functionality.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Category selection logic
    $('#categorySelect').on('change', function() {
        const category = $(this).val();
        if (category) {
            $('#customCategory').val(category);
        }
    });

    $('#customCategory').on('input', function() {
        const category = $(this).val();
        if (category) {
            $('#categorySelect').val('');
        }
    });

    // Form validation
    $('#permissionForm').on('submit', function(e) {
        const permissionName = $('#name').val().trim();
        
        if (!permissionName) {
            e.preventDefault();
            alert('Please enter a permission name.');
            $('#name').focus();
            return false;
        }
        
        // Basic validation for permission name format
        if (!/^[a-zA-Z0-9\s\-_]+$/.test(permissionName)) {
            e.preventDefault();
            alert('Permission name can only contain letters, numbers, spaces, hyphens, and underscores.');
            $('#name').focus();
            return false;
        }

        // Confirm if this permission is used by many roles
        const rolesCount = {{ $permission->roles->count() }};
        const usersCount = {{ $permission->roles->sum(function($role) { return $role->users->count(); }) }};
        
        if (rolesCount > 0 || usersCount > 0) {
            const currentName = '{{ $permission->name }}';
            const newName = permissionName;
            
            if (currentName !== newName) {
                const confirmed = confirm(
                    `This permission is currently used by ${rolesCount} role(s) affecting ${usersCount} user(s).\n\n` +
                    `Are you sure you want to change the name from "${currentName}" to "${newName}"?\n\n` +
                    `This may affect application functionality.`
                );
                
                if (!confirmed) {
                    e.preventDefault();
                    return false;
                }
            }
        }
        
        return true;
    });

    // Highlight changes
    const originalName = '{{ $permission->name }}';
    const originalCategory = '{{ $currentCategory }}';
    
    $('#name').on('input', function() {
        const current = $(this).val();
        if (current !== originalName) {
            $(this).addClass('border-warning');
        } else {
            $(this).removeClass('border-warning');
        }
    });

    $('#customCategory').on('input', function() {
        const current = $(this).val();
        if (current !== originalCategory) {
            $(this).addClass('border-warning');
        } else {
            $(this).removeClass('border-warning');
        }
    });
});
</script>
@endpush