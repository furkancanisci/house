@extends('admin.layouts.app')

@section('title', __('Window Types Management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Window Types Management') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.window-types.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> {{ __('Add New Window Type') }}
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status_filter">{{ __('Status') }}</label>
                                <select class="form-control" id="status_filter">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="search">{{ __('Search') }}</label>
                                <input type="text" class="form-control" id="search" placeholder="{{ __('Search by name...') }}" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                        <i class="fas fa-search"></i> {{ __('Search') }}
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                        <i class="fas fa-times"></i> {{ __('Clear') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Name (English)') }}</th>
                                    <th>{{ __('Name (Arabic)') }}</th>
                                    <th>{{ __('Sort Order') }}</th>
                                    <th>{{ __('Properties Count') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Created') }}</th>
                                    <th width="150">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($windowTypes as $windowType)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-item" value="{{ $windowType->id }}">
                                    </td>
                                    <td>{{ $windowType->id }}</td>
                                    <td>{{ $windowType->name_en }}</td>
                                    <td dir="rtl">{{ $windowType->name_ar }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $windowType->sort_order }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $windowType->properties_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.window-types.toggle-status', $windowType) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-{{ $windowType->is_active ? 'success' : 'danger' }}" 
                                                    title="{{ $windowType->is_active ? __('Active - Click to Deactivate') : __('Inactive - Click to Activate') }}">
                                                <i class="fas fa-{{ $windowType->is_active ? 'check' : 'times' }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>{{ $windowType->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.window-types.show', $windowType) }}" class="btn btn-info btn-sm" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.window-types.edit', $windowType) }}" class="btn btn-primary btn-sm" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $windowType->id }})" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">{{ __('No window types found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($windowTypes->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $windowTypes->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_action">{{ __('Bulk Actions') }}</label>
                                <div class="input-group">
                                    <select class="form-control" id="bulk_action">
                                        <option value="">{{ __('Select Action') }}</option>
                                        <option value="activate">{{ __('Activate Selected') }}</option>
                                        <option value="deactivate">{{ __('Deactivate Selected') }}</option>
                                        <option value="delete">{{ __('Delete Selected') }}</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" onclick="executeBulkAction()">
                                            {{ __('Execute') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <p class="text-muted">
                                {{ __('Showing') }} {{ $windowTypes->firstItem() ?? 0 }} {{ __('to') }} {{ $windowTypes->lastItem() ?? 0 }} 
                                {{ __('of') }} {{ $windowTypes->total() }} {{ __('results') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Forms -->
@foreach($windowTypes as $windowType)
<form id="delete-form-{{ $windowType->id }}" action="{{ route('admin.window-types.destroy', $windowType) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endsection

@push('scripts')
<script>
// Select all functionality
$('#select-all').on('change', function() {
    $('.select-item').prop('checked', this.checked);
});

$('.select-item').on('change', function() {
    if (!this.checked) {
        $('#select-all').prop('checked', false);
    }
});

// Filter functions
function applyFilters() {
    const status = $('#status_filter').val();
    const search = $('#search').val();
    
    let url = new URL(window.location.href);
    url.searchParams.set('status', status);
    url.searchParams.set('search', search);
    url.searchParams.delete('page'); // Reset pagination
    
    window.location.href = url.toString();
}

function clearFilters() {
    let url = new URL(window.location.href);
    url.searchParams.delete('status');
    url.searchParams.delete('search');
    url.searchParams.delete('page');
    
    window.location.href = url.toString();
}

// Delete confirmation
function confirmDelete(id) {
    Swal.fire({
        title: '{{ __('Are you sure?') }}',
        text: '{{ __('This action cannot be undone!') }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '{{ __('Yes, delete it!') }}',
        cancelButtonText: '{{ __('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}

// Bulk actions
function executeBulkAction() {
    const action = $('#bulk_action').val();
    const selected = $('.select-item:checked').map(function() {
        return this.value;
    }).get();
    
    if (!action) {
        toastr.warning('{{ __('Please select an action') }}');
        return;
    }
    
    if (selected.length === 0) {
        toastr.warning('{{ __('Please select at least one item') }}');
        return;
    }
    
    let confirmText = '';
    switch(action) {
        case 'activate':
            confirmText = '{{ __('Are you sure you want to activate selected window types?') }}';
            break;
        case 'deactivate':
            confirmText = '{{ __('Are you sure you want to deactivate selected window types?') }}';
            break;
        case 'delete':
            confirmText = '{{ __('Are you sure you want to delete selected window types? This action cannot be undone!') }}';
            break;
    }
    
    Swal.fire({
        title: '{{ __('Confirm Action') }}',
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '{{ __('Yes, proceed!') }}',
        cancelButtonText: '{{ __('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form for bulk action
            const form = $('<form>', {
                method: 'POST',
                action: '{{ route('admin.window-types.index') }}/bulk-action'
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: '{{ csrf_token() }}'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: action
            }));
            
            selected.forEach(function(id) {
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'ids[]',
                    value: id
                }));
            });
            
            $('body').append(form);
            form.submit();
        }
    });
}

// Enter key search
$('#search').on('keypress', function(e) {
    if (e.which === 13) {
        applyFilters();
    }
});
</script>
@endpush