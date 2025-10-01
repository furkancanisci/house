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