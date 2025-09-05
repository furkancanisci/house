@extends('admin.layouts.app')

@section('title', 'System Settings')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
</ol>
@endsection

@section('content')
<div class="row">
    <!-- Settings Navigation -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Settings Categories</h3>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-pills flex-column" id="settingsNav">
                    @foreach($groups as $groupKey => $groupInfo)
                    <li class="nav-item">
                        <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                           id="{{ $groupKey }}-tab" 
                           data-toggle="pill" 
                           href="#{{ $groupKey }}" 
                           role="tab">
                            <i class="{{ $groupInfo['icon'] }}"></i>
                            {{ $groupInfo['title'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                @can('clear cache')
                <button type="button" class="btn btn-warning btn-block" id="clearCacheBtn" 
                        data-url="{{ route('admin.settings.cache-clear') }}">
                    <i class="fas fa-trash"></i> Clear All Cache
                </button>
                @endcan
                
                <button type="button" class="btn btn-info btn-block" id="testEmailBtn">
                    <i class="fas fa-paper-plane"></i> Test Email
                </button>

                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="col-md-9">
        <div class="tab-content" id="settingsTabContent">
            @foreach($groups as $groupKey => $groupInfo)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                 id="{{ $groupKey }}" 
                 role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="{{ $groupInfo['icon'] }} text-{{ $groupInfo['color'] }}"></i>
                            {{ $groupInfo['title'] }}
                        </h3>
                        <p class="card-text">{{ $groupInfo['description'] }}</p>
                    </div>

                    @if($groupKey == 'general')
                        @include('admin.settings.partials.general', ['settings' => $settingsByGroup[$groupKey]])
                    @elseif($groupKey == 'listings')
                        @include('admin.settings.partials.listings', ['settings' => $settingsByGroup[$groupKey]])
                    @elseif($groupKey == 'seo')
                        @include('admin.settings.partials.seo', ['settings' => $settingsByGroup[$groupKey]])
                    @elseif($groupKey == 'media')
                        @include('admin.settings.partials.media', ['settings' => $settingsByGroup[$groupKey]])
                    @elseif($groupKey == 'maps')
                        @include('admin.settings.partials.maps', ['settings' => $settingsByGroup[$groupKey]])
                    @elseif($groupKey == 'smtp')
                        @include('admin.settings.partials.smtp', ['settings' => $settingsByGroup[$groupKey]])
                    @elseif($groupKey == 'social')
                        @include('admin.settings.partials.social', ['settings' => $settingsByGroup[$groupKey]])
                    @elseif($groupKey == 'security')
                        @include('admin.settings.partials.security', ['settings' => $settingsByGroup[$groupKey]])
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Send Test Email</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="testEmailForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="test_email">Email Address</label>
                        <input type="email" name="test_email" id="test_email" class="form-control" 
                               placeholder="Enter email address to send test email" required>
                        <small class="form-text text-muted">A test email will be sent to verify SMTP configuration</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Test Email
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
    // Clear cache functionality
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

    // Test email functionality
    $('#testEmailBtn').on('click', function() {
        $('#testEmailModal').modal('show');
    });

    $('#testEmailForm').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        
        $.post('{{ route("admin.settings.test-email") }}', $(this).serialize())
        .done(function(response) {
            $('#testEmailModal').modal('hide');
            if (response.success) {
                Swal.fire('Success', response.message, 'success');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to send test email';
            Swal.fire('Error', error, 'error');
        })
        .always(function() {
            btn.prop('disabled', false).html(originalText);
        });
    });

    // Form submission with loading states
    $('.settings-form').on('submit', function() {
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        // Re-enable after 3 seconds in case of issues
        setTimeout(function() {
            btn.prop('disabled', false).html(originalText);
        }, 3000);
    });

    // Auto-save for some settings (optional)
    $('.auto-save').on('change', function() {
        const form = $(this).closest('form');
        const formData = form.serialize();
        const url = form.attr('action');
        
        $.post(url, formData)
        .done(function(response) {
            // Show success notification
            toastr.success('Setting saved automatically');
        });
    });
});
</script>
@endpush