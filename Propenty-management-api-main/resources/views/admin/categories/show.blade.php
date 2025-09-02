@extends('admin.layouts.app')

@section('title', 'Category Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
    <li class="breadcrumb-item active">{{ $category->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    {{ $category->name }}
                    @if($category->is_active)
                        <span class="badge badge-success ml-2">Active</span>
                    @else
                        <span class="badge badge-danger ml-2">Inactive</span>
                    @endif
                </h3>
                <div class="card-tools">
                    @can('edit categories')
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endcan
                    @can('delete categories')
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" {{ $category->properties()->count() > 0 ? 'disabled' : '' }}>
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Basic Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>{{ $category->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $category->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Slug:</strong></td>
                                <td><code>{{ $category->slug }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Parent Category:</strong></td>
                                <td>
                                    @if($category->parent)
                                        <a href="{{ route('admin.categories.show', $category->parent) }}" class="badge badge-info">
                                            {{ $category->parent->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Root Category</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Sort Order:</strong></td>
                                <td>{{ $category->sort_order ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Statistics</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Properties Count:</strong></td>
                                <td>
                                    <span class="badge badge-secondary">{{ $category->properties()->count() }}</span>
                                    @if($category->properties()->count() > 0)
                                        <small class="text-muted d-block">This category is used by properties</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Child Categories:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ $category->children()->count() }}</span>
                                    @if($category->children()->count() > 0)
                                        <small class="text-muted d-block">Has subcategories</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $category->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Updated:</strong></td>
                                <td>{{ $category->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($category->description)
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Description</h5>
                        <p class="text-muted">{{ $category->description }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($category->children()->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Child Categories ({{ $category->children()->count() }})</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Properties</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->children as $child)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.categories.show', $child) }}">
                                        {{ $child->name }}
                                    </a>
                                </td>
                                <td>
                                    @if($child->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $child->properties()->count() }}</span>
                                </td>
                                <td>{{ $child->created_at->format('M j, Y') }}</td>
                                <td>
                                    @can('view categories')
                                    <a href="{{ route('admin.categories.show', $child) }}" class="btn btn-info btn-xs">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($category->properties()->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Properties Using This Category ({{ $category->properties()->count() }})</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($category->properties()->with('city')->take(10)->get() as $property)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.properties.show', $property) }}">
                                        {{ Str::limit($property->title, 30) }}
                                    </a>
                                </td>
                                <td>
                                    @if($property->price)
                                        ${{ number_format($property->price, 2) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($property->city)
                                        {{ $property->city->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($property->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @elseif($property->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($property->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $property->created_at->format('M j, Y') }}</td>
                                <td>
                                    @can('view properties')
                                    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-info btn-xs">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($category->properties()->count() > 10)
                <div class="text-center">
                    <small class="text-muted">Showing first 10 properties. <a href="{{ route('admin.properties.index', ['category' => $category->id]) }}">View all properties in this category</a></small>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('edit categories')
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Edit Category
                    </a>
                    @endcan
                    
                    @can('create categories')
                    <a href="{{ route('admin.categories.create') }}?parent={{ $category->id }}" class="btn btn-success btn-block">
                        <i class="fas fa-plus"></i> Add Child Category
                    </a>
                    @endcan
                    
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-list"></i> All Categories
                    </a>
                    
                    @if($category->properties()->count() > 0)
                    <a href="{{ route('admin.properties.index', ['category' => $category->id]) }}" class="btn btn-info btn-block">
                        <i class="fas fa-home"></i> View Properties ({{ $category->properties()->count() }})
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @if($category->parent || $category->children()->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Category Hierarchy</h3>
            </div>
            <div class="card-body">
                @if($category->parent)
                <div class="mb-3">
                    <h6>Parent Category:</h6>
                    <a href="{{ route('admin.categories.show', $category->parent) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-arrow-up"></i> {{ $category->parent->name }}
                    </a>
                </div>
                @endif
                
                @if($category->children()->count() > 0)
                <div>
                    <h6>Child Categories ({{ $category->children()->count() }}):</h6>
                    @foreach($category->children as $child)
                    <a href="{{ route('admin.categories.show', $child) }}" class="btn btn-outline-secondary btn-sm mb-1">
                        <i class="fas fa-arrow-down"></i> {{ $child->name }}
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection