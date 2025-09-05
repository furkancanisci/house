@extends('admin.layouts.app')

@section('title', 'User Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users & Agents</a></li>
    <li class="breadcrumb-item active">{{ $user->full_name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <!-- User Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $user->full_name }}</h3>
                <div class="card-tools">
                    @can('edit users')
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    @endcan
                    @can('delete users')
                    @if($user->id !== auth()->id())
                    <button type="button" class="btn btn-danger btn-sm delete-user-btn" 
                            data-user-id="{{ $user->id }}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <!-- User Avatar -->
                @if($user->avatar_url)
                <div class="row mb-4">
                    <div class="col-md-12 text-center">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" 
                             class="img-circle elevation-2" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                </div>
                @endif

                <!-- Basic Information -->
                <div class="row">
                    <div class="col-md-6">
                        <h5>Basic Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>User ID:</strong></td>
                                <td>#{{ $user->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Full Name:</strong></td>
                                <td>{{ $user->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>
                                    {{ $user->email }}
                                    @if($user->email_verified_at)
                                        <span class="badge badge-success ml-2">Verified</span>
                                    @else
                                        <span class="badge badge-warning ml-2">Unverified</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $user->phone ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>
                                    @if($user->roles->isNotEmpty())
                                        @foreach($user->roles as $role)
                                        <span class="badge badge-{{ $role->name == 'SuperAdmin' ? 'danger' : ($role->name == 'Admin' ? 'warning' : ($role->name == 'Agent' ? 'info' : 'secondary')) }}">
                                            <i class="fas fa-{{ $role->name == 'SuperAdmin' ? 'crown' : ($role->name == 'Admin' ? 'user-shield' : ($role->name == 'Agent' ? 'user-tie' : 'user')) }}"></i>
                                            {{ $role->name }}
                                        </span>
                                        @endforeach
                                    @else
                                        <span class="badge badge-secondary">No Role Assigned</span>
                                    @endif
                                </td>
                            </tr>
                            @can('manage permissions')
                            <tr>
                                <td><strong>Direct Permissions:</strong></td>
                                <td>
                                    @if($user->getDirectPermissions()->count() > 0)
                                        @foreach($user->getDirectPermissions() as $permission)
                                            <span class="badge badge-warning mr-1 mb-1">
                                                <i class="fas fa-key"></i>
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                        <br>
                                        <small class="text-muted">
                                            These permissions are directly assigned ({{ $user->getDirectPermissions()->count() }} total)
                                        </small>
                                    @else
                                        <span class="text-muted">None assigned directly</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Permissions:</strong></td>
                                <td>
                                    @if($user->getAllPermissions()->count() > 0)
                                        <span class="badge badge-success">
                                            {{ $user->getAllPermissions()->count() }} permissions
                                        </span>
                                        <small class="text-muted">
                                            ({{ $user->getPermissionsViaRoles()->count() }} from roles + {{ $user->getDirectPermissions()->count() }} direct)
                                        </small>
                                    @else
                                        <span class="badge badge-secondary">No permissions</span>
                                    @endif
                                </td>
                            </tr>
                            @endcan
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Account Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Joined:</strong></td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email Verified:</strong></td>
                                <td>
                                    @if($user->email_verified_at)
                                        {{ $user->email_verified_at->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">Not verified</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Properties Count:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ $user->properties->count() }}</span>
                                    @if($user->properties->count() > 0)
                                    <a href="#properties-section" class="btn btn-sm btn-outline-info ml-2">View Properties</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Leads Count:</strong></td>
                                <td>
                                    <span class="badge badge-warning">{{ $user->leads->count() }}</span>
                                    @if($user->leads->count() > 0)
                                    <a href="#leads-section" class="btn btn-sm btn-outline-warning ml-2">View Leads</a>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Bio/Description -->
                @if($user->bio)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Bio</h5>
                        <div class="bg-light p-3 rounded">
                            {{ $user->bio }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Properties -->
        @if($user->properties->count() > 0)
        <div class="card" id="properties-section">
            <div class="card-header">
                <h3 class="card-title">Recent Properties ({{ $user->properties->count() }} total)</h3>
                <div class="card-tools">
                    @can('view properties')
                    <a href="{{ route('admin.users.properties', $user) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-building"></i> View All Properties
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->properties as $property)
                            <tr>
                                <td>
                                    <strong>{{ $property->title }}</strong>
                                    @if($property->is_featured)
                                        <span class="badge badge-warning ml-1">Featured</span>
                                    @endif
                                </td>
                                <td>{{ ucfirst($property->property_type) }}</td>
                                <td>
                                    <strong class="text-success">${{ number_format($property->price) }}</strong>
                                    @if($property->listing_type === 'rent' && $property->price_type)
                                        <small>/{{ $property->price_type }}</small>
                                    @endif
                                </td>
                                <td>{{ $property->city }}</td>
                                <td>
                                    <span class="badge badge-{{ $property->status === 'active' ? 'success' : ($property->status === 'pending' ? 'warning' : ($property->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                        {{ ucfirst($property->status) }}
                                    </span>
                                </td>
                                <td>{{ $property->created_at->format('M d, Y') }}</td>
                                <td>
                                    @can('view properties')
                                    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-info btn-xs">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit properties')
                                    <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($user->properties->count() >= 10)
                <div class="text-center mt-3">
                    <small class="text-muted">Showing recent 10 properties. <a href="{{ route('admin.users.properties', $user) }}">View all properties</a></small>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Recent Leads -->
        @if($user->leads->count() > 0)
        <div class="card" id="leads-section">
            <div class="card-header">
                <h3 class="card-title">Recent Leads ({{ $user->leads->count() }} total)</h3>
                <div class="card-tools">
                    @can('view leads')
                    <a href="{{ route('admin.users.leads', $user) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-user-plus"></i> View All Leads
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Lead ID</th>
                                <th>Property</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->leads as $lead)
                            <tr>
                                <td>#{{ $lead->id }}</td>
                                <td>
                                    @if($lead->property)
                                        <a href="{{ route('admin.properties.show', $lead->property) }}">
                                            {{ $lead->property->title }}
                                        </a>
                                    @else
                                        <span class="text-muted">Property not available</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $lead->name }}</strong><br>
                                    <small class="text-muted">{{ $lead->email }}</small>
                                    @if($lead->phone)
                                        <br><small class="text-muted">{{ $lead->phone }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $lead->status === 'new' ? 'info' : ($lead->status === 'contacted' ? 'warning' : ($lead->status === 'qualified' ? 'primary' : ($lead->status === 'converted' ? 'success' : 'secondary'))) }}">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst($lead->source ?? 'Unknown') }}</td>
                                <td>{{ $lead->created_at->format('M d, Y') }}</td>
                                <td>
                                    @can('view leads')
                                    <a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-info btn-xs">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit leads')
                                    <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($user->leads->count() >= 10)
                <div class="text-center mt-3">
                    <small class="text-muted">Showing recent 10 leads. <a href="{{ route('admin.users.leads', $user) }}">View all leads</a></small>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                @can('edit users')
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit User
                </a>
                @endcan
                
                @can('assign roles')
                <button type="button" class="btn btn-info btn-block" onclick="showRoleModal()">
                    <i class="fas fa-user-tag"></i> Change Role
                </button>
                @endcan
                
                @can('edit users')
                <button type="button" class="btn btn-{{ $user->is_active ? 'warning' : 'success' }} btn-block toggle-status-btn" 
                        data-user-id="{{ $user->id }}" 
                        data-is-active="{{ $user->is_active ? 'true' : 'false' }}"
                        data-toggle-url="{{ route('admin.users.toggle-status', $user) }}">
                    <i class="fas fa-{{ $user->is_active ? 'user-slash' : 'user-check' }}"></i> 
                    {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                </button>
                @endcan
                
                @can('impersonate users')
                @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" style="display: inline-block; width: 100%;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-block" onclick="return confirm('Are you sure you want to impersonate this user?')">
                        <i class="fas fa-user-secret"></i> Impersonate User
                    </button>
                </form>
                @endif
                @endcan
                
                @can('delete users')
                @if($user->id !== auth()->id())
                <button type="button" class="btn btn-danger btn-block delete-user-btn" 
                        data-user-id="{{ $user->id }}"
                        data-delete-url="{{ route('admin.users.destroy', $user) }}">
                    <i class="fas fa-trash"></i> Delete User
                </button>
                @endif
                @endcan
            </div>
        </div>

        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-building"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Properties</span>
                        <span class="info-box-number">{{ $user->properties->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-warning">
                        <i class="fas fa-user-plus"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Leads</span>
                        <span class="info-box-number">{{ $user->leads->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-eye"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Property Views</span>
                        <span class="info-box-number">{{ $user->propertyViews->count() ?? 0 }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-danger">
                        <i class="fas fa-heart"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Favorites</span>
                        <span class="info-box-number">{{ $user->favoriteProperties->count() ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Activity</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-info">Account</span>
                    </div>
                    <div>
                        <i class="fas fa-user bg-green"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Account created</h3>
                            <div class="timeline-body">
                                User registered on {{ $user->created_at->format('M d, Y \a\t H:i') }}
                            </div>
                        </div>
                    </div>
                    
                    @if($user->email_verified_at)
                    <div>
                        <i class="fas fa-envelope bg-blue"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Email verified</h3>
                            <div class="timeline-body">
                                Email verified on {{ $user->email_verified_at->format('M d, Y \a\t H:i') }}
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($user->properties->count() > 0)
                    <div class="time-label">
                        <span class="bg-info">Properties</span>
                    </div>
                    <div>
                        <i class="fas fa-building bg-yellow"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Latest property</h3>
                            <div class="timeline-body">
                                {{ $user->properties->first()->title }} - {{ $user->properties->first()->created_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role Assignment Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change User Role</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="roleForm" data-assign-role-url="{{ route('admin.users.assign-role', $user) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modalRole">Select New Role</label>
                        <select name="role" id="modalRole" class="form-control" required>
                            @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                <option value="{{ $role->name }}" {{ $user->roles->first()?->name == $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Role changes take effect immediately and will affect the user's permissions.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Delete user button event handler
    $('.delete-user-btn').on('click', function() {
        const userId = $(this).data('user-id');
        const deleteUrl = $(this).data('delete-url');
        deleteUser(deleteUrl);
    });
    
    // Toggle status button event handler
    $('.toggle-status-btn').on('click', function() {
        const isActive = $(this).data('is-active') === 'true';
        const toggleUrl = $(this).data('toggle-url');
        toggleUserStatus(isActive, toggleUrl);
    });
    
    // Role form submission
    $('#roleForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const assignRoleUrl = $(this).data('assign-role-url');
        
        $.post(assignRoleUrl, formData)
        .done(function(response) {
            $('#roleModal').modal('hide');
            Swal.fire('Success', response.success, 'success').then(() => {
                location.reload();
            });
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
            Swal.fire('Error', error, 'error');
        });
    });
});

function showRoleModal() {
    $('#roleModal').modal('show');
}

function toggleUserStatus(isActive, toggleUrl) {
    const action = isActive ? 'deactivate' : 'activate';
    
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${action} this user?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: `Yes, ${action}!`
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(toggleUrl, {
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .done(function(response) {
                Swal.fire('Success', response.success, 'success').then(() => {
                    location.reload();
                });
            })
            .fail(function(xhr) {
                const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                Swal.fire('Error', error, 'error');
            });
        }
    });
}

function deleteUser(deleteUrl) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete user!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteUrl;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = $('meta[name="csrf-token"]').attr('content');
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush