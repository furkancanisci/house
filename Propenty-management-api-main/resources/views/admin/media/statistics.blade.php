@extends('admin.layouts.app')

@section('title', 'Media Statistics')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.media.index') }}">Media Library</a></li>
    <li class="breadcrumb-item active">Statistics</li>
</ol>
@endsection

@section('content')
<div class="row">
    <!-- By Collection -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Media by Collection</h3>
            </div>
            <div class="card-body">
                <canvas id="collectionChart"></canvas>
                <table class="table table-sm mt-3">
                    <thead>
                        <tr>
                            <th>Collection</th>
                            <th>Files</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['by_collection'] as $item)
                        <tr>
                            <td>{{ ucfirst($item->collection_name) }}</td>
                            <td>{{ number_format($item->count) }}</td>
                            <td>{{ formatBytes($item->total_size) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- By Type -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Media by Type</h3>
            </div>
            <div class="card-body">
                <canvas id="typeChart"></canvas>
                <table class="table table-sm mt-3">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Files</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['by_type'] as $item)
                        <tr>
                            <td>{{ $item->type }}</td>
                            <td>{{ number_format($item->count) }}</td>
                            <td>{{ formatBytes($item->total_size) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <!-- By Model -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Media by Model</h3>
            </div>
            <div class="card-body">
                <canvas id="modelChart"></canvas>
                <table class="table table-sm mt-3">
                    <thead>
                        <tr>
                            <th>Model</th>
                            <th>Files</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['by_model'] as $item)
                        <tr>
                            <td>{{ $item->model_name ?: 'None' }}</td>
                            <td>{{ number_format($item->count) }}</td>
                            <td>{{ formatBytes($item->total_size) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Storage Analysis -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Storage Analysis</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-database"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Storage Used</span>
                                <span class="info-box-number">
                                    {{ formatBytes(\Spatie\MediaLibrary\MediaCollections\Models\Media::sum('size')) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-file"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Average File Size</span>
                                <span class="info-box-number">
                                    {{ formatBytes(\Spatie\MediaLibrary\MediaCollections\Models\Media::avg('size')) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <!-- Recent Uploads -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Uploads</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['recent_uploads'] as $media)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.media.show', $media) }}">
                                        {{ Str::limit($media->name, 30) }}
                                    </a>
                                </td>
                                <td>{{ explode('/', $media->mime_type)[1] ?? 'file' }}</td>
                                <td>{{ formatBytes($media->size) }}</td>
                                <td>{{ $media->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Largest Files -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Largest Files</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['largest_files'] as $media)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.media.show', $media) }}">
                                        {{ Str::limit($media->name, 30) }}
                                    </a>
                                </td>
                                <td>{{ explode('/', $media->mime_type)[1] ?? 'file' }}</td>
                                <td class="text-danger font-weight-bold">{{ formatBytes($media->size) }}</td>
                                <td>
                                    <a href="{{ route('admin.media.show', $media) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Collection Chart
    new Chart(document.getElementById('collectionChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($stats['by_collection']->pluck('collection_name')->map(fn($c) => ucfirst($c))) !!},
            datasets: [{
                data: {!! json_encode($stats['by_collection']->pluck('count')) !!},
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            }
        }
    });

    // Type Chart
    new Chart(document.getElementById('typeChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($stats['by_type']->pluck('type')) !!},
            datasets: [{
                data: {!! json_encode($stats['by_type']->pluck('count')) !!},
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            }
        }
    });

    // Model Chart
    new Chart(document.getElementById('modelChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($stats['by_model']->pluck('model_name')) !!},
            datasets: [{
                label: 'Files',
                data: {!! json_encode($stats['by_model']->pluck('count')) !!},
                backgroundColor: '#007bff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
canvas {
    max-height: 300px;
}
</style>
@endpush

@php
function formatBytes($bytes, $precision = 2) {
    if (!$bytes) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
@endphp