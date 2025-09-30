@extends('admin.layouts.app')

@section('title', __('admin.add_currency'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.add_currency') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('admin.back') }}
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.currencies.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label">{{ __('admin.currency_code') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code') }}" required
                                           placeholder="USD, EUR, TRY" maxlength="3" style="text-transform: uppercase">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('admin.currency_code_help') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_ar" class="form-label">{{ __('admin.name_ar') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                           id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required
                                           placeholder="الدولار الأمريكي">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_en" class="form-label">{{ __('admin.name_en') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                           id="name_en" name="name_en" value="{{ old('name_en') }}" required
                                           placeholder="US Dollar">
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name_ku" class="form-label">{{ __('admin.name_ku') }}</label>
                                    <input type="text" class="form-control @error('name_ku') is-invalid @enderror"
                                           id="name_ku" name="name_ku" value="{{ old('name_ku') }}"
                                           placeholder="دۆلاری ئەمریکی">
                                    @error('name_ku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="sort_order" class="form-label">{{ __('admin.sort_order') }}</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('admin.sort_order_help') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active"
                                               name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            {{ __('admin.is_active') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('admin.save') }}
                        </button>
                        <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary">
                            {{ __('admin.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-uppercase currency code
    $('#code').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
});
</script>
@endpush
@endsection