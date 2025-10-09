@extends('admin.layouts.app')

@section('title', __('admin.currencies'))

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin.currencies') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.currencies') }}</li>
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
                            <h3 class="card-title">{{ __('admin.manage') }} {{ __('admin.currencies') }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.currencies.create') }}" class="btn btn-primary btn-sm">
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
                                            <th>{{ __('admin.code') }}</th>
                                            <th>{{ __('admin.symbol') }}</th>
                                            <th>{{ __('admin.name_en') }}</th>
                                            <th>{{ __('admin.name_ar') }}</th>
                                            <th>{{ __('admin.name_ku') }}</th>
                                            <th>{{ __('admin.status') }}</th>
                                            <th>{{ __('admin.sort_order') }}</th>
                                            <th>{{ __('admin.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($currencies as $currency)
                                            <tr>
                                                <td>{{ $currency->id }}</td>
                                                <td><code>{{ $currency->code }}</code></td>
                                                <td>{{ $currency->symbol }}</td>
                                                <td>{{ $currency->name_en }}</td>
                                                <td>{{ $currency->name_ar }}</td>
                                                <td>{{ $currency->name_ku ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $currency->is_active ? 'success' : 'danger' }}">
                                                        {{ $currency->is_active ? __('admin.active') : __('admin.inactive') }}
                                                    </span>
                                                </td>
                                                <td>{{ $currency->sort_order }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.currencies.show', $currency) }}" class="btn btn-info btn-sm" title="{{ __('admin.view') }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.currencies.edit', $currency) }}" class="btn btn-warning btn-sm" title="{{ __('admin.edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.currencies.toggle-status', $currency) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-{{ $currency->is_active ? 'secondary' : 'success' }} btn-sm" title="{{ $currency->is_active ? __('admin.deactivate') : __('admin.activate') }}">
                                                                <i class="fas fa-{{ $currency->is_active ? 'ban' : 'check' }}"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.currencies.destroy', $currency) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" title="{{ __('admin.delete') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">{{ __('admin.no_data_found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($currencies->hasPages())
                                <div class="d-flex justify-content-center">
                                    {{ $currencies->links() }}
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
