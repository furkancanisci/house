@extends('admin.layouts.app')

@section('title', __('admin.building_types'))

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin.building_types') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.building_types') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('admin.manage') }} {{ __('admin.building_types') }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.building-types.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> {{ __('admin.add_new') }}
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    {{ session('error') }}
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('admin.id') }}</th>
                                            <th>{{ __('admin.name_en') }}</th>
                                            <th>{{ __('admin.name_ar') }}</th>
                                            <th>{{ __('admin.value') }}</th>
                                            <th>{{ __('admin.status') }}</th>
                                            <th>{{ __('admin.properties_count') }}</th>
                                            <th>{{ __('admin.created_at') }}</th>
                                            <th>{{ __('admin.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($buildingTypes as $buildingType)
                                            <tr>
                                                <td>{{ $buildingType->id }}</td>
                                                <td>{{ $buildingType->name_en }}</td>
                                                <td>{{ $buildingType->name_ar }}</td>
                                                <td><code>{{ $buildingType->value }}</code></td>
                                                <td>
                                                    <span class="badge badge-{{ $buildingType->is_active ? 'success' : 'danger' }}">
                                                        {{ $buildingType->is_active ? __('admin.active') : __('admin.inactive') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $buildingType->properties_count ?? 0 }}</span>
                                                </td>
                                                <td>{{ $buildingType->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.building-types.show', $buildingType) }}" class="btn btn-info btn-sm" title="{{ __('admin.view') }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.building-types.edit', $buildingType) }}" class="btn btn-warning btn-sm" title="{{ __('admin.edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.building-types.toggle-status', $buildingType) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-{{ $buildingType->is_active ? 'secondary' : 'success' }} btn-sm" title="{{ $buildingType->is_active ? __('admin.deactivate') : __('admin.activate') }}">
                                                                <i class="fas fa-{{ $buildingType->is_active ? 'ban' : 'check' }}"></i>
                                                            </button>
                                                        </form>
                                                        @if($buildingType->properties_count == 0)
                                                            <form action="{{ route('admin.building-types.destroy', $buildingType) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm" title="{{ __('admin.delete') }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">{{ __('admin.no_data_found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($buildingTypes->hasPages())
                                <div class="d-flex justify-content-center">
                                    {{ $buildingTypes->links() }}
                                </div>
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
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush