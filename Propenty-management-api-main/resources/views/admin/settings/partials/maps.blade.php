<form action="{{ route('admin.settings.maps') }}" method="POST" class="settings-form">
    @csrf
    <div class="card-body">
        <div class="form-group">
            <label for="google_maps_api_key">Google Maps API Key</label>
            <input type="text" name="google_maps_api_key" id="google_maps_api_key" 
                   class="form-control @error('google_maps_api_key') is-invalid @enderror" 
                   value="{{ old('google_maps_api_key', $settings->get('google_maps_api_key')->value ?? '') }}">
            @error('google_maps_api_key')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Google Maps API key for map functionality</small>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="default_latitude">Default Latitude *</label>
                    <input type="number" name="default_latitude" id="default_latitude" 
                           class="form-control @error('default_latitude') is-invalid @enderror" 
                           value="{{ old('default_latitude', $settings->get('default_latitude')->value ?? 40.7128) }}" 
                           step="0.000001" min="-90" max="90" required>
                    @error('default_latitude')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Default map center latitude (-90 to 90)</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="default_longitude">Default Longitude *</label>
                    <input type="number" name="default_longitude" id="default_longitude" 
                           class="form-control @error('default_longitude') is-invalid @enderror" 
                           value="{{ old('default_longitude', $settings->get('default_longitude')->value ?? -74.0060) }}" 
                           step="0.000001" min="-180" max="180" required>
                    @error('default_longitude')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Default map center longitude (-180 to 180)</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="default_zoom">Default Zoom Level *</label>
                    <input type="number" name="default_zoom" id="default_zoom" 
                           class="form-control @error('default_zoom') is-invalid @enderror" 
                           value="{{ old('default_zoom', $settings->get('default_zoom')->value ?? 12) }}" 
                           min="1" max="20" required>
                    @error('default_zoom')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Default map zoom level (1-20)</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="enable_street_view" 
                       name="enable_street_view" value="1" 
                       {{ old('enable_street_view', $settings->get('enable_street_view')->value ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="enable_street_view">Enable Street View</label>
            </div>
            <small class="form-text text-muted">Enable Google Street View on property pages</small>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> You need a valid Google Maps API key to enable map functionality. 
            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Get API Key</a>
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-save"></i> Save Maps Settings
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</form>