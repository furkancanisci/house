<form action="{{ route('admin.settings.social') }}" method="POST" class="settings-form">
    @csrf
    <div class="card-body">
        <div class="form-group">
            <label for="facebook_url">Facebook Page URL</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-facebook text-primary"></i></span>
                </div>
                <input type="url" name="facebook_url" id="facebook_url" 
                       class="form-control @error('facebook_url') is-invalid @enderror" 
                       value="{{ old('facebook_url', $settings->get('facebook_url')->value ?? '') }}"
                       placeholder="https://www.facebook.com/yourpage">
                @error('facebook_url')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <small class="form-text text-muted">Your Facebook business page URL</small>
        </div>

        <div class="form-group">
            <label for="twitter_url">Twitter Profile URL</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-twitter text-info"></i></span>
                </div>
                <input type="url" name="twitter_url" id="twitter_url" 
                       class="form-control @error('twitter_url') is-invalid @enderror" 
                       value="{{ old('twitter_url', $settings->get('twitter_url')->value ?? '') }}"
                       placeholder="https://twitter.com/yourusername">
                @error('twitter_url')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <small class="form-text text-muted">Your Twitter profile URL</small>
        </div>

        <div class="form-group">
            <label for="instagram_url">Instagram Profile URL</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-instagram text-danger"></i></span>
                </div>
                <input type="url" name="instagram_url" id="instagram_url" 
                       class="form-control @error('instagram_url') is-invalid @enderror" 
                       value="{{ old('instagram_url', $settings->get('instagram_url')->value ?? '') }}"
                       placeholder="https://www.instagram.com/yourusername">
                @error('instagram_url')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <small class="form-text text-muted">Your Instagram profile URL</small>
        </div>

        <div class="form-group">
            <label for="linkedin_url">LinkedIn Page URL</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-linkedin text-primary"></i></span>
                </div>
                <input type="url" name="linkedin_url" id="linkedin_url" 
                       class="form-control @error('linkedin_url') is-invalid @enderror" 
                       value="{{ old('linkedin_url', $settings->get('linkedin_url')->value ?? '') }}"
                       placeholder="https://www.linkedin.com/company/yourcompany">
                @error('linkedin_url')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <small class="form-text text-muted">Your LinkedIn company page URL</small>
        </div>

        <div class="form-group">
            <label for="youtube_url">YouTube Channel URL</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-youtube text-danger"></i></span>
                </div>
                <input type="url" name="youtube_url" id="youtube_url" 
                       class="form-control @error('youtube_url') is-invalid @enderror" 
                       value="{{ old('youtube_url', $settings->get('youtube_url')->value ?? '') }}"
                       placeholder="https://www.youtube.com/c/yourchannel">
                @error('youtube_url')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <small class="form-text text-muted">Your YouTube channel URL</small>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> Social media links will appear in your website footer and contact sections.
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-info">
            <i class="fas fa-save"></i> Save Social Media Settings
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</form>