@extends('admin.layouts.app')

@section('title', 'تعديل المحافظة: ' . $governorate->name_ar)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">تعديل المحافظة: {{ $governorate->name_ar }}</h1>
        <div>
            <a href="{{ route('admin.governorates.show', $governorate) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> عرض
            </a>
            <a href="{{ route('admin.governorates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> العودة للقائمة
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">تعديل معلومات المحافظة</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.governorates.update', $governorate) }}" method="POST" id="governorateForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Arabic Name -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name_ar" class="required">الاسم باللغة العربية</label>
                                    <input type="text" 
                                           class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" 
                                           name="name_ar" 
                                           value="{{ old('name_ar', $governorate->name_ar) }}"
                                           placeholder="أدخل اسم المحافظة بالعربية"
                                           required>
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- English Name -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name_en" class="required">الاسم باللغة الإنجليزية</label>
                                    <input type="text" 
                                           class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" 
                                           name="name_en" 
                                           value="{{ old('name_en', $governorate->name_en) }}"
                                           placeholder="Enter governorate name in English"
                                           required>
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        الرمز الحالي: <code>{{ $governorate->slug }}</code>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Kurdish Name -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name_ku">الاسم باللغة الكردية</label>
                                    <input type="text" 
                                           class="form-control @error('name_ku') is-invalid @enderror" 
                                           id="name_ku" 
                                           name="name_ku" 
                                           value="{{ old('name_ku', $governorate->name_ku) }}"
                                           placeholder="ناوی پارێزگاکە بە کوردی بنووسە">
                                    @error('name_ku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        اختياري - للدعم متعدد اللغات
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Latitude -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">خط العرض</label>
                                    <input type="number" 
                                           class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" 
                                           name="latitude" 
                                           value="{{ old('latitude', $governorate->latitude) }}"
                                           step="0.000001"
                                           min="-90"
                                           max="90"
                                           placeholder="مثال: 33.5138">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        قيمة اختيارية بين -90 و 90
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Longitude -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">خط الطول</label>
                                    <input type="number" 
                                           class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" 
                                           name="longitude" 
                                           value="{{ old('longitude', $governorate->longitude) }}"
                                           step="0.000001"
                                           min="-180"
                                           max="180"
                                           placeholder="مثال: 36.2765">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        قيمة اختيارية بين -180 و 180
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $governorate->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    تفعيل المحافظة
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                المحافظات المفعلة فقط ستظهر في القوائم المنسدلة
                            </small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ التغييرات
                            </button>
                            <a href="{{ route('admin.governorates.show', $governorate) }}" class="btn btn-info ml-2">
                                <i class="fas fa-eye"></i> عرض
                            </a>
                            <a href="{{ route('admin.governorates.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Current Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle"></i> المعلومات الحالية
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>الرقم:</strong></td>
                            <td>{{ $governorate->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>الرمز:</strong></td>
                            <td><code>{{ $governorate->slug }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>الحالة:</strong></td>
                            <td>
                                <span class="badge badge-{{ $governorate->is_active ? 'success' : 'secondary' }}">
                                    {{ $governorate->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>عدد المدن:</strong></td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $governorate->cities()->count() }} مدينة
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>تاريخ الإنشاء:</strong></td>
                            <td>
                                <small>{{ $governorate->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>آخر تحديث:</strong></td>
                            <td>
                                <small>{{ $governorate->updated_at->format('Y-m-d H:i') }}</small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle"></i> تنبيهات مهمة
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-info text-info"></i>
                            تغيير الاسم الإنجليزي سيؤدي لتحديث الرمز
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-warning text-warning"></i>
                            إلغاء التفعيل سيخفي المحافظة من القوائم
                        </li>
                        @if($governorate->cities()->count() > 0)
                            <li class="mb-2">
                                <i class="fas fa-lock text-danger"></i>
                                لا يمكن حذف المحافظة لوجود مدن مرتبطة
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: ' *';
    color: #e74c3c;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const originalNameEn = '{{ $governorate->name_en }}';
    
    // Show slug preview when English name changes
    $('#name_en').on('input', function() {
        const englishName = $(this).val();
        
        if (englishName !== originalNameEn) {
            const slug = englishName.toLowerCase()
                                    .replace(/[^a-z0-9\s-]/g, '')
                                    .replace(/\s+/g, '-')
                                    .replace(/-+/g, '-')
                                    .trim('-');
            
            if (slug) {
                if (!$('#slug-preview').length) {
                    $(this).parent().find('.form-text').after('<small id="slug-preview" class="form-text text-warning"></small>');
                }
                $('#slug-preview').text('الرمز الجديد: ' + slug);
            } else {
                $('#slug-preview').remove();
            }
        } else {
            $('#slug-preview').remove();
        }
    });

    // Form validation
    $('#governorateForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const nameAr = $('#name_ar').val().trim();
        const nameEn = $('#name_en').val().trim();
        
        if (!nameAr) {
            $('#name_ar').addClass('is-invalid');
            isValid = false;
        } else {
            $('#name_ar').removeClass('is-invalid');
        }
        
        if (!nameEn) {
            $('#name_en').addClass('is-invalid');
            isValid = false;
        } else {
            $('#name_en').removeClass('is-invalid');
        }
        
        // Check coordinates if provided
        const latitude = $('#latitude').val();
        const longitude = $('#longitude').val();
        
        if (latitude && (latitude < -90 || latitude > 90)) {
            $('#latitude').addClass('is-invalid');
            isValid = false;
        } else {
            $('#latitude').removeClass('is-invalid');
        }
        
        if (longitude && (longitude < -180 || longitude > 180)) {
            $('#longitude').addClass('is-invalid');
            isValid = false;
        } else {
            $('#longitude').removeClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'خطأ في البيانات',
                text: 'يرجى التحقق من البيانات المدخلة وإصلاح الأخطاء'
            });
        }
    });
});
</script>
@endpush