<form action="{{ route('admin.settings.media') }}" method="POST" class="settings-form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="max_upload_size">Max Upload Size (MB) *</label>
                    <input type="number" name="max_upload_size" id="max_upload_size" 
                           class="form-control @error('max_upload_size') is-invalid @enderror" 
                           value="{{ old('max_upload_size', $settings->get('max_upload_size')->value ?? 5) }}" 
                           min="1" max="20" required>
                    @error('max_upload_size')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Maximum file upload size in megabytes (1-20 MB)</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="image_quality">Image Quality *</label>
                    <input type="number" name="image_quality" id="image_quality" 
                           class="form-control @error('image_quality') is-invalid @enderror" 
                           value="{{ old('image_quality', $settings->get('image_quality')->value ?? 85) }}" 
                           min="50" max="100" required>
                    @error('image_quality')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Image compression quality (50-100)</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="allowed_image_types">Allowed Image Types *</label>
            <input type="text" name="allowed_image_types" id="allowed_image_types" 
                   class="form-control @error('allowed_image_types') is-invalid @enderror" 
                   value="{{ old('allowed_image_types', $settings->get('allowed_image_types')->value ?? 'jpeg,jpg,png,gif,webp') }}" 
                   required>
            @error('allowed_image_types')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Comma-separated file extensions (e.g., jpeg,jpg,png,gif,webp)</small>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="generate_thumbnails" 
                               name="generate_thumbnails" value="1" 
                               {{ old('generate_thumbnails', $settings->get('generate_thumbnails')->value ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="generate_thumbnails">Generate Thumbnails</label>
                    </div>
                    <small class="form-text text-muted">Automatically generate image thumbnails</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="watermark_enabled" 
                               name="watermark_enabled" value="1" 
                               {{ old('watermark_enabled', $settings->get('watermark_enabled')->value ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="watermark_enabled">Enable Watermarks</label>
                    </div>
                    <small class="form-text text-muted">Add watermarks to uploaded images</small>
                </div>
            </div>
        </div>

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Note:</strong> Changes to upload size require server configuration updates to take full effect.
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-warning">
            <i class="fas fa-save"></i> Save Media Settings
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</form>