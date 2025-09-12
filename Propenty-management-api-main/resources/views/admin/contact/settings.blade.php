@extends('admin.layouts.app')

@section('title', __('admin.contact_settings') ?? 'Contact Settings')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('admin.contact_settings') ?? 'Contact Settings' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.contact.index') }}">{{ __('admin.contact_messages') ?? 'Contact Messages' }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.settings') ?? 'Settings' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Info Alert -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>{{ __('admin.contact_settings_info') ?? 'Contact Settings Information' }}</strong><br>
                {{ __('admin.contact_settings_description') ?? 'These settings control the contact information displayed on your website\'s contact page. Changes will be reflected immediately on the frontend.' }}
            </div>

            <!-- Settings Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> {{ __('admin.edit_contact_settings') ?? 'Edit Contact Settings' }}
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.contact.settings.update') }}">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            @foreach($settings as $setting)
                            <div class="col-md-6 mb-4">
                                <div class="card {{ $setting->is_displayed ? 'border-success' : 'border-secondary' }}">
                                    <div class="card-header py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label for="setting_{{ $setting->key }}" class="font-weight-bold mb-0">
                                                {{ $setting->label }}
                                                @if($setting->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            <div class="custom-control custom-switch">
                                                <input 
                                                    type="checkbox" 
                                                    class="custom-control-input display-toggle" 
                                                    id="display_{{ $setting->key }}" 
                                                    name="display[{{ $setting->key }}]"
                                                    {{ $setting->is_displayed ? 'checked' : '' }}
                                                    data-target="setting_card_{{ $setting->key }}"
                                                >
                                                <label class="custom-control-label" for="display_{{ $setting->key }}">
                                                    <span class="badge badge-{{ $setting->is_displayed ? 'success' : 'secondary' }}" id="status_{{ $setting->key }}">
                                                        {{ $setting->is_displayed ? 'Shown' : 'Hidden' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        @if($setting->description)
                                            <small class="text-muted">{{ $setting->description }}</small>
                                        @endif
                                    </div>
                                    <div class="card-body" id="setting_card_{{ $setting->key }}" style="{{ !$setting->is_displayed ? 'opacity: 0.5;' : '' }}">
                                        @if($setting->type === 'textarea')
                                            <textarea 
                                                name="settings[{{ $setting->key }}]" 
                                                id="setting_{{ $setting->key }}"
                                                class="form-control @error('settings.' . $setting->key) is-invalid @enderror"
                                                rows="3"
                                                placeholder="{{ $setting->label }}"
                                                {{ $setting->is_required && $setting->is_displayed ? 'required' : '' }}
                                            >{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                        @else
                                            <input 
                                                type="{{ $setting->type === 'phone' ? 'tel' : $setting->type }}" 
                                                name="settings[{{ $setting->key }}]" 
                                                id="setting_{{ $setting->key }}"
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                class="form-control @error('settings.' . $setting->key) is-invalid @enderror"
                                                placeholder="{{ $setting->label }}"
                                                {{ $setting->is_required && $setting->is_displayed ? 'required' : '' }}
                                                @if($setting->type === 'email') 
                                                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                                @endif
                                                @if($setting->type === 'url') 
                                                    pattern="https?://.+"
                                                @endif
                                            >
                                        @endif
                                        
                                        @error('settings.' . $setting->key)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('admin.save_settings') ?? 'Save Settings' }}
                                </button>
                                <a href="{{ route('admin.contact.index') }}" class="btn btn-secondary ml-2">
                                    <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_messages') ?? 'Back to Messages' }}
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    {{ __('admin.required_fields_note') ?? 'Fields marked with * are required' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Preview Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye"></i> {{ __('admin.contact_preview') ?? 'Contact Information Preview' }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('admin.current_contact_info') ?? 'Current Contact Information' }}</h5>
                            
                            @php $phoneSetting = $settings->where('key', 'phone')->first(); @endphp
                            @if($phoneSetting?->value && $phoneSetting->is_displayed)
                            <div class="mb-2">
                                <i class="fas fa-phone text-primary mr-2"></i>
                                <strong>{{ __('admin.phone') ?? 'Phone' }}:</strong>
                                {{ $phoneSetting->value }}
                            </div>
                            @endif
                            
                            @php $emailSetting = $settings->where('key', 'email')->first(); @endphp
                            @if($emailSetting?->value && $emailSetting->is_displayed)
                            <div class="mb-2">
                                <i class="fas fa-envelope text-primary mr-2"></i>
                                <strong>{{ __('admin.email') ?? 'Email' }}:</strong>
                                {{ $emailSetting->value }}
                            </div>
                            @endif
                            
                            @php $addressSetting = $settings->where('key', 'address')->first(); @endphp
                            @if($addressSetting?->value && $addressSetting->is_displayed)
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                                <strong>{{ __('admin.address') ?? 'Address' }}:</strong><br>
                                <span class="ml-4">{!! nl2br(e($addressSetting->value)) !!}</span>
                            </div>
                            @endif
                            
                            @php $businessHoursSetting = $settings->where('key', 'business_hours')->first(); @endphp
                            @if($businessHoursSetting?->value && $businessHoursSetting->is_displayed)
                            <div class="mb-2">
                                <i class="fas fa-clock text-primary mr-2"></i>
                                <strong>{{ __('admin.business_hours') ?? 'Business Hours' }}:</strong><br>
                                <span class="ml-4">{!! nl2br(e($businessHoursSetting->value)) !!}</span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <h5>{{ __('admin.social_links') ?? 'Social Media & Additional Links' }}</h5>
                            
                            @php $whatsappSetting = $settings->where('key', 'whatsapp')->first(); @endphp
                            @if($whatsappSetting?->value && $whatsappSetting->is_displayed)
                            <div class="mb-2">
                                <i class="fab fa-whatsapp text-success mr-2"></i>
                                <strong>WhatsApp:</strong>
                                {{ $whatsappSetting->value }}
                            </div>
                            @endif
                            
                            @php $websiteSetting = $settings->where('key', 'website')->first(); @endphp
                            @if($websiteSetting?->value && $websiteSetting->is_displayed)
                            <div class="mb-2">
                                <i class="fas fa-globe text-primary mr-2"></i>
                                <strong>{{ __('admin.website') ?? 'Website' }}:</strong>
                                <a href="{{ $websiteSetting->value }}" target="_blank">
                                    {{ $websiteSetting->value }}
                                </a>
                            </div>
                            @endif
                            
                            @foreach(['facebook', 'twitter', 'linkedin'] as $social)
                                @php $socialSetting = $settings->where('key', $social)->first(); @endphp
                                @if($socialSetting?->value && $socialSetting->is_displayed)
                                <div class="mb-2">
                                    <i class="fab fa-{{ $social }} text-primary mr-2"></i>
                                    <strong>{{ ucfirst($social) }}:</strong>
                                    <a href="{{ $socialSetting->value }}" target="_blank">
                                        {{ $socialSetting->value }}
                                    </a>
                                </div>
                                @endif
                            @endforeach
                            
                            @if(!$settings->where('key', 'whatsapp')->first()?->value && 
                                !$settings->where('key', 'website')->first()?->value && 
                                !$settings->where('key', 'facebook')->first()?->value &&
                                !$settings->where('key', 'twitter')->first()?->value &&
                                !$settings->where('key', 'linkedin')->first()?->value)
                                <p class="text-muted">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    {{ __('admin.no_social_links') ?? 'No social media links configured yet.' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-format phone numbers (basic formatting)
    $('input[type="tel"]').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 10) {
            let formatted = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            $(this).val(formatted);
        }
    });
    
    // URL validation helper
    $('input[type="url"]').on('blur', function() {
        let url = $(this).val();
        if (url && !url.match(/^https?:\/\//)) {
            $(this).val('https://' + url);
        }
    });

    // Display toggle functionality
    $('.display-toggle').on('change', function() {
        const settingKey = $(this).attr('id').replace('display_', '');
        const cardBody = $('#setting_card_' + settingKey);
        const statusBadge = $('#status_' + settingKey);
        const card = $(this).closest('.card');
        const input = $('#setting_' + settingKey);
        
        if ($(this).is(':checked')) {
            // Show setting
            cardBody.css('opacity', '1');
            statusBadge.removeClass('badge-secondary').addClass('badge-success').text('Shown');
            card.removeClass('border-secondary').addClass('border-success');
            
            // Add required attribute if setting is required
            if (input.data('required')) {
                input.attr('required', true);
            }
        } else {
            // Hide setting
            cardBody.css('opacity', '0.5');
            statusBadge.removeClass('badge-success').addClass('badge-secondary').text('Hidden');
            card.removeClass('border-success').addClass('border-secondary');
            
            // Remove required attribute when hidden
            input.removeAttr('required');
        }
        
        // Update preview in real-time
        updatePreview();
    });
    
    // Update preview function
    function updatePreview() {
        // This would update the preview section in real-time
        // For now, we'll keep the existing server-side preview
        // In a future enhancement, we could add AJAX calls to update preview
    }
    
    // Initialize toggle states on page load
    $('.display-toggle').each(function() {
        $(this).trigger('change');
    });
});
</script>
@endpush