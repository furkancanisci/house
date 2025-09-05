@extends('admin.layouts.app')

@section('title', 'Create Role')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create New Role</h3>
            </div>

            <form action="{{ route('admin.roles.store') }}" method="POST" id="roleForm">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Role Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Role name should be unique and descriptive (e.g., Manager, Editor)</small>
                    </div>

                    <div class="form-group">
                        <label for="guard_name">Guard Name *</label>
                        <select name="guard_name" id="guard_name" class="form-control @error('guard_name') is-invalid @enderror" required>
                            <option value="web" {{ old('guard_name', 'web') == 'web' ? 'selected' : '' }}>Web</option>
                        </select>
                        @error('guard_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Guard defines which authentication system this role applies to</small>
                    </div>

                    <div class="form-group">
                        <label>Assign Permissions</label>
                        <div class="permissions-container">
                            @if($groupedPermissions->count() > 0)
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="selectAll">
                                            <label class="custom-control-label font-weight-bold" for="selectAll">
                                                Select All Permissions
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    @foreach($groupedPermissions as $category => $categoryPermissions)
                                    <div class="col-md-6 mb-4">
                                        <div class="card card-outline card-primary">
                                            <div class="card-header p-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input category-checkbox" 
                                                           id="category-{{ Str::slug($category) }}">
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
                                                           {{ is_array(old('permissions')) && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
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
                        <i class="fas fa-save"></i> Create Role
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Role Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Use clear, descriptive role names</li>
                    <li><i class="fas fa-check text-success"></i> Follow principle of least privilege</li>
                    <li><i class="fas fa-check text-success"></i> Group related permissions together</li>
                    <li><i class="fas fa-check text-success"></i> Test roles before assigning to users</li>
                    <li><i class="fas fa-check text-success"></i> Document role purposes and responsibilities</li>
                </ul>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> Be careful when assigning powerful permissions like user management or system settings.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission Categories</h3>
            </div>
            <div class="card-body">
                @foreach($groupedPermissions as $category => $categoryPermissions)
                <div class="info-box mb-2">
                    <span class="info-box-icon bg-primary">
                        <i class="fas fa-{{ $category == 'dashboard' ? 'tachometer-alt' : ($category == 'users' ? 'users' : ($category == 'properties' ? 'home' : 'cog')) }}"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ ucfirst($category) }}</span>
                        <span class="info-box-number">{{ $categoryPermissions->count() }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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
});
</script>
@endpush