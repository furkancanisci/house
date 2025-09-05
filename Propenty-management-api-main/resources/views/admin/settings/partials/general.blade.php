<form action="{{ route('admin.settings.general') }}" method="POST" class="settings-form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="site_name">Site Name *</label>
                    <input type="text" name="site_name" id="site_name" class="form-control @error('site_name') is-invalid @enderror" 
                           value="{{ old('site_name', $settings->get('site_name')->value ?? '') }}" required>
                    @error('site_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">The name of your website</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="contact_email">Contact Email *</label>
                    <input type="email" name="contact_email" id="contact_email" class="form-control @error('contact_email') is-invalid @enderror" 
                           value="{{ old('contact_email', $settings->get('contact_email')->value ?? '') }}" required>
                    @error('contact_email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Main contact email address</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="site_description">Site Description</label>
            <textarea name="site_description" id="site_description" class="form-control @error('site_description') is-invalid @enderror" 
                      rows="3" maxlength="500">{{ old('site_description', $settings->get('site_description')->value ?? '') }}</textarea>
            @error('site_description')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Brief description of your website (max 500 characters)</small>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="contact_phone">Contact Phone</label>
                    <input type="text" name="contact_phone" id="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" 
                           value="{{ old('contact_phone', $settings->get('contact_phone')->value ?? '') }}">
                    @error('contact_phone')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Main contact phone number</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="timezone">Timezone *</label>
                    <select name="timezone" id="timezone" class="form-control @error('timezone') is-invalid @enderror" required>
                        <option value="UTC" {{ old('timezone', $settings->get('timezone')->value ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="America/New_York" {{ old('timezone', $settings->get('timezone')->value ?? '') == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                        <option value="America/Chicago" {{ old('timezone', $settings->get('timezone')->value ?? '') == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                        <option value="America/Denver" {{ old('timezone', $settings->get('timezone')->value ?? '') == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                        <option value="America/Los_Angeles" {{ old('timezone', $settings->get('timezone')->value ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                        <option value="Europe/London" {{ old('timezone', $settings->get('timezone')->value ?? '') == 'Europe/London' ? 'selected' : '' }}>London</option>
                        <option value="Europe/Paris" {{ old('timezone', $settings->get('timezone')->value ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                        <option value="Asia/Tokyo" {{ old('timezone', $settings->get('timezone')->value ?? '') == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                    </select>
                    @error('timezone')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="address">Business Address</label>
            <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" 
                   value="{{ old('address', $settings->get('address')->value ?? '') }}">
            @error('address')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Your business address</small>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="currency">Currency *</label>
                    <select name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required>
                        <option value="USD" {{ old('currency', $settings->get('currency')->value ?? '') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="EUR" {{ old('currency', $settings->get('currency')->value ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="GBP" {{ old('currency', $settings->get('currency')->value ?? '') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                        <option value="CAD" {{ old('currency', $settings->get('currency')->value ?? '') == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                        <option value="AUD" {{ old('currency', $settings->get('currency')->value ?? '') == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                    </select>
                    @error('currency')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="currency_symbol">Currency Symbol *</label>
                    <input type="text" name="currency_symbol" id="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" 
                           value="{{ old('currency_symbol', $settings->get('currency_symbol')->value ?? '') }}" maxlength="5" required>
                    @error('currency_symbol')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="maintenance_mode" name="maintenance_mode" value="1" 
                               {{ old('maintenance_mode', $settings->get('maintenance_mode')->value ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="maintenance_mode">Maintenance Mode</label>
                    </div>
                    <small class="form-text text-muted">Enable maintenance mode to show a maintenance page to visitors</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save General Settings
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</form>