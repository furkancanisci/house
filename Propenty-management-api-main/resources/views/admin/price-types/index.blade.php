@extends('admin.layouts.app')

@section('title', __('admin.price_types'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('admin.price_types') }}</h3>
                    <a href="{{ route('admin.price-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('admin.add_price_type') }}
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
                                    <th>{{ __('admin.name_ar') }}</th>
                                    <th>{{ __('admin.name_en') }}</th>
                                    <th>{{ __('admin.name_ku') }}</th>
                                    <th>{{ __('admin.key') }}</th>
                                    <th>{{ __('admin.listing_type') }}</th>
                                    <th>{{ __('admin.status') }}</th>
                                    <th>{{ __('admin.created_at') }}</th>
                                    <th>{{ __('admin.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($priceTypes as $priceType)
                                    <tr>
                                        <td>{{ $priceType->id }}</td>
                                        <td>{{ $priceType->name_ar }}</td>
                                        <td>{{ $priceType->name_en }}</td>
                                        <td>{{ $priceType->name_ku }}</td>
                                        <td><code>{{ $priceType->key }}</code></td>
                                        <td>
                                            @if($priceType->listing_type == 'rent')
                                                <span class="badge bg-info">{{ __('admin.rent') }}</span>
                                            @elseif($priceType->listing_type == 'sale')
                                                <span class="badge bg-success">{{ __('admin.sale') }}</span>
                                            @else
                                                <span class="badge bg-primary">{{ __('admin.both') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($priceType->is_active)
                                                <span class="badge bg-success">{{ __('admin.active') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('admin.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $priceType->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.price-types.show', $priceType) }}" 
                                                   class="btn btn-sm btn-info" title="{{ __('admin.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.price-types.edit', $priceType) }}" 
                                                   class="btn btn-sm btn-warning" title="{{ __('admin.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.price-types.destroy', $priceType) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('{{ __('admin.confirm_delete') }}')"
                                                      class="delete-form">
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
                                        <td colspan="9" class="text-center">{{ __('admin.no_price_types_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($priceTypes->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $priceTypes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection