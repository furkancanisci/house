<form action="{{ route('admin.settings.listings') }}" method="POST" class="settings-form">
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="listings_per_page">Listings Per Page *</label>
                    <input type="number" name="listings_per_page" id="listings_per_page" 
                           class="form-control @error('listings_per_page') is-invalid @enderror" 
                           value="{{ old('listings_per_page', $settings->get('listings_per_page')->value ?? 12) }}" 
                           min="5" max="100" required>
                    @error('listings_per_page')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Number of listings to display per page (5-100)</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="featured_listings_limit">Featured Listings Limit *</label>
                    <input type="number" name="featured_listings_limit" id="featured_listings_limit" 
                           class="form-control @error('featured_listings_limit') is-invalid @enderror" 
                           value="{{ old('featured_listings_limit', $settings->get('featured_listings_limit')->value ?? 8) }}" 
                           min="1" max="50" required>
                    @error('featured_listings_limit')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Maximum featured listings on homepage (1-50)</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="max_images_per_listing">Max Images Per Listing *</label>
                    <input type="number" name="max_images_per_listing" id="max_images_per_listing" 
                           class="form-control @error('max_images_per_listing') is-invalid @enderror" 
                           value="{{ old('max_images_per_listing', $settings->get('max_images_per_listing')->value ?? 20) }}" 
                           min="1" max="50" required>
                    @error('max_images_per_listing')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Maximum images allowed per listing (1-50)</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="auto_approve_listings" 
                               name="auto_approve_listings" value="1" 
                               {{ old('auto_approve_listings', $settings->get('auto_approve_listings')->value ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="auto_approve_listings">Auto Approve Listings</label>
                    </div>
                    <small class="form-text text-muted">Automatically approve new property listings</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="allow_guest_inquiries" 
                               name="allow_guest_inquiries" value="1" 
                               {{ old('allow_guest_inquiries', $settings->get('allow_guest_inquiries')->value ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="allow_guest_inquiries">Allow Guest Inquiries</label>
                    </div>
                    <small class="form-text text-muted">Allow non-registered users to make inquiries</small>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> Changes to listing display settings will take effect immediately on your website.
        </div>
    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Save Listing Settings
        </button>
        <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
</form>