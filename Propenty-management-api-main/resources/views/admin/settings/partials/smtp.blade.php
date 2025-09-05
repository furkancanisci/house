<form action="{{ route('admin.settings.smtp') }}" method="POST" class="settings-form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mail_host">SMTP Host *</label>
                    <input type="text" name="mail_host" id="mail_host" 
                           class="form-control @error('mail_host') is-invalid @enderror" 
                           value="{{ old('mail_host', $settings->get('mail_host')->value ?? 'smtp.gmail.com') }}" required>
                    @error('mail_host')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">SMTP server hostname</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mail_port">SMTP Port *</label>
                    <input type="number" name="mail_port" id="mail_port" 
                           class="form-control @error('mail_port') is-invalid @enderror" 
                           value="{{ old('mail_port', $settings->get('mail_port')->value ?? 587) }}" 
                           min="1" max="65535" required>
                    @error('mail_port')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">SMTP server port (common: 587 for TLS, 465 for SSL)</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mail_username">SMTP Username *</label>
                    <input type="text" name="mail_username" id="mail_username" 
                           class="form-control @error('mail_username') is-invalid @enderror" 
                           value="{{ old('mail_username', $settings->get('mail_username')->value ?? '') }}" required>
                    @error('mail_username')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">SMTP username (usually your email)</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mail_password">SMTP Password</label>
                    <input type="password" name="mail_password" id="mail_password" 
                           class="form-control @error('mail_password') is-invalid @enderror" 
                           placeholder="Leave blank to keep current password">
                    @error('mail_password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">SMTP password (leave blank to keep current)</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="mail_encryption">Encryption</label>
                    <select name="mail_encryption" id="mail_encryption" class="form-control @error('mail_encryption') is-invalid @enderror">
                        <option value="">None</option>
                        <option value="tls" {{ old('mail_encryption', $settings->get('mail_encryption')->value ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('mail_encryption', $settings->get('mail_encryption')->value ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                    @error('mail_encryption')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Encryption method</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="mail_from_address">From Email *</label>
                    <input type="email" name="mail_from_address" id="mail_from_address" 
                           class="form-control @error('mail_from_address') is-invalid @enderror" 
                           value="{{ old('mail_from_address', $settings->get('mail_from_address')->value ?? '') }}" required>
                    @error('mail_from_address')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Default sender email</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="mail_from_name">From Name *</label>
                    <input type="text" name="mail_from_name" id="mail_from_name" 
                           class="form-control @error('mail_from_name') is-invalid @enderror" 
                           value="{{ old('mail_from_name', $settings->get('mail_from_name')->value ?? '') }}" required>
                    @error('mail_from_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Default sender name</small>
                </div>
            </div>
        </div>

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Security Note:</strong> Use app passwords for Gmail and other services that support them. Never use your main account password.
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-secondary">
            <i class="fas fa-save"></i> Save SMTP Settings
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</form>