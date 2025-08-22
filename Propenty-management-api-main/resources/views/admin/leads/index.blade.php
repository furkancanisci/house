@extends('admin.layouts.app')

@section('title', 'Leads Management')

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Leads</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Leads & Inquiries</h3>
                <div class="card-tools">
                    @can('export leads')
                    <a href="{{ route('admin.leads.export') }}?{{ request()->getQueryString() }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </a>
                    @endcan
                    @can('create leads')
                    <a href="{{ route('admin.leads.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Lead
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" class="mb-3" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by name, email, phone..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                                    <option value="unqualified" {{ request('status') == 'unqualified' ? 'selected' : '' }}>Unqualified</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select name="source" class="form-control">
                                    <option value="">All Sources</option>
                                    <option value="website" {{ request('source') == 'website' ? 'selected' : '' }}>Website</option>
                                    <option value="contact_form" {{ request('source') == 'contact_form' ? 'selected' : '' }}>Contact Form</option>
                                    <option value="listing_inquiry" {{ request('source') == 'listing_inquiry' ? 'selected' : '' }}>Listing Inquiry</option>
                                    <option value="phone" {{ request('source') == 'phone' ? 'selected' : '' }}>Phone</option>
                                    <option value="walk_in" {{ request('source') == 'walk_in' ? 'selected' : '' }}>Walk-in</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select name="assigned_to" class="form-control">
                                    <option value="">All Agents</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.leads.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                    
                    <!-- Advanced Filters -->
                    <div class="row mt-2">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Min Quality Score</label>
                                <select name="quality_score" class="form-control">
                                    <option value="">Any Score</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ request('quality_score') == $i ? 'selected' : '' }}>
                                            {{ $i }}+ ⭐
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Statistics Cards -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Leads</span>
                                <span class="info-box-number">{{ $leads->total() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">New Leads</span>
                                <span class="info-box-number">{{ $leads->where('status', 'new')->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Qualified</span>
                                <span class="info-box-number">{{ $leads->where('status', 'qualified')->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Closed</span>
                                <span class="info-box-number">{{ $leads->where('status', 'closed')->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leads Table -->
                <div id="leadsTable">
                    @include('admin.leads.partials.table')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Lead</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modalAgent">Select Agent</label>
                        <select name="agent_id" id="modalAgent" class="form-control" required>
                            <option value="">Choose Agent...</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="noteForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modalNote">Note</label>
                        <textarea name="note" id="modalNote" class="form-control" rows="3" required></textarea>
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
    // Auto-submit filter form on change
    $('#filterForm select').on('change', function() {
        $('#filterForm').submit();
    });

    // AJAX pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        
        $.get(url, function(data) {
            $('#leadsTable').html(data.html);
        });
    });

    // Quick assign lead
    let currentLeadId = null;
    $(document).on('click', '.assign-lead', function(e) {
        e.preventDefault();
        currentLeadId = $(this).data('lead');
        $('#assignModal').modal('show');
    });

    $('#assignForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!currentLeadId) return;
        
        let formData = $(this).serialize();
        
        $.post(`/admin/leads/${currentLeadId}/assign`, formData)
        .done(function(response) {
            $('#assignModal').modal('hide');
            Swal.fire('Success', response.success, 'success');
            location.reload();
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
            Swal.fire('Error', error, 'error');
        });
    });

    // Update lead status
    $(document).on('change', '.lead-status', function() {
        let leadId = $(this).data('lead');
        let status = $(this).val();
        
        $.post(`/admin/leads/${leadId}/status`, {
            _token: $('meta[name="csrf-token"]').attr('content'),
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
    let currentNoteLeadId = null;
    $(document).on('click', '.add-note', function(e) {
        e.preventDefault();
        currentNoteLeadId = $(this).data('lead');
        $('#noteModal').modal('show');
    });

    $('#noteForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!currentNoteLeadId) return;
        
        let formData = $(this).serialize();
        
        $.post(`/admin/leads/${currentNoteLeadId}/notes`, formData)
        .done(function(response) {
            $('#noteModal').modal('hide');
            $('#modalNote').val('');
            Swal.fire('Success', response.success, 'success');
            location.reload();
        })
        .fail(function(xhr) {
            const error = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred';
            Swal.fire('Error', error, 'error');
        });
    });

    // Delete confirmation
    $(document).on('click', '.delete-lead', function(e) {
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