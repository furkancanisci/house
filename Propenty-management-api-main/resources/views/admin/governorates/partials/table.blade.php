<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th width="5%">#</th>
                <th width="15%">الاسم العربي</th>
                <th width="15%">الاسم الإنجليزي</th>
                <th width="15%">الاسم الكردي</th>
                <th width="10%">الرمز</th>
                <th width="10%">عدد المدن</th>
                <th width="10%">الحالة</th>
                <th width="10%">تاريخ الإنشاء</th>
                <th width="10%">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($governorates as $governorate)
                <tr>
                    <td>{{ $governorate->id }}</td>
                    <td>
                        <strong>{{ $governorate->name_ar }}</strong>
                        @if($governorate->latitude && $governorate->longitude)
                            <br><small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> 
                                {{ number_format($governorate->latitude, 4) }}, {{ number_format($governorate->longitude, 4) }}
                            </small>
                        @endif
                    </td>
                    <td>{{ $governorate->name_en }}</td>
                    <td>
                        @if($governorate->name_ku)
                            {{ $governorate->name_ku }}
                        @else
                            <span class="text-muted">غير محدد</span>
                        @endif
                    </td>
                    <td>
                        <code>{{ $governorate->slug }}</code>
                    </td>
                    <td>
                        <span class="badge badge-info">
                            {{ $governorate->cities_count }} مدينة
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm toggle-status {{ $governorate->is_active ? 'btn-success' : 'btn-secondary' }}" 
                                data-id="{{ $governorate->id }}">
                            @if($governorate->is_active)
                                <i class="fas fa-check"></i> نشط
                            @else
                                <i class="fas fa-times"></i> غير نشط
                            @endif
                        </button>
                    </td>
                    <td>
                        <small>{{ $governorate->created_at->format('Y-m-d') }}</small>
                        <br>
                        <small class="text-muted">{{ $governorate->created_at->diffForHumans() }}</small>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <!-- View -->
                            <a href="{{ route('admin.governorates.show', $governorate) }}" 
                               class="btn btn-sm btn-info" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <!-- Edit -->
                            <a href="{{ route('admin.governorates.edit', $governorate) }}" 
                               class="btn btn-sm btn-warning" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <!-- Delete -->
                            @if($governorate->cities_count == 0)
                                <button class="btn btn-sm btn-danger delete-governorate" 
                                        data-id="{{ $governorate->id }}"
                                        data-name="{{ $governorate->name_ar }}"
                                        title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-secondary" 
                                        title="لا يمكن الحذف - يحتوي على مدن"
                                        disabled>
                                    <i class="fas fa-lock"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h5>لا توجد محافظات</h5>
                            <p>لم يتم العثور على أي محافظات تطابق معايير البحث</p>
                            <a href="{{ route('admin.governorates.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إضافة محافظة جديدة
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($governorates->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            عرض {{ $governorates->firstItem() }} إلى {{ $governorates->lastItem() }} 
            من أصل {{ $governorates->total() }} محافظة
        </div>
        <div>
            {{ $governorates->appends(request()->query())->links() }}
        </div>
    </div>