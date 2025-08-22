<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>English Name</th>
                <th>Arabic Name</th>
                <th>Slug</th>
                <th>Neighborhoods</th>
                <th>Properties</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cities as $city)
            <tr>
                <td>{{ $city->id }}</td>
                <td>{{ $city->name }}</td>
                <td>{{ $city->name_en }}</td>
                <td>{{ $city->name_ar }}</td>
                <td>
                    <code>{{ $city->slug }}</code>
                </td>
                <td>
                    <span class="badge badge-info">{{ $city->neighborhoods_count }}</span>
                </td>
                <td>
                    <span class="badge badge-success">{{ $city->properties_count }}</span>
                </td>
                <td>
                    @if($city->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </td>
                <td>{{ $city->created_at->format('M d, Y') }}</td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.cities.show', $city) }}" 
                           class="btn btn-info btn-sm" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('manage neighborhoods')
                        <a href="{{ route('admin.cities.neighborhoods', $city) }}" 
                           class="btn btn-secondary btn-sm" title="Neighborhoods">
                            <i class="fas fa-map-marker-alt"></i>
                        </a>
                        @endcan
                        @can('manage cities')
                        <a href="{{ route('admin.cities.edit', $city) }}" 
                           class="btn btn-warning btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($city->properties_count == 0)
                        <form action="{{ route('admin.cities.destroy', $city) }}" 
                              method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm delete-city" title="Delete">
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
                <td colspan="10" class="text-center">No cities found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($cities->hasPages())
<div class="d-flex justify-content-center">
    {{ $cities->appends(request()->query())->links() }}
</div>
@endif