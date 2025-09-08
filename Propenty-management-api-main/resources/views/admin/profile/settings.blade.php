@extends('admin.layouts.app')

@section('title', 'Account Settings')

@section('content-header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Account Settings</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.profile.index') }}">Profile</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-3">
                <!-- Settings Menu -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Settings Menu</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a href="#notifications" class="nav-link active" data-toggle="tab">
                                    <i class="fas fa-bell"></i> Notifications
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#preferences" class="nav-link" data-toggle="tab">
                                    <i class="fas fa-sliders-h"></i> Preferences
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#privacy" class="nav-link" data-toggle="tab">
                                    <i class="fas fa-lock"></i> Privacy
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#danger" class="nav-link" data-toggle="tab">
                                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <form action="{{ route('admin.profile.update-settings') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="tab-content">
                        <!-- Notifications Tab -->
                        <div class="tab-pane active" id="notifications">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Notification Preferences</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="notification_email" name="notification_email" value="1" 
                                                   {{ session('user_settings.notification_email', true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="notification_email">
                                                <strong>Email Notifications</strong>
                                                <br>
                                                <small class="text-muted">Receive notifications via email</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="notification_sms" name="notification_sms" value="1"
                                                   {{ session('user_settings.notification_sms', false) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="notification_sms">
                                                <strong>SMS Notifications</strong>
                                                <br>
                                                <small class="text-muted">Receive notifications via SMS (if phone number is provided)</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="newsletter" name="newsletter" value="1"
                                                   {{ session('user_settings.newsletter', true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="newsletter">
                                                <strong>Newsletter</strong>
                                                <br>
                                                <small class="text-muted">Receive our weekly newsletter</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="marketing_emails" name="marketing_emails" value="1"
                                                   {{ session('user_settings.marketing_emails', false) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="marketing_emails">
                                                <strong>Marketing Emails</strong>
                                                <br>
                                                <small class="text-muted">Receive promotional offers and updates</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preferences Tab -->
                        <div class="tab-pane" id="preferences">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Display Preferences</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="timezone">Timezone</label>
                                        <select class="form-control" id="timezone" name="timezone">
                                            <option value="UTC" {{ session('user_settings.timezone', 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                            <option value="Asia/Damascus" {{ session('user_settings.timezone') == 'Asia/Damascus' ? 'selected' : '' }}>Asia/Damascus (Syria)</option>
                                            <option value="America/New_York" {{ session('user_settings.timezone') == 'America/New_York' ? 'selected' : '' }}>America/New York</option>
                                            <option value="Europe/London" {{ session('user_settings.timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                            <option value="Asia/Dubai" {{ session('user_settings.timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="language">Language</label>
                                        <select class="form-control" id="language" name="language">
                                            <option value="en" {{ session('user_settings.language', 'en') == 'en' ? 'selected' : '' }}>English</option>
                                            <option value="ar" {{ session('user_settings.language') == 'ar' ? 'selected' : '' }}>Arabic</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="date_format">Date Format</label>
                                        <select class="form-control" id="date_format" name="date_format">
                                            <option value="Y-m-d" {{ session('user_settings.date_format', 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>{{ date('Y-m-d') }} (YYYY-MM-DD)</option>
                                            <option value="d/m/Y" {{ session('user_settings.date_format') == 'd/m/Y' ? 'selected' : '' }}>{{ date('d/m/Y') }} (DD/MM/YYYY)</option>
                                            <option value="m/d/Y" {{ session('user_settings.date_format') == 'm/d/Y' ? 'selected' : '' }}>{{ date('m/d/Y') }} (MM/DD/YYYY)</option>
                                            <option value="d M, Y" {{ session('user_settings.date_format') == 'd M, Y' ? 'selected' : '' }}>{{ date('d M, Y') }} (DD Mon, YYYY)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Privacy Tab -->
                        <div class="tab-pane" id="privacy">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Privacy Settings</h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="icon fas fa-info"></i>
                                        Privacy settings help control who can see your information.
                                    </div>

                                    <div class="form-group">
                                        <label>Profile Visibility</label>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="profile_public" name="profile_visibility" value="public">
                                            <label for="profile_public" class="custom-control-label">
                                                Public - Anyone can view your profile
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="profile_users" name="profile_visibility" value="users" checked>
                                            <label for="profile_users" class="custom-control-label">
                                                Users Only - Only logged-in users can view your profile
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="profile_private" name="profile_visibility" value="private">
                                            <label for="profile_private" class="custom-control-label">
                                                Private - Only you can view your profile
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Activity Status</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="show_online_status" name="show_online_status" value="1" checked>
                                            <label class="custom-control-label" for="show_online_status">
                                                Show when I'm online
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Search Engine</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="searchable" name="searchable" value="1">
                                            <label class="custom-control-label" for="searchable">
                                                Allow search engines to index my profile
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone Tab -->
                        <div class="tab-pane" id="danger">
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h3 class="card-title">Danger Zone</h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="icon fas fa-exclamation-triangle"></i>
                                        These actions are permanent and cannot be undone. Please proceed with caution.
                                    </div>

                                    <div class="form-group">
                                        <h5>Export Account Data</h5>
                                        <p class="text-muted">Download all your data in a machine-readable format.</p>
                                        <button type="button" class="btn btn-info" onclick="alert('Feature coming soon!')">
                                            <i class="fas fa-download"></i> Export Data
                                        </button>
                                    </div>

                                    <hr>

                                    <div class="form-group">
                                        <h5>Delete Account</h5>
                                        <p class="text-muted">Permanently delete your account and all associated data.</p>
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteAccountModal">
                                            <i class="fas fa-trash"></i> Delete Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="{{ route('admin.profile.index') }}" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Back to Profile
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.profile.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title">Delete Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>Warning!</strong> This action cannot be undone. All your data will be permanently deleted.
                        </div>
                        
                        <div class="form-group">
                            <label for="delete_password">Enter your password to confirm:</label>
                            <input type="password" class="form-control" id="delete_password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Type "DELETE" to confirm:</label>
                            <input type="text" class="form-control" id="delete_confirm" pattern="DELETE" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                            <i class="fas fa-trash"></i> Delete My Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Enable delete button only when user types DELETE
    $('#delete_confirm').on('input', function() {
        if ($(this).val() === 'DELETE') {
            $('#confirmDeleteBtn').prop('disabled', false);
        } else {
            $('#confirmDeleteBtn').prop('disabled', true);
        }
    });

    // Switch active tab based on URL hash
    $(document).ready(function() {
        if (window.location.hash) {
            $('.nav-link[href="' + window.location.hash + '"]').click();
        }
    });
</script>
@endpush
