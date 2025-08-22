@extends('admin.layouts.app')

@section('title', 'Media Library')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Media Library</li>
</ol>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['total_files']) }}</h3>
                <p>Total Files</p>
            </div>
            <div class="icon">
                <i class="fas fa-file"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ formatBytes($stats['total_size']) }}</h3>
                <p>Total Size</p>
            </div>
            <div class="icon">
                <i class="fas fa-database"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['images_count']) }}</h3>
                <p>Images</p>
            </div>
            <div class="icon">
                <i class="fas fa-image"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($stats['documents_count']) }}</h3>
                <p>Documents</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-pdf"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Media Library</h3>
        <div class="card-tools">
            @can('create media')
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload Files
            </button>
            @endcan
            @can('delete media')
            <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display: none;">
                <i class="fas fa-trash"></i> Delete Selected
            </button>
            <a href="{{ route('admin.media.cleanup') }}" class="btn btn-warning btn-sm" 
               onclick="return confirm('This will delete all orphaned media files. Continue?')">
                <i class="fas fa-broom"></i> Cleanup
            </a>
            @endcan
            <a href="{{ route('admin.media.statistics') }}" class="btn btn-info btn-sm">
                <i class="fas fa-chart-bar"></i> Statistics
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('admin.media.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="mime_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="images" {{ request('mime_type') == 'images' ? 'selected' : '' }}>Images</option>
                        <option value="documents" {{ request('mime_type') == 'documents' ? 'selected' : '' }}>Documents</option>
                        <option value="videos" {{ request('mime_type') == 'videos' ? 'selected' : '' }}>Videos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="collection" class="form-control">
                        <option value="">All Collections</option>
                        @foreach($collections as $collection)
                            <option value="{{ $collection }}" {{ request('collection') == $collection ? 'selected' : '' }}>
                                {{ ucfirst($collection) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="model_type" class="form-control">
                        <option value="">All Models</option>
                        @foreach($modelTypes as $type)
                            <option value="{{ $type['value'] }}" {{ request('model_type') == $type['value'] ? 'selected' : '' }}>
                                {{ $type['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="sort_by" class="form-control">
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date</option>
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="size" {{ request('sort_by') == 'size' ? 'selected' : '' }}>Size</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="sort_order" class="form-control">
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>DESC</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>ASC</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Media Grid -->
        <div class="row" id="mediaGrid">
            @forelse($media as $item)
            <div class="col-md-2 col-sm-4 col-6 mb-3">
                <div class="media-item border rounded p-2" data-media-id="{{ $item->id }}">
                    <div class="media-checkbox position-absolute" style="top: 5px; left: 5px; z-index: 10;">
                        <input type="checkbox" class="media-select" value="{{ $item->id }}">
                    </div>
                    
                    @if(str_starts_with($item->mime_type, 'image/'))
                        <a href="{{ route('admin.media.show', $item) }}">
                            <img src="{{ $item->getUrl('thumb') ?? $item->getUrl() }}" 
                                 class="img-fluid rounded" 
                                 alt="{{ $item->name }}"
                                 style="height: 150px; width: 100%; object-fit: cover;">
                        </a>
                    @elseif(str_starts_with($item->mime_type, 'video/'))
                        <a href="{{ route('admin.media.show', $item) }}">
                            <div class="text-center bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-video fa-3x text-primary"></i>
                            </div>
                        </a>
                    @elseif($item->mime_type == 'application/pdf')
                        <a href="{{ route('admin.media.show', $item) }}">
                            <div class="text-center bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('admin.media.show', $item) }}">
                            <div class="text-center bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-file fa-3x text-secondary"></i>
                            </div>
                        </a>
                    @endif
                    
                    <div class="media-info mt-2">
                        <p class="mb-0 text-truncate" title="{{ $item->name }}">
                            <small><strong>{{ $item->name }}</strong></small>
                        </p>
                        <p class="mb-0">
                            <small class="text-muted">{{ formatBytes($item->size) }}</small>
                        </p>
                        <p class="mb-0">
                            <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                        </p>
                    </div>
                    
                    <div class="media-actions mt-2">
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <a href="{{ route('admin.media.show', $item) }}" class="btn btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.media.download', $item) }}" class="btn btn-success" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            @can('delete media')
                            <form action="{{ route('admin.media.destroy', $item) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger delete-media" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No media files found.
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($media->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $media->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Files</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.media.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="files">Select Files</label>
                        <input type="file" name="files[]" id="files" class="form-control" multiple required>
                        <small class="text-muted">Maximum file size: 10MB per file</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="collection">Collection</label>
                        <input type="text" name="collection" id="collection" class="form-control" 
                               placeholder="default" value="default">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="model_type">Attach to Model (Optional)</label>
                                <select name="model_type" id="model_type" class="form-control">
                                    <option value="">None</option>
                                    <option value="App\Models\Property">Property</option>
                                    <option value="App\Models\User">User</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="model_id">Model ID</label>
                                <input type="number" name="model_id" id="model_id" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle checkbox visibility
    $('.media-select').on('change', function() {
        let checkedCount = $('.media-select:checked').length;
        $('#bulkDeleteBtn').toggle(checkedCount > 0);
    });

    // Bulk delete
    $('#bulkDeleteBtn').on('click', function() {
        let selectedIds = [];
        $('.media-select:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire('Warning', 'Please select files to delete', 'warning');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: `Delete ${selectedIds.length} selected files?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("admin.media.bulk-delete") }}', {
                    _token: '{{ csrf_token() }}',
                    media_ids: selectedIds
                })
                .done(function(response) {
                    Swal.fire('Success', response.success, 'success');
                    location.reload();
                })
                .fail(function(xhr) {
                    const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
                    Swal.fire('Error', error, 'error');
                });
            }
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

@push('styles')
<style>
.media-item {
    position: relative;
    transition: all 0.3s;
}
.media-item:hover {
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
.media-checkbox {
    display: none;
}
.media-item:hover .media-checkbox,
.media-select:checked ~ .media-checkbox {
    display: block;
}
</style>
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