<form action="{{ route('admin.settings.seo') }}" method="POST" class="settings-form">
    @csrf
    <div class="card-body">
        <div class="form-group">
            <label for="meta_title">Meta Title</label>
            <input type="text" name="meta_title" id="meta_title" class="form-control @error('meta_title') is-invalid @enderror" 
                   value="{{ old('meta_title', $settings->get('meta_title')->value ?? '') }}" maxlength="60">
            @error('meta_title')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Default meta title for SEO (max 60 characters)</small>
        </div>

        <div class="form-group">
            <label for="meta_description">Meta Description</label>
            <textarea name="meta_description" id="meta_description" class="form-control @error('meta_description') is-invalid @enderror" 
                      rows="3" maxlength="160">{{ old('meta_description', $settings->get('meta_description')->value ?? '') }}</textarea>
            @error('meta_description')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Default meta description for SEO (max 160 characters)</small>
        </div>

        <div class="form-group">
            <label for="meta_keywords">Meta Keywords</label>
            <input type="text" name="meta_keywords" id="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" 
                   value="{{ old('meta_keywords', $settings->get('meta_keywords')->value ?? '') }}" maxlength="255">
            @error('meta_keywords')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="form-text text-muted">Comma-separated keywords for SEO (max 255 characters)</small>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="google_analytics_id">Google Analytics ID</label>
                    <input type="text" name="google_analytics_id" id="google_analytics_id" 
                           class="form-control @error('google_analytics_id') is-invalid @enderror" 
                           value="{{ old('google_analytics_id', $settings->get('google_analytics_id')->value ?? '') }}"
                           placeholder="G-XXXXXXXXXX">
                    @error('google_analytics_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Google Analytics tracking ID</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="google_tag_manager_id">Google Tag Manager ID</label>
                    <input type="text" name="google_tag_manager_id" id="google_tag_manager_id" 
                           class="form-control @error('google_tag_manager_id') is-invalid @enderror" 
                           value="{{ old('google_tag_manager_id', $settings->get('google_tag_manager_id')->value ?? '') }}"
                           placeholder="GTM-XXXXXXX">
                    @error('google_tag_manager_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Google Tag Manager ID</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save SEO Settings
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</form>