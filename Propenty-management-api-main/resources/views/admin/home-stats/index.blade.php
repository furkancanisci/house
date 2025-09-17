@extends('admin.layouts.app')

@section('title', 'إدارة إحصائيات الصفحة الرئيسية')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">إدارة إحصائيات الصفحة الرئيسية</h1>
        <a href="{{ route('admin.home-stats.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> إضافة إحصائية جديدة
        </a>
    </div>

    <!-- Alerts are handled by the layout -->

    <!-- Statistics Cards -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">قائمة الإحصائيات</h6>
        </div>
        <div class="card-body">
            @if($stats->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>الترتيب</th>
                                <th>المفتاح</th>
                                <th>الأيقونة</th>
                                <th>القيمة</th>
                                <th>التسمية (عربي)</th>
                                <th>التسمية (إنجليزي)</th>
                                <th>التسمية (كردي)</th>
                                <th>اللون</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats as $stat)
                                <tr>
                                    <td>{{ $stat->order }}</td>
                                    <td>
                                        <code>{{ $stat->key }}</code>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $stat->icon }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $stat->number }}</strong>
                                        @if($stat->key === 'properties_listed')
                                            <br><small class="text-muted">(ديناميكي - يتم حسابه من العقارات النشطة)</small>
                                        @endif
                                    </td>
                                    <td>{{ $stat->label_ar }}</td>
                                    <td>{{ $stat->label_en }}</td>
                                    <td>{{ $stat->label_ku }}</td>
                                    <td>
                                        <span class="text-muted">{{ $stat->color }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.home-stats.toggle-status', $stat) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $stat->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                <i class="fas {{ $stat->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                                {{ $stat->is_active ? 'نشط' : 'غير نشط' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.home-stats.edit', $stat) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            <form method="POST" action="{{ route('admin.home-stats.destroy', $stat) }}" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الإحصائية؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد إحصائيات</h5>
                    <p class="text-muted">يمكنك إضافة إحصائية جديدة باستخدام الزر أعلاه</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Preview Section -->
    @if($stats->where('is_active', true)->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">معاينة الإحصائيات النشطة</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($stats->where('is_active', true)->sortBy('order') as $stat)
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                {{ $stat->label_ar }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $stat->number }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-home fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<!-- DataTables -->
<script src="{{ asset('admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json"
        },
        "order": [[ 0, "asc" ]]
    });
});
</script>
@endsection