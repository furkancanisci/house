@extends('admin.layouts.app')

@section('title', 'Lead Details')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">{{ $lead->name }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Lead Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lead Information</h3>
                <div class="card-tools">
                    @can('edit leads')
                    <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Name:</th>
                                <td>{{ $lead->name }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td>
                                    @if($lead->phone)
                                        <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Source:</th>
                                <td>
                                    <span class="badge badge-info">
                                        {{ ucfirst(str_replace('_', ' ', $lead->source)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @php
                                        $statusColors = [
                                            'new' => 'warning',
                                            'in_progress' => 'info',
                                            'qualified' => 'success',
                                            'unqualified' => 'secondary',
                                            'closed' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$lead->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Quality Score:</th>
                                <td>
                                    @if($lead->quality_score)
                                        <span class="badge badge-{{ $lead->quality_score >= 7 ? 'success' : ($lead->quality_score >= 4 ? 'warning' : 'danger') }}">
                                            {{ $lead->quality_score }}/10
                                        </span>
                                    @else
                                        <span class="text-muted">Not rated</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Property Type:</th>
                                <td>{{ $lead->property_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Listing Type:</th>
                                <td>{{ $lead->listing_type ? ucfirst($lead->listing_type) : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Budget:</th>
                                <td>
                                    @if($lead->budget_min || $lead->budget_max)
                                        @if($lead->budget_min && $lead->budget_max)
                                            ${{ number_format($lead->budget_min) }} - ${{ number_format($lead->budget_max) }}
                                        @elseif($lead->budget_min)
                                            Min: ${{ number_format($lead->budget_min) }}
                                        @else
                                            Max: ${{ number_format($lead->budget_max) }}
                                        @endif
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Bedrooms:</th>
                                <td>{{ $lead->bedrooms ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Bathrooms:</th>
                                <td>{{ $lead->bathrooms ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Move-in Date:</th>
                                <td>{{ $lead->move_in_date ? $lead->move_in_date->format('M d, Y') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($lead->preferred_location)
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>Preferred Location:</strong> {{ $lead->preferred_location }}
                    </div>
                </div>
                @endif

                @if($lead->message)
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>Message:</strong>
                        <div class="bg-light p-3 rounded">
                            {{ $lead->message }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Related Property -->
        @if($lead->property)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Related Property</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        @if($lead->property->getFirstMediaUrl('images'))
                            <img src="{{ $lead->property->getFirstMediaUrl('images', 'thumb') }}" 
                                 class="img-fluid rounded" alt="Property">
                        @else
                            <div class="bg-light p-4 text-center rounded">
                                <i class="fas fa-home fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <h5>{{ $lead->property->title }}</h5>
                        <p class="text-muted">{{ Str::limit($lead->property->description, 150) }}</p>
                        <p>
                            <strong>Price:</strong> ${{ number_format($lead->property->price) }}<br>
                            <strong>Location:</strong> {{ $lead->property->city?->name }}<br>
                            <strong>Type:</strong> {{ $lead->property->type?->name }}
                        </p>
                        <a href="{{ route('admin.properties.show', $lead->property) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View Property
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Internal Notes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Internal Notes & Communication History</h3>
                @can('edit leads')
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addNoteModal">
                        <i class="fas fa-plus"></i> Add Note
                    </button>
                </div>
                @endcan
            </div>
            <div class="card-body">
                @if($lead->internal_notes)
                    <div class="direct-chat-messages" style="height: 300px;">
                        <pre class="mb-0">{{ $lead->internal_notes }}</pre>
                    </div>
                @else
                    <p class="text-muted">No notes yet.</p>
                @endif
                
                @if($lead->contact_attempts > 0)
                <hr>
                <p class="text-muted">
                    <i class="fas fa-phone"></i> Contact Attempts: {{ $lead->contact_attempts }}
                    @if($lead->last_contacted_at)
                        | Last Contact: {{ $lead->last_contacted_at->format('M d, Y H:i') }}
                    @endif
                </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Assignment & Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assignment & Status</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Assigned To:</label>
                    @can('assign leads')
                    <select class="form-control" id="assignAgent">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>
                                {{ $agent->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @else
                    <p>{{ $lead->assignedTo?->full_name ?? 'Unassigned' }}</p>
                    @endcan
                </div>

                @if($lead->assigned_at)
                <p class="text-muted">
                    <small>Assigned: {{ $lead->assigned_at->format('M d, Y H:i') }}</small>
                </p>
                @endif

                <div class="form-group">
                    <label>Status:</label>
                    @can('edit leads')
                    <select class="form-control" id="updateStatus">
                        <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                        <option value="in_progress" {{ $lead->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="unqualified" {{ $lead->status == 'unqualified' ? 'selected' : '' }}>Unqualified</option>
                        <option value="closed" {{ $lead->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                    @else
                    <p>{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</p>
                    @endcan
                </div>

                @if($lead->converted_at)
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Converted on {{ $lead->converted_at->format('M d, Y') }}
                </div>
                @endif
            </div>
        </div>

        <!-- Metadata -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Metadata</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>Created:</th>
                        <td>{{ $lead->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Updated:</th>
                        <td>{{ $lead->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @if($lead->ip_address)
                    <tr>
                        <th>IP Address:</th>
                        <td>{{ $lead->ip_address }}</td>
                    </tr>
                    @endif
                    @if($lead->referrer)
                    <tr>
                        <th>Referrer:</th>
                        <td class="text-truncate" title="{{ $lead->referrer }}">
                            {{ parse_url($lead->referrer, PHP_URL_HOST) ?? $lead->referrer }}
                        </td>
                    </tr>
                    @endif
                </table>

                @if($lead->utm_parameters && count($lead->utm_parameters) > 0)
                <h6 class="mt-3">UTM Parameters:</h6>
                <table class="table table-sm table-borderless">
                    @foreach($lead->utm_parameters as $key => $value)
                    <tr>
                        <th>{{ ucfirst($key) }}:</th>
                        <td>{{ $value }}</td>
                    </tr>
                    @endforeach
                </table>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="mailto:{{ $lead->email }}" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-envelope"></i> Send Email
                </a>
                @if($lead->phone)
                <a href="tel:{{ $lead->phone }}" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-phone"></i> Call Lead
                </a>
                @endif
                @can('edit leads')
                <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Edit Lead
                </a>
                @endcan
                @can('delete leads')
                <hr>
                <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block delete-lead">
                        <i class="fas fa-trash"></i> Delete Lead
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea name="note" id="note" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update assignment
    $('#assignAgent').on('change', function() {
        let agentId = $(this).val();
        
        $.post('{{ route("admin.leads.assign", $lead) }}', {
            _token: '{{ csrf_token() }}',
            agent_id: agentId || null
        })
        .done(function(response) {
            Swal.fire('Success', response.success, 'success');
            location.reload();
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
            Swal.fire('Error', error, 'error');
        });
    });

    // Update status
    $('#updateStatus').on('change', function() {
        let status = $(this).val();
        
        $.post('{{ route("admin.leads.status", $lead) }}', {
            _token: '{{ csrf_token() }}',
            status: status
        })
        .done(function(response) {
            Swal.fire('Success', response.success, 'success');
            location.reload();
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
            Swal.fire('Error', error, 'error');
        });
    });

    // Add note
    $('#addNoteForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        $.post('{{ route("admin.leads.notes", $lead) }}', formData)
        .done(function(response) {
            $('#addNoteModal').modal('hide');
            $('#note').val('');
            Swal.fire('Success', response.success, 'success');
            location.reload();
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
            Swal.fire('Error', error, 'error');
        });
    });

    // Delete confirmation
    $('.delete-lead').on('click', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush