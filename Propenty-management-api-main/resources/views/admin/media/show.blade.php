@extends('admin.layouts.app')

@section('title', 'Media Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.media.index') }}">Media Library</a></li>
    <li class="breadcrumb-item active">{{ $media->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Media Preview -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Media Preview</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.media.download', $media) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-download"></i> Download
                    </a>
                    @can('edit media')
                    <form action="{{ route('admin.media.regenerate', $media) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="fas fa-sync"></i> Regenerate Conversions
                        </button>
                    </form>
                    @endcan
                    @can('delete media')
                    <form action="{{ route('admin.media.destroy', $media) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm delete-media">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @if(str_starts_with($media->mime_type, 'image/'))
                    <img src="{{ $media->getUrl() }}" class="img-fluid rounded" alt="{{ $media->name }}">
                @elseif(str_starts_with($media->mime_type, 'video/'))
                    <video controls class="w-100">
                        <source src="{{ $media->getUrl() }}" type="{{ $media->mime_type }}">
                        Your browser does not support the video tag.
                    </video>
                @elseif($media->mime_type == 'application/pdf')
                    <embed src="{{ $media->getUrl() }}" type="application/pdf" width="100%" height="600px">
                @else
                    <div class="text-center p-5 bg-light rounded">
                        <i class="fas fa-file fa-5x text-secondary mb-3"></i>
                        <p>Preview not available for this file type</p>
                        <a href="{{ $media->getUrl() }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Conversions -->
        @if(count($conversions) > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Generated Conversions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($conversions as $name => $url)
                    <div class="col-md-4">
                        <div class="border rounded p-2 mb-3">
                            <h6>{{ ucfirst($name) }}</h6>
                            @if(str_starts_with($media->mime_type, 'image/'))
                                <img src="{{ $url }}" class="img-fluid rounded" alt="{{ $name }}">
                            @else
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-info btn-block">
                                    <i class="fas fa-external-link-alt"></i> View {{ ucfirst($name) }}
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- File Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">File Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>File Name:</th>
                        <td>{{ $media->file_name }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $media->name }}</td>
                    </tr>
                    <tr>
                        <th>MIME Type:</th>
                        <td>{{ $media->mime_type }}</td>
                    </tr>
                    <tr>
                        <th>Size:</th>
                        <td>{{ formatBytes($media->size) }}</td>
                    </tr>
                    <tr>
                        <th>Collection:</th>
                        <td>{{ $media->collection_name }}</td>
                    </tr>
                    <tr>
                        <th>Disk:</th>
                        <td>{{ $media->disk }}</td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $media->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated:</th>
                        <td>{{ $media->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Related Model -->
        @if($model)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Related {{ class_basename($media->model_type) }}</h3>
            </div>
            <div class="card-body">
                @if($media->model_type == 'App\Models\Property')
                    <h5>{{ $model->title }}</h5>
                    <p class="text-muted">{{ Str::limit($model->description, 100) }}</p>
                    <a href="{{ route('admin.properties.show', $model) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> View Property
                    </a>
                @elseif($media->model_type == 'App\Models\User')
                    <h5>{{ $model->full_name }}</h5>
                    <p class="text-muted">{{ $model->email }}</p>
                    <a href="{{ route('admin.users.show', $model) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> View User
                    </a>
                @else
                    <p>Model ID: {{ $media->model_id }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Custom Properties -->
        @if(count($customProperties) > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Custom Properties</h3>
            </div>
            <div class="card-body">
                <pre>{{ json_encode($customProperties, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif

        <!-- URLs -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">URLs</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Original URL:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $media->getUrl() }}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary copy-url" type="button" data-url="{{ $media->getUrl() }}">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                @foreach($conversions as $name => $url)
                <div class="form-group">
                    <label>{{ ucfirst($name) }} URL:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $url }}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary copy-url" type="button" data-url="{{ $url }}">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Copy URL to clipboard
    $('.copy-url').on('click', function() {
        let url = $(this).data('url');
        let $temp = $('<input>');
        $('body').append($temp);
        $temp.val(url).select();
        document.execCommand('copy');
        $temp.remove();
        
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'URL copied to clipboard',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Delete confirmation
    $('.delete-media').on('click', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush

@php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
@endphp