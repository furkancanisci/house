@extends('admin.layouts.app')

@section('title', 'Create Permission')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active">Create</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create New Permission</h3>
            </div>

            <form action="{{ route('admin.permissions.store') }}" method="POST" id="permissionForm">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Permission Name *</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required placeholder="e.g., create posts">
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
                            <option value="web" {{ old('guard_name', 'web') == 'web' ? 'selected' : '' }}>Web</option>
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
                                        <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="customCategory" class="form-control" 
                                       placeholder="Or enter new category...">
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Permissions are grouped by category (e.g., users, properties, dashboard)
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Describe what this permission allows users to do...">{{ old('description') }}</textarea>
                        <small class="form-text text-muted">
                            Helps other administrators understand the purpose of this permission
                        </small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Permission
                    </button>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission Guidelines</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Use clear, action-based names</li>
                    <li><i class="fas fa-check text-success"></i> Follow consistent naming patterns</li>
                    <li><i class="fas fa-check text-success"></i> Group related permissions by category</li>
                    <li><i class="fas fa-check text-success"></i> Start with basic permissions first</li>
                    <li><i class="fas fa-check text-success"></i> Test permissions after creation</li>
                </ul>
                
                <div class="alert alert-info">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Tip:</strong> Common patterns include "view [resource]", "create [resource]", "edit [resource]", "delete [resource]"
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Common Permission Examples</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>User Management:</strong>
                    <ul class="small">
                        <li>view users</li>
                        <li>create users</li>
                        <li>edit users</li>
                        <li>delete users</li>
                        <li>assign roles</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <strong>Property Management:</strong>
                    <ul class="small">
                        <li>view properties</li>
                        <li>create properties</li>
                        <li>edit properties</li>
                        <li>approve properties</li>
                        <li>feature properties</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <strong>System Management:</strong>
                    <ul class="small">
                        <li>view settings</li>
                        <li>edit settings</li>
                        <li>clear cache</li>
                        <li>view reports</li>
                        <li>export data</li>
                    </ul>
                </div>
            </div>
        </div>

        @if($existingCategories->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Existing Categories</h3>
            </div>
            <div class="card-body">
                @foreach($existingCategories as $category)
                <span class="badge badge-primary mr-1 mb-1">{{ ucfirst($category) }}</span>
                @endforeach
            </div>
        </div>
        @endif
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
            updatePermissionName();
        }
    });

    $('#customCategory').on('input', function() {
        const category = $(this).val();
        if (category) {
            $('#categorySelect').val('');
            updatePermissionName();
        }
    });

    // Auto-suggest permission name based on category
    function updatePermissionName() {
        const category = $('#customCategory').val() || $('#categorySelect').val();
        const currentName = $('#name').val();
        
        if (category && !currentName) {
            const suggestions = {
                'users': 'view users',
                'properties': 'view properties',
                'dashboard': 'view dashboard',
                'settings': 'view settings',
                'reports': 'view reports',
                'media': 'view media'
            };
            
            const suggestion = suggestions[category.toLowerCase()];
            if (suggestion) {
                $('#name').attr('placeholder', `e.g., ${suggestion}`);
            }
        }
    }

    // Permission name suggestions based on common patterns
    const commonActions = ['view', 'create', 'edit', 'delete', 'manage', 'approve', 'publish', 'export'];
    const nameInput = $('#name');
    
    nameInput.on('input', function() {
        const value = $(this).val().toLowerCase();
        
        // Auto-complete common patterns
        if (value && !value.includes(' ')) {
            const category = $('#customCategory').val() || $('#categorySelect').val();
            if (category && commonActions.includes(value)) {
                $(this).attr('placeholder', `${value} ${category.toLowerCase()}`);
            }
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
        
        return true;
    });

    // Click on examples to auto-fill
    $('.card-body ul li').on('click', function() {
        const text = $(this).text().trim();
        $('#name').val(text);
        
        // Extract category from the text
        const words = text.split(' ');
        if (words.length > 1) {
            const possibleCategory = words[1];
            if ($('#categorySelect option[value="' + possibleCategory + '"]').length > 0) {
                $('#categorySelect').val(possibleCategory);
                $('#customCategory').val(possibleCategory);
            } else {
                $('#customCategory').val(possibleCategory);
                $('#categorySelect').val('');
            }
        }
    });
});
</script>
@endpush