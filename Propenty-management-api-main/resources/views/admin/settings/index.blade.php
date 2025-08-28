@extends('admin.layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Settings</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-cog"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">General Settings</span>
                                <span class="info-box-number">Configure basic application settings</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Email Settings</span>
                                <span class="info-box-number">SMTP configuration</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-map"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Maps Settings</span>
                                <span class="info-box-number">Google Maps API configuration</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger">
                                <i class="fas fa-trash"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cache Management</span>
                                <span class="info-box-number">Clear application cache</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Coming Soon!</h5>
                    Settings functionality is currently under development. Advanced settings panels will be available in future updates.
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                @can('clear cache')
                                <button type="button" class="btn btn-warning" id="clearCacheBtn" 
                                        data-url="{{ route('admin.settings.cache-clear') }}">
                                    <i class="fas fa-trash"></i> Clear All Cache
                                </button>
                                @endcan
                                
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#clearCacheBtn').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();
        const url = btn.data('url');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Clearing...');
        
        $.post(url, {
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            Swal.fire('Error', error, 'error');
        })
        .always(function() {
            btn.prop('disabled', false).html(originalText);
        });
    });
});
</script>
@endpush