@extends('admin.layouts.app')

@section('title', 'Feature Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.features.index') }}">Features</a></li>
    <li class="breadcrumb-item active">{{ $feature->name_en }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    @if($feature->icon)
                        <i class="{{ $feature->icon }}"></i>
                    @endif
                    {{ $feature->name_en }}
                    @if($feature->is_active)
                        <span class="badge badge-success ml-2">Active</span>
                    @else
                        <span class="badge badge-danger ml-2">Inactive</span>
                    @endif
                </h3>
                <div class="card-tools">
                    @can('edit features')
                    <a href="{{ route('admin.features.edit', $feature) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endcan
                    @can('delete features')
                    <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this feature?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" {{ $feature->properties()->count() > 0 ? 'disabled' : '' }}>
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
                                <td>{{ $feature->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Name (English):</strong></td>
                                <td>{{ $feature->name_en }}</td>
                            </tr>
                            <tr>
                                <td><strong>Name (Arabic):</strong></td>
                                <td dir="rtl">{{ $feature->name_ar }}</td>
                            </tr>
                            <tr>
                                <td><strong>Category:</strong></td>
                                <td>
                                    @if($feature->category)
                                        <span class="badge badge-info">{{ $feature->category }}</span>
                                    @else
                                        <span class="text-muted">No Category</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Sort Order:</strong></td>
                                <td>{{ $feature->sort_order ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($feature->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Additional Details</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Icon:</strong></td>
                                <td>
                                    @if($feature->icon)
                                        <i class="{{ $feature->icon }} fa-2x"></i>
                                        <br><small class="text-muted">{{ $feature->icon }}</small>
                                    @else
                                        <span class="text-muted">No Icon</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Properties Count:</strong></td>
                                <td>
                                    <span class="badge badge-secondary">{{ $feature->properties()->count() }}</span>
                                    @if($feature->properties()->count() > 0)
                                        <small class="text-muted d-block">This feature is used by properties</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $feature->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Updated:</strong></td>
                                <td>{{ $feature->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($feature->description_en || $feature->description_ar)
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Descriptions</h5>
                        @if($feature->description_en)
                        <div class="mb-3">
                            <h6>English Description:</h6>
                            <p class="text-muted">{{ $feature->description_en }}</p>
                        </div>
                        @endif
                        @if($feature->description_ar)
                        <div class="mb-3">
                            <h6>Arabic Description:</h6>
                            <p class="text-muted" dir="rtl">{{ $feature->description_ar }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($feature->properties()->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Properties Using This Feature ({{ $feature->properties()->count() }})</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feature->properties()->limit(10)->get() as $property)
                            <tr>
                                <td>{{ $property->id }}</td>
                                <td>{{ $property->title_en }}</td>
                                <td>
                                    @if($property->category)
                                        <span class="badge badge-info">{{ $property->category->name_en }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($property->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
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
                @if($feature->properties()->count() > 10)
                <div class="text-center">
                    <small class="text-muted">Showing first 10 properties. <a href="{{ route('admin.properties.index', ['feature' => $feature->id]) }}">View all properties with this feature</a></small>
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
                    @can('edit features')
                    <a href="{{ route('admin.features.edit', $feature) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Edit Feature
                    </a>
                    @endcan
                    
                    <a href="{{ route('admin.features.create') }}" class="btn btn-success btn-block">
                        <i class="fas fa-plus"></i> Add New Feature
                    </a>
                    
                    <a href="{{ route('admin.features.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-list"></i> All Features
                    </a>
                    
                    @can('delete features')
                    @if($feature->properties()->count() == 0)
                    <form method="POST" action="{{ route('admin.features.destroy', $feature) }}" onsubmit="return confirm('Are you sure you want to delete this feature?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Delete Feature
                        </button>
                    </form>
                    @else
                    <button class="btn btn-danger btn-block" disabled title="Cannot delete feature that is used by properties">
                        <i class="fas fa-trash"></i> Delete Feature
                    </button>
                    <small class="text-muted">Cannot delete: Feature is used by {{ $feature->properties()->count() }} properties</small>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-home"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Properties</span>
                        <span class="info-box-number">{{ $feature->properties()->count() }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active Properties</span>
                        <span class="info-box-number">{{ $feature->properties()->where('is_active', 1)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if($feature->category)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Related Features</h3>
            </div>
            <div class="card-body">
                @php
                    $relatedFeatures = \App\Models\Feature::where('category', $feature->category)
                                                          ->where('id', '!=', $feature->id)
                                                          ->where('is_active', 1)
                                                          ->limit(5)
                                                          ->get();
                @endphp
                @if($relatedFeatures->count() > 0)
                    <ul class="list-unstyled">
                        @foreach($relatedFeatures as $relatedFeature)
                        <li class="mb-2">
                            <a href="{{ route('admin.features.show', $relatedFeature) }}" class="text-decoration-none">
                                @if($relatedFeature->icon)
                                    <i class="{{ $relatedFeature->icon }}"></i>
                                @endif
                                {{ $relatedFeature->name_en }}
                            </a>
                            <small class="text-muted d-block">{{ $relatedFeature->properties()->count() }} properties</small>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No other features in this category.</p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection