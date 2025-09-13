@extends('admin.layouts.app')

@section('title', __('admin.view_price_type'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.view_price_type') }}: {{ $priceType->getLocalizedName() }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.price-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('admin.back') }}
                        </a>
                        <a href="{{ route('admin.price-types.edit', $priceType) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> {{ __('admin.edit') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">{{ __('admin.id') }}</th>
                                    <td>{{ $priceType->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('admin.name_ar') }}</th>
                                    <td>{{ $priceType->name_ar }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('admin.name_en') }}</th>
                                    <td>{{ $priceType->name_en }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('admin.name_ku') }}</th>
                                    <td>{{ $priceType->name_ku }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('admin.key') }}</th>
                                    <td><code>{{ $priceType->key }}</code></td>
                                </tr>
                                <tr>
                                    <th>{{ __('admin.listing_type') }}</th>
                                    <td>
                                        @if($priceType->listing_type == 'rent')
                                            <span class="badge badge-info">{{ __('admin.rent') }}</span>
                                        @elseif($priceType->listing_type == 'sale')
                                            <span class="badge badge-success">{{ __('admin.sale') }}</span>
                                        @else
                                            <span class="badge badge-primary">{{ __('admin.both') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('admin.status') }}</th>
                                    <td>
                                        @if($priceType->is_active)
                                            <span class="badge badge-success">{{ __('admin.active') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __('admin.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('admin.created_at') }}</th>
                                    <td>{{ $priceType->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('admin.updated_at') }}</th>
                                    <td>{{ $priceType->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">{{ __('admin.usage_statistics') }}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-home"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ __('admin.properties_using_this_type') }}</span>
                                            <span class="info-box-number">{{ $priceType->properties()->count() }}</span>
                                        </div>
                                    </div>
                                    
                                    @if($priceType->properties()->count() > 0)
                                        <div class="mt-3">
                                            <h6>{{ __('admin.recent_properties') }}:</h6>
                                            <ul class="list-group list-group-flush">
                                                @foreach($priceType->properties()->latest()->limit(5)->get() as $property)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $property->title }}</strong><br>
                                                            <small class="text-muted">{{ $property->created_at->diffForHumans() }}</small>
                                                        </div>
                                                        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-sm btn-outline-primary">
                                                            {{ __('admin.view') }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if($priceType->properties()->count() > 5)
                                                <div class="text-center mt-2">
                                                    <small class="text-muted">
                                                        {{ __('admin.and_more_properties', ['count' => $priceType->properties()->count() - 5]) }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.price-types.edit', $priceType) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> {{ __('admin.edit') }}
                    </a>
                    <a href="{{ route('admin.price-types.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> {{ __('admin.back_to_list') }}
                    </a>
                    @if($priceType->properties()->count() == 0)
                        <form action="{{ route('admin.price-types.destroy', $priceType) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-danger" disabled title="{{ __('admin.cannot_delete_in_use') }}">
                            <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection