<form action="{{ route('admin.settings.security') }}" method="POST" class="settings-form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="enable_recaptcha" 
                               name="enable_recaptcha" value="1" 
                               {{ old('enable_recaptcha', $settings->get('enable_recaptcha')->value ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="enable_recaptcha">Enable Google reCAPTCHA</label>
                    </div>
                    <small class="form-text text-muted">Enable Google reCAPTCHA on contact and registration forms</small>
                </div>
            </div>
        </div>

        <div class="row" id="recaptcha-settings" style="{{ old('enable_recaptcha', $settings->get('enable_recaptcha')->value ?? false) ? '' : 'display: none;' }}">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="recaptcha_site_key">reCAPTCHA Site Key</label>
                    <input type="text" name="recaptcha_site_key" id="recaptcha_site_key" 
                           class="form-control @error('recaptcha_site_key') is-invalid @enderror" 
                           value="{{ old('recaptcha_site_key', $settings->get('recaptcha_site_key')->value ?? '') }}">
                    @error('recaptcha_site_key')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Google reCAPTCHA site key (public)</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="recaptcha_secret_key">reCAPTCHA Secret Key</label>
                    <input type="password" name="recaptcha_secret_key" id="recaptcha_secret_key" 
                           class="form-control @error('recaptcha_secret_key') is-invalid @enderror" 
                           value="{{ old('recaptcha_secret_key', $settings->get('recaptcha_secret_key')->value ?? '') }}">
                    @error('recaptcha_secret_key')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Google reCAPTCHA secret key (private)</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="max_login_attempts">Max Login Attempts *</label>
                    <input type="number" name="max_login_attempts" id="max_login_attempts" 
                           class="form-control @error('max_login_attempts') is-invalid @enderror" 
                           value="{{ old('max_login_attempts', $settings->get('max_login_attempts')->value ?? 5) }}" 
                           min="1" max="20" required>
                    @error('max_login_attempts')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Maximum login attempts before account lockout (1-20)</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="lockout_duration">Lockout Duration (seconds) *</label>
                    <input type="number" name="lockout_duration" id="lockout_duration" 
                           class="form-control @error('lockout_duration') is-invalid @enderror" 
                           value="{{ old('lockout_duration', $settings->get('lockout_duration')->value ?? 900) }}" 
                           min="60" max="7200" required>
                    @error('lockout_duration')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Account lockout duration in seconds (60-7200)</small>
                </div>
            </div>
        </div>

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Important:</strong> Security settings affect user access to your system. Test changes carefully.
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>reCAPTCHA Setup:</strong> Get your reCAPTCHA keys from 
            <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin</a>
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-dark">
            <i class="fas fa-save"></i> Save Security Settings
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Toggle reCAPTCHA settings visibility
    $('#enable_recaptcha').on('change', function() {
        if ($(this).is(':checked')) {
            $('#recaptcha-settings').slideDown();
        } else {
            $('#recaptcha-settings').slideUp();
        }
    });
});
</script>