@extends('admin.layouts.app')

@section('title', 'Property Types')

@section('content-header', 'Property Types')

@section('breadcrumb')
    <li class="breadcrumb-item active">Property Types</li>
@endsection

@section('content')
    <!-- Main content -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Property Types List</h3>
                    <div class="card-tools">
                        @can('create property types')
                        <a href="{{ route('admin.property-types.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Property Type
                        </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="60">Icon</th>
                                    <th>Name</th>
                                    <th>Parent</th>
                                    <th width="100">Properties</th>
                                    <th width="100">Status</th>
                                    <th width="100">Sort Order</th>
                                    <th width="120">Created</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($propertyTypes as $propertyType)
                                <tr>
                                    <td class="text-center">
                                        {!! $propertyType->icon_html !!}
                                    </td>
                                    <td>
                                        <strong>{{ $propertyType->getPreferredName() }}</strong>
                                        @if($propertyType->name_ar && $propertyType->name_ar !== $propertyType->name)
                                            <br>
                                            <small class="text-muted">English: {{ $propertyType->name }}</small>
                                        @endif
                                        @if($propertyType->description)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($propertyType->description, 50) }}</small>
                                        @endif
                                        <br>
                                        <small class="text-info">{{ $propertyType->slug }}</small>
                                    </td>
                                    <td>
                                        @if($propertyType->parent)
                                            <span class="badge badge-secondary">{{ $propertyType->parent->getPreferredName() }}</span>
                                        @else
                                            <span class="text-muted">Root Category</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($propertyType->properties_count > 0)
                                            <span class="badge badge-info">{{ $propertyType->properties_count }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $propertyType->is_active ? 'success' : 'secondary' }}">
                                            {{ $propertyType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{ $propertyType->sort_order }}
                                    </td>
                                    <td>
                                        {{ $propertyType->created_at->format('M j, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $propertyType->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            @can('view property types')
                                            <a href="{{ route('admin.property-types.show', $propertyType) }}"
                                               class="btn btn-info btn-xs" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('edit property types')
                                            <a href="{{ route('admin.property-types.edit', $propertyType) }}"
                                               class="btn btn-primary btn-xs" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete property types')
                                            @if($propertyType->properties()->count() == 0 && $propertyType->children()->count() == 0)
                                            <form method="POST" action="{{ route('admin.property-types.destroy', $propertyType) }}"
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this property type?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs" title="Delete">
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
                                    <td colspan="8" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No property types found</h5>
                                            <p class="text-muted">Create your first property type to get started.</p>
                                            @can('create property types')
                                            <a href="{{ route('admin.property-types.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create First Property Type
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $propertyTypes->firstItem() ?? 0 }} to {{ $propertyTypes->lastItem() ?? 0 }}
                            of {{ $propertyTypes->total() }} results
                        </div>
                        <div>
                            {{ $propertyTypes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection