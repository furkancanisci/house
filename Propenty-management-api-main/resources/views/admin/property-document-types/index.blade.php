@extends('admin.layouts.app')

@section('title', 'Property Document Types')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Property Document Types</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Property Document Types</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Property Document Types List</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.property-document-types.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Add New Document Type
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.property-document-types.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="is_active" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('admin.property-document-types.index') }}" class="btn btn-default">Clear</a>
                                </div>
                            </div>
                        </form>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="icheck-primary d-inline">
                                                <input type="checkbox" id="select-all">
                                                <label for="select-all"></label>
                                            </div>
                                        </th>
                                        <th>Name (AR)</th>
                                        <th>Name (EN)</th>
                                        <th>Name (KU)</th>
                                        <th>Sort Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($documentTypes as $documentType)
                                        <tr>
                                            <td>
                                                <div class="icheck-primary d-inline">
                                                    <input type="checkbox" name="selected_document_types[]" value="{{ $documentType->id }}" id="select-{{ $documentType->id }}">
                                                    <label for="select-{{ $documentType->id }}"></label>
                                                </div>
                                            </td>
                                            <td>{{ $documentType->name_ar }}</td>
                                            <td>{{ $documentType->name_en }}</td>
                                            <td>{{ $documentType->name_ku }}</td>
                                            <td>{{ $documentType->sort_order }}</td>
                                            <td>
                                                <span class="badge {{ $documentType->is_active ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $documentType->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.property-document-types.show', $documentType) }}" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.property-document-types.edit', $documentType) }}" class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="confirmDelete({{ $documentType->id }}, '{{ $documentType->name_en }}')" 
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button type="button" class="btn btn-{{ $documentType->is_active ? 'warning' : 'success' }} btn-sm"
                                                        onclick="toggleStatus({{ $documentType->id }})"
                                                        title="{{ $documentType->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas fa-{{ $documentType->is_active ? 'times' : 'check' }}"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No property document types found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bulk-actions d-none">
                                    <div class="input-group">
                                        <select name="bulk_action" class="form-control" id="bulk-action-select">
                                            <option value="">Select Bulk Action</option>
                                            <option value="activate">Activate</option>
                                            <option value="deactivate">Deactivate</option>
                                            <option value="delete">Delete</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-secondary" onclick="performBulkAction()">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="float-right">
                                    {{ $documentTypes->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="toggle-status-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="bulk-action-form" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="bulk_action" id="bulk-action-input">
</form>
@endsection

@section('scripts')
<script>
function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete the document type "${name}"?`)) {
        let form = document.getElementById('delete-form');
        form.action = `{{ url('admin/property-document-types') }}/${id}`;
        form.submit();
    }
}

function toggleStatus(id) {
    let form = document.getElementById('toggle-status-form');
    form.action = `{{ url('admin/property-document-types') }}/${id}/toggle-status`;
    form.submit();
}

document.getElementById('select-all').addEventListener('change', function() {
    let checkboxes = document.querySelectorAll('input[name="selected_document_types[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    
    document.querySelector('.bulk-actions').classList.toggle('d-none', !this.checked);
});

document.querySelectorAll('input[name="selected_document_types[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        let anyChecked = document.querySelectorAll('input[name="selected_document_types[]"]:checked').length > 0;
        document.querySelector('.bulk-actions').classList.toggle('d-none', !anyChecked);
        
        // Update select-all checkbox state
        let totalCheckboxes = document.querySelectorAll('input[name="selected_document_types[]"]').length;
        let checkedCheckboxes = document.querySelectorAll('input[name="selected_document_types[]"]:checked').length;
        document.getElementById('select-all').checked = totalCheckboxes === checkedCheckboxes;
    });
});

function performBulkAction() {
    let selectedIds = Array.from(document.querySelectorAll('input[name="selected_document_types[]"]:checked'))
                          .map(checkbox => checkbox.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one document type.');
        return;
    }
    
    let action = document.getElementById('bulk-action-select').value;
    if (!action) {
        alert('Please select a bulk action.');
        return;
    }
    
    if (action === 'delete' && !confirm('Are you sure you want to delete the selected document types?')) {
        return;
    }
    
    let form = document.getElementById('bulk-action-form');
    form.action = `{{ route('admin.property-document-types.bulk-action') }}`;
    
    // Add selected IDs to form
    selectedIds.forEach(id => {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'document_types[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.getElementById('bulk-action-input').value = action;
    form.submit();
}
</script>
@endsection