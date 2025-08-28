<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                @can('edit amenities')
                <th width="50">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="selectAll">
                        <label class="custom-control-label" for="selectAll"></label>
                    </div>
                </th>
                @endcan
                <th>ID</th>
                <th>Icon</th>
                <th>Name</th>
                <th>English Name</th>
                <th>Arabic Name</th>
                <th>Slug</th>
                <th>Properties</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($amenities as $amenity)
            <tr>
                @can('edit amenities')
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input amenity-checkbox" 
                               id="amenity{{ $amenity->id }}" value="{{ $amenity->id }}">
                        <label class="custom-control-label" for="amenity{{ $amenity->id }}"></label>
                    </div>
                </td>
                @endcan
                <td>{{ $amenity->id }}</td>
                <td>
                    @if($amenity->icon)
                        <i class="{{ $amenity->icon }} fa-lg text-primary"></i>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $amenity->name }}</td>
                <td>{{ $amenity->name_en }}</td>
                <td dir="rtl">{{ $amenity->name_ar }}</td>
                <td>
                    <code>{{ $amenity->slug }}</code>
                </td>
                <td>
                    <span class="badge badge-info">{{ $amenity->properties_count }}</span>
                </td>
                <td>
                    @if($amenity->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </td>
                <td>{{ $amenity->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.amenities.show', $amenity) }}" 
                           class="btn btn-info btn-sm" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('edit amenities')
                        <a href="{{ route('admin.amenities.edit', $amenity) }}" 
                           class="btn btn-warning btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                        @can('delete amenities')
                        @if($amenity->properties_count == 0)
                        <form action="{{ route('admin.amenities.destroy', $amenity) }}" 
                              method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm delete-amenity" title="Delete">
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
                <td colspan="{{ auth()->user()->can('edit amenities') ? '11' : '10' }}" class="text-center">
                    No amenities found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($amenities->hasPages())
<div class="d-flex justify-content-center">
    {{ $amenities->appends(request()->query())->links() }}
</div>
@endif