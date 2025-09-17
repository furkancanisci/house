@extends('admin.layouts.app')

@section('title', 'إضافة إحصائية جديدة')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">إضافة إحصائية جديدة</h1>
        <a href="{{ route('admin.home-stats.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> العودة للقائمة
        </a>
    </div>

    <!-- Alerts are handled by the layout -->

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">بيانات الإحصائية</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.home-stats.store') }}">
                        @csrf

                        <div class="row">
                            <!-- Key -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="key">المفتاح <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('key') is-invalid @enderror"
                                           id="key"
                                           name="key"
                                           value="{{ old('key') }}"
                                           placeholder="properties_listed"
                                           required>
                                    <small class="form-text text-muted">مفتاح فريد لتحديد الإحصائية (بالإنجليزية فقط)</small>
                                    @error('key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Icon -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="icon">اسم الأيقونة <span class="text-danger">*</span></label>
                                    <select class="form-control @error('icon') is-invalid @enderror"
                                            id="icon"
                                            name="icon"
                                            required>
                                        <option value="">اختر الأيقونة</option>
                                        <option value="HomeIcon" {{ old('icon') == 'HomeIcon' ? 'selected' : '' }}>HomeIcon (منزل)</option>
                                        <option value="Users" {{ old('icon') == 'Users' ? 'selected' : '' }}>Users (مستخدمين)</option>
                                        <option value="TrendingUp" {{ old('icon') == 'TrendingUp' ? 'selected' : '' }}>TrendingUp (ارتفاع)</option>
                                        <option value="Award" {{ old('icon') == 'Award' ? 'selected' : '' }}>Award (جائزة)</option>
                                        <option value="Star" {{ old('icon') == 'Star' ? 'selected' : '' }}>Star (نجمة)</option>
                                        <option value="Building" {{ old('icon') == 'Building' ? 'selected' : '' }}>Building (مبنى)</option>
                                        <option value="MapPin" {{ old('icon') == 'MapPin' ? 'selected' : '' }}>MapPin (موقع)</option>
                                        <option value="Heart" {{ old('icon') == 'Heart' ? 'selected' : '' }}>Heart (قلب)</option>
                                    </select>
                                    <small class="form-text text-muted">اختر أيقونة من مكتبة Lucide</small>
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Number -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="number">القيمة المعروضة <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('number') is-invalid @enderror"
                                           id="number"
                                           name="number"
                                           value="{{ old('number') }}"
                                           placeholder="1000+"
                                           required>
                                    <small class="form-text text-muted">القيمة التي ستظهر (أرقام، نص، رموز)</small>
                                    @error('number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Order -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="order">ترتيب العرض</label>
                                    <input type="number"
                                           class="form-control @error('order') is-invalid @enderror"
                                           id="order"
                                           name="order"
                                           value="{{ old('order', 0) }}"
                                           min="0">
                                    <small class="form-text text-muted">ترتيب ظهور الإحصائية (الأقل أولاً)</small>
                                    @error('order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Labels -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="label_ar">التسمية بالعربية <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('label_ar') is-invalid @enderror"
                                           id="label_ar"
                                           name="label_ar"
                                           value="{{ old('label_ar') }}"
                                           placeholder="عقارات مدرجة"
                                           required>
                                    @error('label_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="label_en">التسمية بالإنجليزية <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('label_en') is-invalid @enderror"
                                           id="label_en"
                                           name="label_en"
                                           value="{{ old('label_en') }}"
                                           placeholder="Properties Listed"
                                           required>
                                    @error('label_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="label_ku">التسمية بالكردية <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('label_ku') is-invalid @enderror"
                                           id="label_ku"
                                           name="label_ku"
                                           value="{{ old('label_ku') }}"
                                           placeholder="Xanî Lîstekirî"
                                           required>
                                    @error('label_ku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Color -->
                        <div class="form-group">
                            <label for="color">لون النص (Tailwind CSS)</label>
                            <select class="form-control @error('color') is-invalid @enderror"
                                    id="color"
                                    name="color">
                                <option value="text-primary-600" {{ old('color') == 'text-primary-600' ? 'selected' : '' }}>Primary 600</option>
                                <option value="text-primary-700" {{ old('color') == 'text-primary-700' ? 'selected' : '' }}>Primary 700</option>
                                <option value="text-primary-800" {{ old('color') == 'text-primary-800' ? 'selected' : '' }}>Primary 800</option>
                                <option value="text-primary-500" {{ old('color') == 'text-primary-500' ? 'selected' : '' }}>Primary 500</option>
                                <option value="text-blue-600" {{ old('color') == 'text-blue-600' ? 'selected' : '' }}>Blue 600</option>
                                <option value="text-green-600" {{ old('color') == 'text-green-600' ? 'selected' : '' }}>Green 600</option>
                                <option value="text-red-600" {{ old('color') == 'text-red-600' ? 'selected' : '' }}>Red 600</option>
                                <option value="text-purple-600" {{ old('color') == 'text-purple-600' ? 'selected' : '' }}>Purple 600</option>
                            </select>
                            <small class="form-text text-muted">لون النص المستخدم في الواجهة الأمامية</small>
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="is_active"
                                       name="is_active"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    تفعيل الإحصائية
                                </label>
                            </div>
                            <small class="form-text text-muted">الإحصائيات غير المفعلة لن تظهر في الموقع</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> حفظ الإحصائية
                            </button>
                            <a href="{{ route('admin.home-stats.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">الأيقونات المتاحة</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">يمكنك استخدام الأيقونات التالية من مكتبة Lucide:</p>
                    <ul class="list-unstyled">
                        <li><strong>HomeIcon:</strong> أيقونة منزل</li>
                        <li><strong>Users:</strong> أيقونة المستخدمين</li>
                        <li><strong>TrendingUp:</strong> أيقونة الارتفاع</li>
                        <li><strong>Award:</strong> أيقونة الجائزة</li>
                        <li><strong>Star:</strong> أيقونة النجمة</li>
                        <li><strong>Building:</strong> أيقونة المبنى</li>
                        <li><strong>MapPin:</strong> أيقونة الموقع</li>
                        <li><strong>Heart:</strong> أيقونة القلب</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection