@extends('admin.layouts.app')

@section('title', 'إدارة المحافظات')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">إدارة المحافظات</h1>
        <a href="{{ route('admin.governorates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> إضافة محافظة جديدة
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">البحث والفلترة</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">البحث</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="البحث في الاسم أو الرمز">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">الحالة</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">جميع الحالات</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sort_by">ترتيب حسب</label>
                            <select class="form-control" id="sort_by" name="sort_by">
                                <option value="name_ar" {{ request('sort_by') == 'name_ar' ? 'selected' : '' }}>الاسم العربي</option>
                                <option value="name_en" {{ request('sort_by') == 'name_en' ? 'selected' : '' }}>الاسم الإنجليزي</option>
                                <option value="name_ku" {{ request('sort_by') == 'name_ku' ? 'selected' : '' }}>الاسم الكردي</option>
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>تاريخ الإنشاء</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="sort_order">نوع الترتيب</label>
                            <select class="form-control" id="sort_order" name="sort_order">
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                قائمة المحافظات ({{ $governorates->total() }} محافظة)
            </h6>
        </div>
        <div class="card-body">
            <div id="governorates-table">
                @include('admin.governorates.partials.table')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#filterForm select, #filterForm input').on('change keyup', function() {
        if ($(this).attr('type') === 'text') {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(function() {
                $('#filterForm').submit();
            }, 500);
        } else {
            $('#filterForm').submit();
        }
    });

    // AJAX form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("admin.governorates.index") }}',
            method: 'GET',
            data: $(this).serialize(),
            success: function(response) {
                $('#governorates-table').html(response);
            },
            error: function() {
                Swal.fire('خطأ!', 'حدث خطأ أثناء تحميل البيانات', 'error');
            }
        });
    });

    // Toggle status
    $(document).on('click', '.toggle-status', function() {
        const governorateId = $(this).data('id');
        const button = $(this);
        
        $.ajax({
            url: `/admin/governorates/${governorateId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if (response.is_active) {
                        button.removeClass('btn-secondary').addClass('btn-success')
                              .html('<i class="fas fa-check"></i> نشط');
                    } else {
                        button.removeClass('btn-success').addClass('btn-secondary')
                              .html('<i class="fas fa-times"></i> غير نشط');
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'تم!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function() {
                Swal.fire('خطأ!', 'حدث خطأ أثناء تحديث الحالة', 'error');
            }
        });
    });

    // Delete governorate
    $(document).on('click', '.delete-governorate', function() {
        const governorateId = $(this).data('id');
        const governorateName = $(this).data('name');
        
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: `سيتم حذف المحافظة "${governorateName}" نهائياً`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذف!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/governorates/${governorateId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('تم الحذف!', response.message, 'success');
                            $('#filterForm').submit(); // Reload table
                        } else {
                            Swal.fire('خطأ!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('خطأ!', 'حدث خطأ أثناء الحذف', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush