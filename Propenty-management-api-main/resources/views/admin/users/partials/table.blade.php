<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                @can('assign roles')
                <th width="30px">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="selectAllCheckbox">
                        <label class="custom-control-label" for="selectAllCheckbox"></label>
                    </div>
                </th>
                @endcan
                <th>Avatar</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Properties</th>
                <th>Leads</th>
                <th>Status</th>
                <th>Verified</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                @can('assign roles')
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input user-checkbox" id="user-{{ $user->id }}" 
                               value="{{ $user->id }}" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <label class="custom-control-label" for="user-{{ $user->id }}"></label>
                    </div>
                </td>
                @endcan
                <td>
                    <div class="user-block">
                        <img class="img-circle" 
                             src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name) }}" 
                             alt="User Avatar" width="40" height="40">
                    </div>
                </td>
                <td>
                    <div>
                        <strong>{{ $user->full_name }}</strong>
                        <br><small class="text-muted">#{{ $user->id }}</small>
                    </div>
                </td>
                <td>
                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                </td>
                <td>
                    @if($user->phone)
                        <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($user->roles->count() > 0)
                        @foreach($user->roles as $role)
                            <span class="badge badge-{{ $role->name == 'SuperAdmin' ? 'danger' : ($role->name == 'Admin' ? 'warning' : ($role->name == 'Agent' ? 'info' : 'secondary')) }}">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    @else
                        <span class="badge badge-light">No Role</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-success">{{ $user->properties_count }}</span>
                </td>
                <td>
                    <span class="badge badge-info">{{ $user->leads_count }}</span>
                </td>
                <td>
                    @if($user->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </td>
                <td>
                    @if($user->email_verified_at)
                        <span class="badge badge-success">
                            <i class="fas fa-check"></i> Verified
                        </span>
                    @else
                        <span class="badge badge-warning">
                            <i class="fas fa-times"></i> Unverified
                        </span>
                    @endif
                </td>
                <td>{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.users.show', $user) }}" 
                           class="btn btn-info btn-sm" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        
                        @can('edit users')
                        <a href="{{ route('admin.users.edit', $user) }}" 
                           class="btn btn-warning btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        @if($user->id !== auth()->id())
                        <button type="button" class="btn btn-secondary btn-sm toggle-status" 
                                data-user="{{ $user->id }}" data-status="{{ $user->is_active }}"
                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                            <i class="fas fa-{{ $user->is_active ? 'user-slash' : 'user-check' }}"></i>
                        </button>
                        @endif
                        @endcan

                        @can('assign roles')
                        <button type="button" class="btn btn-purple btn-sm assign-role" 
                                data-user="{{ $user->id }}" 
                                data-role="{{ $user->roles->first()->name ?? '' }}"
                                title="Assign Role">
                            <i class="fas fa-user-tag"></i>
                        </button>
                        @endcan

                        @can('impersonate users')
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-dark btn-sm impersonate-user" 
                                    data-user-name="{{ $user->full_name }}" title="Impersonate">
                                <i class="fas fa-user-secret"></i>
                            </button>
                        </form>
                        @endif
                        @endcan

                        @can('delete users')
                        @if($user->id !== auth()->id() && $user->properties_count == 0)
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm delete-user" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ can('assign roles') ? '12' : '11' }}" class="text-center">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
<div class="d-flex justify-content-center">
    {{ $users->appends(request()->query())->links() }}
</div>
@endif

<style>
.btn-purple {
    color: #fff;
    background-color: #6f42c1;
    border-color: #6f42c1;
}
.btn-purple:hover {
    color: #fff;
    background-color: #5a32a3;
    border-color: #5a32a3;
}
</style>