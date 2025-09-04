@extends('admin.layouts.app')

@section('title', 'عرض المحافظة: ' . $governorate->name_ar)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $governorate->name_ar }}</h1>
        <div>
            <a href="{{ route('admin.governorates.edit', $governorate) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <a href="{{ route('admin.governorates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> العودة للقائمة
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <!-- Basic Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> معلومات المحافظة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">الرقم:</td>
                                    <td>{{ $governorate->id }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">الاسم العربي:</td>
                                    <td>{{ $governorate->name_ar }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">الاسم الإنجليزي:</td>
                                    <td>{{ $governorate->name_en }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">الرمز:</td>
                                    <td><code>{{ $governorate->slug }}</code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">الحالة:</td>
                                    <td>
                                        <span class="badge badge-{{ $governorate->is_active ? 'success' : 'secondary' }} badge-lg">
                                            {{ $governorate->is_active ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">خط العرض:</td>
                                    <td>{{ $governorate->latitude ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">خط الطول:</td>
                                    <td>{{ $governorate->longitude ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">عدد المدن:</td>
                                    <td>
                                        <span class="badge badge-info badge-lg">
                                            {{ $governorate->cities()->count() }} مدينة
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($governorate->latitude && $governorate->longitude)
                        <div class="mt-3">
                            <h6 class="font-weight-bold">الموقع على الخريطة:</h6>
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe class="embed-responsive-item" 
                                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $governorate->longitude - 0.1 }},{{ $governorate->latitude - 0.1 }},{{ $governorate->longitude + 0.1 }},{{ $governorate->latitude + 0.1 }}&layer=mapnik&marker={{ $governorate->latitude }},{{ $governorate->longitude }}"
                                        style="border: 1px solid black">
                                </iframe>
                            </div>
                            <small class="text-muted">
                                <a href="https://www.openstreetmap.org/?mlat={{ $governorate->latitude }}&mlon={{ $governorate->longitude }}#map=10/{{ $governorate->latitude }}/{{ $governorate->longitude }}" target="_blank">
                                    عرض في خريطة أكبر
                                </a>
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cities List Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-city"></i> المدن التابعة ({{ $cities->count() }})
                    </h6>
                    @if($cities->count() > 0)
                        <div>
                            <button class="btn btn-sm btn-outline-success" onclick="toggleAllCities()">
                                <i class="fas fa-eye"></i> عرض/إخفاء الكل
                            </button>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($cities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">الاسم العربي</th>
                                        <th width="25%">الاسم الإنجليزي</th>
                                        <th width="15%">الحالة</th>
                                        <th width="15%">عدد الأحياء</th>
                                        <th width="15%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="citiesTable">
                                    @foreach($cities as $city)
                                        <tr>
                                            <td>{{ $city->id }}</td>
                                            <td>{{ $city->name_ar }}</td>
                                            <td>{{ $city->name_en }}</td>
                                            <td>
                                                <span class="badge badge-{{ $city->is_active ? 'success' : 'secondary' }}">
                                                    {{ $city->is_active ? 'نشط' : 'غير نشط' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $city->neighborhoods()->count() }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.cities.show', $city) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="عرض المدينة">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.cities.edit', $city) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="تعديل المدينة">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($cities->hasPages())
                            <div class="d-flex justify-content-center">
                                {{ $cities->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-city fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد مدن مرتبطة بهذه المحافظة</h5>
                            <p class="text-muted">يمكنك إضافة مدن جديدة من قسم إدارة المدن</p>
                            <a href="{{ route('admin.cities.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة مدينة جديدة
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs"></i> إجراءات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.governorates.edit', $governorate) }}" class="btn btn-warning btn-block">
                            <i class="fas fa-edit"></i> تعديل المحافظة
                        </a>
                        
                        <button type="button" 
                                class="btn btn-{{ $governorate->is_active ? 'secondary' : 'success' }} btn-block"
                                onclick="toggleStatus({{ $governorate->id }})">
                            <i class="fas fa-{{ $governorate->is_active ? 'pause' : 'play' }}"></i>
                            {{ $governorate->is_active ? 'إلغاء التفعيل' : 'تفعيل' }}
                        </button>
                        
                        <a href="{{ route('admin.cities.create', ['governorate_id' => $governorate->id]) }}" 
                           class="btn btn-success btn-block">
                            <i class="fas fa-plus"></i> إضافة مدينة جديدة
                        </a>
                        
                        @if($governorate->cities()->count() == 0)
                            <button type="button" 
                                    class="btn btn-danger btn-block"
                                    onclick="deleteGovernorate({{ $governorate->id }})">
                                <i class="fas fa-trash"></i> حذف المحافظة
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-bar"></i> إحصائيات
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h4 class="text-primary">{{ $governorate->cities()->where('is_active', true)->count() }}</h4>
                                <small class="text-muted">مدن نشطة</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-secondary">{{ $governorate->cities()->where('is_active', false)->count() }}</h4>
                            <small class="text-muted">مدن غير نشطة</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-12">
                            <h4 class="text-success">{{ $governorate->cities()->withCount('neighborhoods')->get()->sum('neighborhoods_count') }}</h4>
                            <small class="text-muted">إجمالي الأحياء</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timestamps Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-clock"></i> التواريخ
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>تاريخ الإنشاء:</strong></td>
                            <td>
                                <small>{{ $governorate->created_at->format('Y-m-d H:i') }}</small><br>
                                <small class="text-muted">{{ $governorate->created_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>آخر تحديث:</strong></td>
                            <td>
                                <small>{{ $governorate->updated_at->format('Y-m-d H:i') }}</small><br>
                                <small class="text-muted">{{ $governorate->updated_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.75em;
}

.border-right {
    border-right: 1px solid #e3e6f0;
}

.table th {
    border-top: none;
}
</style>
@endpush

@push('scripts')
<script>
function toggleAllCities() {
    const table = document.getElementById('citiesTable');
    const isVisible = table.style.display !== 'none';
    table.style.display = isVisible ? 'none' : '';
}

function toggleStatus(governorateId) {
    Swal.fire({
        title: 'تأكيد العملية',
        text: 'هل أنت متأكد من تغيير حالة المحافظة؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'نعم، تأكيد',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/governorates/${governorateId}/toggle-status`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'تم بنجاح',
                            text: response.message
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'حدث خطأ أثناء تحديث الحالة'
                    });
                }
            });
        }
    });
}

function deleteGovernorate(governorateId) {
    Swal.fire({
        title: 'تأكيد الحذف',
        text: 'هل أنت متأكد من حذف هذه المحافظة؟ لا يمكن التراجع عن هذا الإجراء!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/governorates/${governorateId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush