@extends('admin.layouts.app')

@section('title', 'Property Type Details')

@section('content-header', 'Property Type Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.property-types.index') }}">Property Types</a></li>
    <li class="breadcrumb-item active">{{ $propertyType->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $propertyType->name }}</h3>
                    <div class="card-tools">
                        @can('edit property types')
                        <a href="{{ route('admin.property-types.edit', $propertyType) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Name (English)</th>
                                    <td>{{ $propertyType->name }}</td>
                                </tr>
                                @if($propertyType->name_ar)
                                <tr>
                                    <th>Name (Arabic)</th>
                                    <td>{{ $propertyType->name_ar }}</td>
                                </tr>
                                @endif
                                @if($propertyType->name_ku)
                                <tr>
                                    <th>Name (Kurdish)</th>
                                    <td>{{ $propertyType->name_ku }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Slug</th>
                                    <td><code>{{ $propertyType->slug }}</code></td>
                                </tr>
                                @if($propertyType->description)
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $propertyType->description }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Icon</th>
                                    <td>
                                        {!! $propertyType->icon_html !!}
                                        @if($propertyType->icon)
                                            <code class="ml-2">{{ $propertyType->icon }}</code>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $propertyType->is_active ? 'success' : 'secondary' }}">
                                            {{ $propertyType->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order</th>
                                    <td>{{ $propertyType->sort_order }}</td>
                                </tr>
                                @if($propertyType->parent)
                                <tr>
                                    <th>Parent Type</th>
                                    <td>
                                        <a href="{{ route('admin.property-types.show', $propertyType->parent) }}">
                                            {{ $propertyType->parent->name }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Created</th>
                                    <td>{{ $propertyType->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated</th>
                                    <td>{{ $propertyType->updated_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Child Types -->
            @if($propertyType->children->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Child Types ({{ $propertyType->children->count() }})</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($propertyType->children as $child)
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        {!! $child->icon_html !!}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('admin.property-types.show', $child) }}">
                                                {{ $child->name }}
                                            </a>
                                        </h6>
                                        @if($child->description)
                                            <small class="text-muted">{{ Str::limit($child->description, 60) }}</small>
                                        @endif
                                        <br>
                                        <span class="badge badge-{{ $child->is_active ? 'success' : 'secondary' }} badge-sm">
                                            {{ $child->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <small class="text-muted">{{ $child->properties()->count() }} properties</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Properties using this type -->
            @if($propertyType->properties->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Properties using this type ({{ $propertyType->properties->count() }})</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Location</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($propertyType->properties->take(10) as $property)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.properties.show', $property) }}">
                                            {{ Str::limit($property->title, 40) }}
                                        </a>
                                    </td>
                                    <td>{{ $property->city }}</td>
                                    <td>{{ $property->formatted_price }}</td>
                                    <td>
                                        <span class="badge badge-{{ $property->status === 'published' ? 'success' : 'warning' }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $property->created_at->format('M j, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($propertyType->properties->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.properties.index', ['filter[property_type]' => $propertyType->slug]) }}" class="btn btn-outline-primary">
                                View all {{ $propertyType->properties->count() }} properties
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Stats Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h4 class="text-primary">{{ $propertyType->properties->count() }}</h4>
                                <small class="text-muted">Properties</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-info">{{ $propertyType->children->count() }}</h4>
                            <small class="text-muted">Child Types</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    @can('edit property types')
                    <a href="{{ route('admin.property-types.edit', $propertyType) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Property Type
                    </a>
                    @endcan

                    @if($propertyType->children->count() === 0)
                    @can('create property types')
                    <a href="{{ route('admin.property-types.create') }}?parent_id={{ $propertyType->id }}" class="btn btn-success btn-block">
                        <i class="fas fa-plus"></i> Add Child Type
                    </a>
                    @endcan
                    @endif

                    <a href="{{ route('admin.properties.index', ['filter[property_type]' => $propertyType->slug]) }}" class="btn btn-info btn-block">
                        <i class="fas fa-list"></i> View Properties
                    </a>

                    @can('delete property types')
                    @if($propertyType->properties->count() === 0 && $propertyType->children->count() === 0)
                    <form method="POST" action="{{ route('admin.property-types.destroy', $propertyType) }}"
                          onsubmit="return confirm('Are you sure you want to delete this property type?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Property Type
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>

            <!-- Hierarchy Card -->
            @if($propertyType->parent || $propertyType->children->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hierarchy</h3>
                </div>
                <div class="card-body">
                    @if($propertyType->parent)
                        <div class="mb-2">
                            <small class="text-muted">Parent:</small><br>
                            <a href="{{ route('admin.property-types.show', $propertyType->parent) }}">
                                {!! $propertyType->parent->icon_html !!} {{ $propertyType->parent->name }}
                            </a>
                        </div>
                        <div class="text-center mb-2">
                            <i class="fas fa-arrow-down text-muted"></i>
                        </div>
                    @endif

                    <div class="mb-2">
                        <strong>{!! $propertyType->icon_html !!} {{ $propertyType->name }}</strong>
                    </div>

                    @if($propertyType->children->count() > 0)
                        <div class="text-center mb-2">
                            <i class="fas fa-arrow-down text-muted"></i>
                        </div>
                        <small class="text-muted">Children:</small>
                        @foreach($propertyType->children as $child)
                            <div class="ml-3">
                                <a href="{{ route('admin.property-types.show', $child) }}">
                                    {!! $child->icon_html !!} {{ $child->name }}
                                </a>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection