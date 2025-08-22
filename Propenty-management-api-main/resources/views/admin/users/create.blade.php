@extends('admin.layouts.app')

@section('title', 'Create User')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users & Agents</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create New User</h3>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" 
                                       value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                                       value="{{ old('last_name') }}" required>
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
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Minimum 8 characters</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password *</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="form-text text-muted">Active users can log in and access the system</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="email_verified" name="email_verified" value="1" 
                                           {{ old('email_verified') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="email_verified">Email Verified</label>
                                </div>
                                <small class="form-text text-muted">Mark email as verified (user won't need to verify)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
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
                    <li><i class="fas fa-check text-success"></i> Use real email addresses for communication</li>
                    <li><i class="fas fa-check text-success"></i> Assign appropriate roles based on responsibilities</li>
                    <li><i class="fas fa-check text-success"></i> Set strong passwords (minimum 8 characters)</li>
                    <li><i class="fas fa-check text-success"></i> Mark email as verified for trusted users</li>
                    <li><i class="fas fa-check text-success"></i> Keep user information up to date</li>
                </ul>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Tip:</strong> Users will receive a welcome email with their login credentials.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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
});
</script>
@endpush