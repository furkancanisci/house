@extends('admin.layouts.app')

@section('title', __('admin.edit_price_type'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.edit_price_type') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.price-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('admin.back') }}
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.price-types.update', $priceType) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_ar" class="form-label">{{ __('admin.name_ar') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" name="name_ar" value="{{ old('name_ar', $priceType->name_ar) }}" required>
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_en" class="form-label">{{ __('admin.name_en') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" name="name_en" value="{{ old('name_en', $priceType->name_en) }}" required>
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_ku" class="form-label">{{ __('admin.name_ku') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_ku') is-invalid @enderror" 
                                           id="name_ku" name="name_ku" value="{{ old('name_ku', $priceType->name_ku) }}" required>
                                    @error('name_ku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="key" class="form-label">{{ __('admin.key') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('key') is-invalid @enderror" 
                                           id="key" name="key" value="{{ old('key', $priceType->key) }}" required
                                           placeholder="e.g., negotiable, final_price">
                                    @error('key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('admin.key_help_text') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="listing_type" class="form-label">{{ __('admin.listing_type') }} <span class="text-danger">*</span></label>
                                    <select class="form-control @error('listing_type') is-invalid @enderror" 
                                            id="listing_type" name="listing_type" required>
                                        <option value="">{{ __('admin.select_listing_type') }}</option>
                                        <option value="rent" {{ old('listing_type', $priceType->listing_type) == 'rent' ? 'selected' : '' }}>
                                            {{ __('admin.rent') }}
                                        </option>
                                        <option value="sale" {{ old('listing_type', $priceType->listing_type) == 'sale' ? 'selected' : '' }}>
                                            {{ __('admin.sale') }}
                                        </option>
                                        <option value="both" {{ old('listing_type', $priceType->listing_type) == 'both' ? 'selected' : '' }}>
                                            {{ __('admin.both') }}
                                        </option>
                                    </select>
                                    @error('listing_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active', $priceType->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            {{ __('admin.is_active') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($priceType->properties()->count() > 0)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                {{ __('admin.price_type_in_use_warning', ['count' => $priceType->properties()->count()]) }}
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('admin.update') }}
                        </button>
                        <a href="{{ route('admin.price-types.index') }}" class="btn btn-secondary">
                            {{ __('admin.cancel') }}
                        </a>
                        <a href="{{ route('admin.price-types.show', $priceType) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> {{ __('admin.view') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection