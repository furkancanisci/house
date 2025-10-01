@extends('admin.layouts.app')

@section('title', 'Property Document Type Details')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Property Document Type Details</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.property-document-types.index') }}">Property Document Types</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Document Type Information</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.property-document-types.edit', $propertyDocumentType) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Name (Arabic):</th>
                                        <td>{{ $propertyDocumentType->name_ar }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name (English):</th>
                                        <td>{{ $propertyDocumentType->name_en }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name (Kurdish):</th>
                                        <td>{{ $propertyDocumentType->name_ku }}</td>
                                    </tr>
                                    <tr>
                                        <th>Sort Order:</th>
                                        <td>{{ $propertyDocumentType->sort_order }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge {{ $propertyDocumentType->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $propertyDocumentType->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Description (Arabic):</th>
                                        <td>{{ $propertyDocumentType->description_ar ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description (English):</th>
                                        <td>{{ $propertyDocumentType->description_en ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description (Kurdish):</th>
                                        <td>{{ $propertyDocumentType->description_ku ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At:</th>
                                        <td>{{ $propertyDocumentType->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At:</th>
                                        <td>{{ $propertyDocumentType->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Associated Properties</h3>
                    </div>
                    
                    <div class="card-body">
                        @if($propertyDocumentType->properties->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>City</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($propertyDocumentType->properties as $property)
                                            <tr>
                                                <td>{{ $property->id }}</td>
                                                <td>{{ $property->title }}</td>
                                                <td>{{ $property->city->name ?? 'N/A' }}</td>
                                                <td>{{ number_format($property->price, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ $property->is_active ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $property->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-primary btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>No properties associated with this document type.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection