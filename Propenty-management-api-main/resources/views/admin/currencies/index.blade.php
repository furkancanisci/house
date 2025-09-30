@extends('admin.layouts.app')

@section('title', __('admin.currencies'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('admin.currencies') }}</h3>
                    <a href="{{ route('admin.currencies.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('admin.add_currency') }}
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.id') }}</th>
                                    <th>{{ __('admin.code') }}</th>
                                    <th>{{ __('admin.name_ar') }}</th>
                                    <th>{{ __('admin.name_en') }}</th>
                                    <th>{{ __('admin.name_ku') }}</th>
                                    <th>{{ __('admin.sort_order') }}</th>
                                    <th>{{ __('admin.status') }}</th>
                                    <th>{{ __('admin.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($currencies as $currency)
                                    <tr>
                                        <td>{{ $currency->id }}</td>
                                        <td><code>{{ $currency->code }}</code></td>
                                        <td>{{ $currency->name_ar }}</td>
                                        <td>{{ $currency->name_en }}</td>
                                        <td>{{ $currency->name_ku ?? '-' }}</td>
                                        <td>{{ $currency->sort_order }}</td>
                                        <td>
                                            <form action="{{ route('admin.currencies.toggle-status', $currency) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $currency->is_active ? 'btn-success' : 'btn-danger' }}">
                                                    @if($currency->is_active)
                                                        <i class="fas fa-check"></i> {{ __('admin.active') }}
                                                    @else
                                                        <i class="fas fa-times"></i> {{ __('admin.inactive') }}
                                                    @endif
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.currencies.edit', $currency) }}"
                                                   class="btn btn-sm btn-warning" title="{{ __('admin.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.currencies.destroy', $currency) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="{{ __('admin.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ __('admin.no_currencies_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection