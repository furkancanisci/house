@extends('admin.layouts.app')

@section('title', 'Property Categories')

@section('content-header', 'Property Categories')

@section('breadcrumb')
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-6 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Categories</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['with_properties'] }}</h3>
                    <p>With Properties</p>
                </div>
                <div class="icon">
                    <i class="fas fa-home"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Categories List</h3>
                    <div class="card-tools">
                        @can('create categories')
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Category
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
                                @forelse($categories as $category)
                                <tr>
                                    <td class="text-center">
                                        {!! $category->icon_html !!}
                                    </td>
                                    <td>
                                        <strong>{{ $category->getPreferredName() }}</strong>
                                        @if($category->name_ar && $category->name_ar !== $category->name)
                                            <br>
                                            <small class="text-muted">English: {{ $category->name }}</small>
                                        @endif
                                        @if($category->description)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                        @endif
                                        <br>
                                        <small class="text-info">{{ $category->slug }}</small>
                                    </td>
                                    <td>
                                        @if($category->parent)
                                            <span class="badge badge-secondary">{{ $category->parent->getPreferredName() }}</span>
                                        @else
                                            <span class="text-muted">Root Category</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($category->properties_count > 0)
                                            <span class="badge badge-info">{{ $category->properties_count }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $category->is_active ? 'success' : 'secondary' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{ $category->sort_order }}
                                    </td>
                                    <td>
                                        {{ $category->created_at->format('M j, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $category->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            @can('view categories')
                                            <a href="{{ route('admin.categories.show', $category) }}" 
                                               class="btn btn-info btn-xs" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('edit categories')
                                            <a href="{{ route('admin.categories.edit', $category) }}" 
                                               class="btn btn-primary btn-xs" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete categories')
                                            @if($category->properties_count == 0 && $category->children->count() == 0)
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this category?')">
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
                                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No categories found</h5>
                                            <p class="text-muted">Create your first property category to get started.</p>
                                            @can('create categories')
                                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create First Category
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
                            Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} 
                            of {{ $categories->total() }} results
                        </div>
                        <div>
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection