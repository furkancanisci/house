@extends('admin.layouts.app')

@section('title', __('Directions Management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Directions Management') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.directions.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> {{ __('Add New Direction') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status_filter">{{ __('Status') }}</label>
                                <select class="form-control" id="status_filter" name="status">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">{{ __('Search') }}</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       placeholder="{{ __('Search by name...') }}" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="sort_by">{{ __('Sort By') }}</label>
                                <select class="form-control" id="sort_by" name="sort_by">
                                    <option value="sort_order" {{ request('sort_by') == 'sort_order' ? 'selected' : '' }}>{{ __('Sort Order') }}</option>
                                    <option value="name_en" {{ request('sort_by') == 'name_en' ? 'selected' : '' }}>{{ __('Name (English)') }}</option>
                                    <option value="name_ar" {{ request('sort_by') == 'name_ar' ? 'selected' : '' }}>{{ __('Name (Arabic)') }}</option>
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>{{ __('Created Date') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-flex">
                                    <button type="button" class="btn btn-info btn-sm mr-2" onclick="applyFilters()">
                                        <i class="fas fa-search"></i> {{ __('Filter') }}
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()">
                                        <i class="fas fa-times"></i> {{ __('Clear') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <div class="custom-control custom-checkbox mr-3">
                                    <input class="custom-control-input" type="checkbox" id="select_all">
                                    <label for="select_all" class="custom-control-label">{{ __('Select All') }}</label>
                                </div>
                                <div class="btn-group" id="bulk_actions" style="display: none;">
                                    <button type="button" class="btn btn-success btn-sm" onclick="bulkActivate()">
                                        <i class="fas fa-check"></i> {{ __('Activate Selected') }}
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkDeactivate()">
                                        <i class="fas fa-pause"></i> {{ __('Deactivate Selected') }}
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                                        <i class="fas fa-trash"></i> {{ __('Delete Selected') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Directions Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" id="select_all_table">
                                            <label for="select_all_table" class="custom-control-label"></label>
                                        </div>
                                    </th>
                                    <th width="8%">{{ __('ID') }}</th>
                                    <th width="20%">{{ __('Name (English)') }}</th>
                                    <th width="20%">{{ __('Name (Arabic)') }}</th>
                                    <th width="10%">{{ __('Value') }}</th>
                                    <th width="15%">{{ __('Description') }}</th>
                                    <th width="8%">{{ __('Sort Order') }}</th>
                                    <th width="8%">{{ __('Properties') }}</th>
                                    <th width="8%">{{ __('Status') }}</th>
                                    <th width="8%">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($directions as $direction)
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input row-checkbox" type="checkbox"
                                                   id="checkbox_{{ $direction->id }}" value="{{ $direction->id }}">
                                            <label for="checkbox_{{ $direction->id }}" class="custom-control-label"></label>
                                        </div>
                                    </td>
                                    <td>{{ $direction->id }}</td>
                                    <td>
                                        <strong>{{ $direction->name_en }}</strong>
                                        <br>
                                        <small class="text-muted">{{ __('Created') }}: {{ $direction->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td dir="rtl">
                                        <strong>{{ $direction->name_ar }}</strong>
                                        <br>
                                        <small class="text-muted">{{ __('Updated') }}: {{ $direction->updated_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-lg">{{ $direction->value }}</span>
                                    </td>
                                    <td>
                                        <div class="description-preview">
                                            {{ Str::limit($direction->description_en ?: $direction->description_ar, 50) }}
                                            @if(strlen($direction->description_en ?: $direction->description_ar) > 50)
                                                <button type="button" class="btn btn-link btn-sm p-0"
                                                        onclick="showFullDescription('{{ $direction->id }}')">
                                                    {{ __('Read more') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary badge-lg">{{ $direction->sort_order }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $direction->properties_count ?? 0 }}</span>
                                        @if(($direction->properties_count ?? 0) > 0)
                                            <br>
                                            <a href="{{ route('admin.properties.index', ['direction' => $direction->id]) }}"
                                               class="btn btn-link btn-sm p-0" title="{{ __('View Properties') }}">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.directions.toggle-status', $direction) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-{{ $direction->is_active ? 'success' : 'secondary' }}"
                                                    title="{{ $direction->is_active ? __('Active - Click to deactivate') : __('Inactive - Click to activate') }}">
                                                <i class="fas fa-{{ $direction->is_active ? 'check' : 'times' }}"></i>
                                                {{ $direction->is_active ? __('Active') : __('Inactive') }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.directions.show', $direction) }}"
                                               class="btn btn-info btn-sm" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.directions.edit', $direction) }}"
                                               class="btn btn-primary btn-sm" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(($direction->properties_count ?? 0) == 0)
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete({{ $direction->id }})" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-secondary btn-sm" disabled
                                                    title="{{ __('Cannot delete: Direction is used by properties') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-compass fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">{{ __('No directions found') }}</h5>
                                            <p class="text-muted">{{ __('Start by creating your first direction') }}</p>
                                            <a href="{{ route('admin.directions.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> {{ __('Add New Direction') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($directions->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="text-muted">
                                {{ __('Showing :from to :to of :total results', [
                                    'from' => $directions->firstItem(),
                                    'to' => $directions->lastItem(),
                                    'total' => $directions->total()
                                ]) }}
                            </p>
                        </div>
                        <div>
                            {{ $directions->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Forms -->
@foreach($directions as $direction)
<form id="delete-form-{{ $direction->id }}" action="{{ route('admin.directions.destroy', $direction) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach

<!-- Bulk Action Form -->
<form id="bulk-action-form" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulk_action">
    <input type="hidden" name="ids" id="bulk_ids">
</form>

<!-- Description Modal -->
<div class="modal fade" id="descriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Full Description') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-description-content">
                <!-- Description content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.empty-state {
    padding: 2rem;
}

.description-preview {
    max-width: 200px;
    word-wrap: break-word;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
}

@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }

    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 2px;
    }

    .btn-group .btn:last-child {
        margin-bottom: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Filter functionality
function applyFilters() {
    const params = new URLSearchParams();

    const status = document.getElementById('status_filter').value;
    const search = document.getElementById('search').value;
    const sortBy = document.getElementById('sort_by').value;

    if (status) params.append('status', status);
    if (search) params.append('search', search);
    if (sortBy) params.append('sort_by', sortBy);

    window.location.href = '{{ route('admin.directions.index') }}?' + params.toString();
}

function clearFilters() {
    window.location.href = '{{ route('admin.directions.index') }}';
}

// Enter key for search
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

// Select all functionality
document.getElementById('select_all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkActions();
});

document.getElementById('select_all_table').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    document.getElementById('select_all').checked = this.checked;
    toggleBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.row-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.row-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');

        document.getElementById('select_all').checked = allCheckboxes.length === checkedCheckboxes.length;
        document.getElementById('select_all_table').checked = allCheckboxes.length === checkedCheckboxes.length;

        toggleBulkActions();
    });
});

function toggleBulkActions() {
    const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkActions = document.getElementById('bulk_actions');

    if (checkedCheckboxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Bulk actions
function bulkActivate() {
    performBulkAction('activate', '{{ __('activate') }}');
}

function bulkDeactivate() {
    performBulkAction('deactivate', '{{ __('deactivate') }}');
}

function bulkDelete() {
    performBulkAction('delete', '{{ __('delete') }}');
}

function performBulkAction(action, actionText) {
    const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    const ids = Array.from(checkedCheckboxes).map(cb => cb.value);

    if (ids.length === 0) {
        toastr.warning('{{ __('Please select at least one direction') }}');
        return;
    }

    const confirmText = action === 'delete' ?
        '{{ __('This action cannot be undone!') }}' :
        '{{ __('Are you sure you want to :action the selected directions?', ['action' => ':action']) }}'.replace(':action', actionText);

    Swal.fire({
        title: '{{ __('Confirm Bulk Action') }}',
        text: confirmText,
        icon: action === 'delete' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#d33' : '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '{{ __('Yes, :action!', ['action' => ':action']) }}'.replace(':action', actionText),
        cancelButtonText: '{{ __('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('bulk-action-form');
            document.getElementById('bulk_action').value = action;
            document.getElementById('bulk_ids').value = ids.join(',');
            form.action = '{{ route('admin.directions.bulk-action') }}';
            form.submit();
        }
    });
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

// Show full description
function showFullDescription(id) {
    // You would typically fetch this via AJAX
    // For now, we'll use the data already available
    const descriptions = @json($directions->pluck('description_en', 'id')->merge($directions->pluck('description_ar', 'id')));

    const content = descriptions[id] || '{{ __('No description available') }}';
    document.getElementById('modal-description-content').innerHTML = '<p>' + content + '</p>';
    $('#descriptionModal').modal('show');
}

// Status toggle confirmation
$('form[action*="toggle-status"]').on('submit', function(e) {
    e.preventDefault();

    const form = this;
    const button = form.querySelector('button');
    const isActive = button.classList.contains('btn-success');
    const action = isActive ? '{{ __('deactivate') }}' : '{{ __('activate') }}';

    Swal.fire({
        title: '{{ __('Confirm Status Change') }}',
        text: '{{ __('Are you sure you want to :action this direction?', ['action' => ':action']) }}'.replace(':action', action),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '{{ __('Yes, :action it!', ['action' => ':action']) }}'.replace(':action', action),
        cancelButtonText: '{{ __('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

// Auto-refresh every 30 seconds (optional)
// setInterval(function() {
//     location.reload();
// }, 30000);

// Keyboard shortcuts
$(document).on('keydown', function(e) {
    // Ctrl+N for new direction
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '{{ route('admin.directions.create') }}';
    }

    // Ctrl+F for search focus
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        document.getElementById('search').focus();
    }
});
</script>
@endpush