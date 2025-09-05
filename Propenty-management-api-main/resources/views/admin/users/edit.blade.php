@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users & Agents</a></li>
    <li class="breadcrumb-item active">Edit {{ $user->full_name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit User: {{ $user->full_name }}</h3>
            </div>

            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" 
                                       value="{{ old('first_name', $user->first_name) }}" required>
                                @error('first_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                                       value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Leave blank to keep current password. Minimum 8 characters for new password.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                <small class="form-text text-muted">Only required if changing password.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    @can('manage permissions')
                    <div class="form-group">
                        <label>Direct Permissions (Optional)</label>
                        <small class="form-text text-muted mb-2">
                            These permissions will be assigned directly to the user in addition to their role permissions.
                        </small>
                        
                        <div class="permissions-container">
                            @if($groupedPermissions->count() > 0)
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="selectAllPerms">
                                            <label class="custom-control-label small font-weight-bold" for="selectAllPerms">
                                                Select All Direct Permissions
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                        <div class="row">
                                            @foreach($groupedPermissions as $category => $categoryPermissions)
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-primary">
                                                    <i class="fas fa-{{ $category == 'dashboard' ? 'tachometer-alt' : ($category == 'users' ? 'users' : ($category == 'properties' ? 'home' : 'cog')) }}"></i>
                                                    {{ ucfirst($category) }}
                                                    <span class="badge badge-secondary">{{ $categoryPermissions->count() }}</span>
                                                </h6>
                                                @foreach($categoryPermissions as $permission)
                                                <div class="custom-control custom-checkbox mb-1">
                                                    <input type="checkbox" class="custom-control-input direct-permission-checkbox" 
                                                           name="direct_permissions[]" value="{{ $permission->id }}" 
                                                           id="direct-permission-{{ $permission->id }}"
                                                           {{ $user->hasDirectPermission($permission->name) ? 'checked' : '' }}>
                                                    <label class="custom-control-label small" for="direct-permission-{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                        @if($user->hasPermissionTo($permission->name) && !$user->hasDirectPermission($permission->name))
                                                            <small class="text-muted">(via role)</small>
                                                        @endif
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle"></i>
                                    <small>
                                        <strong>Note:</strong> Direct permissions override role-based permissions. 
                                        Use sparingly for special cases only.
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No permissions available for direct assignment.
                                </div>
                            @endif
                        </div>
                    </div>
                    @endcan

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="form-text text-muted">Active users can log in and access the system</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="email_verified" name="email_verified" value="1" 
                                           {{ old('email_verified', $user->email_verified_at) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="email_verified">Email Verified</label>
                                </div>
                                <small class="form-text text-muted">Mark email as verified (user won't need to verify)</small>
                            </div>
                        </div>
                    </div>

                    @if($user->id === auth()->id())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> You are editing your own account. Be careful when changing your role or status.
                    </div>
                    @endif
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    @can('view users')
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    @endcan
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <!-- User Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">User ID:</th>
                                <td>#{{ $user->id }}</td>
                            </tr>
                            <tr>
                                <th>Current Role:</th>
                                <td>
                                    @if($user->roles->isNotEmpty())
                                        <span class="badge badge-{{ $user->roles->first()->name == 'SuperAdmin' ? 'danger' : ($user->roles->first()->name == 'Admin' ? 'warning' : ($user->roles->first()->name == 'Agent' ? 'info' : 'secondary')) }}">
                                            {{ $user->roles->first()->name }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">No Role</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Email Status:</th>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge badge-success">Verified</span>
                                    @else
                                        <span class="badge badge-warning">Unverified</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Joined:</th>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Role Descriptions</h3>
            </div>
            <div class="card-body">
                @foreach($roles as $role)
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-{{ $role->name == 'SuperAdmin' ? 'danger' : ($role->name == 'Admin' ? 'warning' : ($role->name == 'Agent' ? 'info' : 'secondary')) }}">
                        <i class="fas fa-{{ $role->name == 'SuperAdmin' ? 'crown' : ($role->name == 'Admin' ? 'user-shield' : ($role->name == 'Agent' ? 'user-tie' : 'user')) }}"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $role->name }}</span>
                        <span class="info-box-number">
                            @switch($role->name)
                                @case('SuperAdmin')
                                    Full system access
                                    @break
                                @case('Admin')
                                    Administrative access
                                    @break
                                @case('Moderator')
                                    Content moderation
                                    @break
                                @case('Agent')
                                    Property management
                                    @break
                                @default
                                    Standard user access
                            @endswitch
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Verify email changes carefully</li>
                    <li><i class="fas fa-check text-success"></i> Role changes affect user permissions</li>
                    <li><i class="fas fa-check text-success"></i> Password changes require confirmation</li>
                    <li><i class="fas fa-check text-success"></i> Deactivating users prevents login</li>
                    <li><i class="fas fa-check text-success"></i> Keep user information up to date</li>
                </ul>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> Changes to user roles and status take effect immediately.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for JavaScript -->
<div id="jsData" 
     data-is-self-editing="{{ $user->id === auth()->id() ? 'true' : 'false' }}" 
     style="display: none;"></div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Get self-editing flag from data attribute
    var isSelfEditing = $('#jsData').data('is-self-editing') === 'true';
    
    // Password confirmation validation
    $('#password_confirmation').on('input', function() {
        let password = $('#password').val();
        let confirm = $(this).val();
        
        if (password !== confirm && confirm !== '') {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<span class="invalid-feedback">Passwords do not match</span>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Role selection helper
    $('#role').on('change', function() {
        let role = $(this).val();
        let descriptions = {
            'SuperAdmin': 'Complete system access including user management and system settings',
            'Admin': 'Administrative access to manage properties, users, and content',
            'Moderator': 'Can moderate content and manage property listings',
            'Agent': 'Can manage their own properties and leads',
            'User': 'Standard user with basic property browsing capabilities'
        };
        
        if (role && descriptions[role]) {
            if (!$('#roleDescription').length) {
                $(this).after('<small id="roleDescription" class="form-text text-info"></small>');
            }
            $('#roleDescription').text(descriptions[role]);
        } else {
            $('#roleDescription').remove();
        }
    });

    // Warning for self-editing
    if (isSelfEditing) {
        $('#role, #is_active').on('change', function() {
            if (!$('#selfEditWarning').length) {
                $(this).closest('.form-group').append('<div id="selfEditWarning" class="alert alert-warning mt-2"><i class="fas fa-exclamation-triangle"></i> You are modifying your own account. Changes will affect your access.</div>');
            }
        });
    }

    // Password field enhancement
    $('#password').on('input', function() {
        let password = $(this).val();
        if (password.length > 0 && password.length < 8) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<span class="invalid-feedback">Password must be at least 8 characters</span>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Direct Permissions Management
    @can('manage permissions')
    // Select all direct permissions
    $('#selectAllPerms').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.direct-permission-checkbox').prop('checked', isChecked);
        updatePermissionsInfo();
    });

    // Individual permission checkbox change
    $('.direct-permission-checkbox').on('change', function() {
        updatePermissionsInfo();
        
        // Update select all checkbox
        const totalPermissions = $('.direct-permission-checkbox').length;
        const checkedPermissions = $('.direct-permission-checkbox:checked').length;
        $('#selectAllPerms').prop('checked', checkedPermissions === totalPermissions);
    });

    function updatePermissionsInfo() {
        const selectedCount = $('.direct-permission-checkbox:checked').length;
        
        if (selectedCount > 0) {
            if (!$('#directPermInfo').length) {
                $('.permissions-container .alert-info').after(
                    '<div id="directPermInfo" class="alert alert-warning mt-2">' +
                    '<i class="fas fa-exclamation-triangle"></i> ' +
                    '<span id="directPermCount">' + selectedCount + '</span> direct permission(s) selected. ' +
                    'These will be granted in addition to role permissions.' +
                    '</div>'
                );
            } else {
                $('#directPermCount').text(selectedCount);
            }
        } else {
            $('#directPermInfo').remove();
        }
    }

    // Initialize permissions info
    updatePermissionsInfo();

    // Role change warning for direct permissions
    $('#role').on('change', function() {
        const selectedPerms = $('.direct-permission-checkbox:checked').length;
        if (selectedPerms > 0) {
            if (!$('#roleChangeWarning').length) {
                $(this).after(
                    '<div id="roleChangeWarning" class="alert alert-info mt-2">' +
                    '<i class="fas fa-info-circle"></i> ' +
                    'This user has ' + selectedPerms + ' direct permission(s) assigned. ' +
                    'Role change will not affect direct permissions.' +
                    '</div>'
                );
            }
        } else {
            $('#roleChangeWarning').remove();
        }
    });
    @endcan
});
</script>
@endpush