@extends('admin.layouts.app')

@section('title', __('admin.contact_messages') ?? 'Contact Messages')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('admin.contact_messages') ?? 'Contact Messages' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.contact_messages') ?? 'Contact Messages' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['total'] }}</h3>
                            <p>{{ __('admin.total_messages') ?? 'Total Messages' }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['unread'] }}</h3>
                            <p>{{ __('admin.unread_messages') ?? 'Unread Messages' }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $stats['spam'] }}</h3>
                            <p>{{ __('admin.spam_messages') ?? 'Spam Messages' }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['today'] }}</h3>
                            <p>{{ __('admin.today_messages') ?? 'Today\'s Messages' }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.filter_messages') ?? 'Filter Messages' }}</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.contact.index') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('admin.search') ?? 'Search' }}</label>
                                    <input type="text" name="search" class="form-control" 
                                           value="{{ request('search') }}" 
                                           placeholder="{{ __('admin.search_messages') ?? 'Search in name, email, subject, message...' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('admin.status') ?? 'Status' }}</label>
                                    <select name="status" class="form-control">
                                        <option value="">{{ __('admin.all_statuses') ?? 'All Statuses' }}</option>
                                        <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>
                                            {{ __('admin.unread') ?? 'Unread' }}
                                        </option>
                                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>
                                            {{ __('admin.read') ?? 'Read' }}
                                        </option>
                                        <option value="spam" {{ request('status') === 'spam' ? 'selected' : '' }}>
                                            {{ __('admin.spam') ?? 'Spam' }}
                                        </option>
                                        <option value="not_spam" {{ request('status') === 'not_spam' ? 'selected' : '' }}>
                                            {{ __('admin.not_spam') ?? 'Not Spam' }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> {{ __('admin.search') ?? 'Search' }}
                                        </button>
                                        <a href="{{ route('admin.contact.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> {{ __('admin.clear') ?? 'Clear' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Messages List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.messages') ?? 'Messages' }} ({{ $messages->total() }})</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.contact.settings') }}" class="btn btn-sm btn-info mr-2">
                            <i class="fas fa-cog"></i> {{ __('admin.contact_settings') ?? 'Contact Settings' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')" id="bulk-delete-btn" style="display: none;">
                            <i class="fas fa-trash"></i> {{ __('admin.delete_selected') ?? 'Delete Selected' }}
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('mark_spam')" id="bulk-spam-btn" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> {{ __('admin.mark_as_spam') ?? 'Mark as Spam' }}
                        </button>
                        <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('mark_read')" id="bulk-read-btn" style="display: none;">
                            <i class="fas fa-check"></i> {{ __('admin.mark_as_read') ?? 'Mark as Read' }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($messages->count() > 0)
                    <form id="bulk-action-form" method="POST" action="{{ route('admin.contact.bulk-action') }}">
                        @csrf
                        <input type="hidden" name="action" id="bulk-action-type">
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="select-all">
                                                <label for="select-all"></label>
                                            </div>
                                        </th>
                                        <th>{{ __('admin.name') ?? 'Name' }}</th>
                                        <th>{{ __('admin.email') ?? 'Email' }}</th>
                                        <th>{{ __('admin.subject') ?? 'Subject' }}</th>
                                        <th>{{ __('admin.message') ?? 'Message' }}</th>
                                        <th>{{ __('admin.status') ?? 'Status' }}</th>
                                        <th>{{ __('admin.date') ?? 'Date' }}</th>
                                        <th width="120">{{ __('admin.actions') ?? 'Actions' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                    <tr class="{{ !$message->is_read ? 'table-warning' : '' }} {{ $message->is_spam ? 'table-danger' : '' }}">
                                        <td>
                                            <div class="icheck-primary">
                                                <input type="checkbox" name="messages[]" value="{{ $message->id }}" class="message-checkbox" id="check-{{ $message->id }}">
                                                <label for="check-{{ $message->id }}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $message->name }}</strong>
                                            @if($message->phone)
                                                <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $message->phone }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $message->email }}</td>
                                        <td>{{ Str::limit($message->subject, 30) }}</td>
                                        <td>{{ Str::limit($message->message, 50) }}</td>
                                        <td>
                                            @if($message->is_spam)
                                                <span class="badge badge-danger">{{ __('admin.spam') ?? 'Spam' }}</span>
                                            @elseif($message->is_read)
                                                <span class="badge badge-success">{{ __('admin.read') ?? 'Read' }}</span>
                                            @else
                                                <span class="badge badge-warning">{{ __('admin.unread') ?? 'Unread' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $message->created_at->format('M j, Y') }}<br>{{ $message->created_at->format('g:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.contact.show', $message) }}" class="btn btn-sm btn-info" title="{{ __('admin.view') ?? 'View' }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(!$message->is_spam)
                                                <form method="POST" action="{{ route('admin.contact.mark-spam', $message) }}" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-warning" title="{{ __('admin.mark_as_spam') ?? 'Mark as Spam' }}" 
                                                            onclick="return confirm('{{ __('admin.confirm_spam') ?? 'Are you sure you want to mark this as spam?' }}')">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                <form method="POST" action="{{ route('admin.contact.destroy', $message) }}" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin.delete') ?? 'Delete' }}" 
                                                            onclick="return confirm('{{ __('admin.confirm_delete') ?? 'Are you sure you want to delete this message?' }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">{{ __('admin.no_messages') ?? 'No messages found' }}</h4>
                        <p class="text-muted">{{ __('admin.no_messages_desc') ?? 'There are no contact messages matching your criteria.' }}</p>
                    </div>
                    @endif
                </div>
                
                @if($messages->hasPages())
                <div class="card-footer">
                    {{ $messages->links() }}
                </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#select-all').on('change', function() {
        $('.message-checkbox').prop('checked', this.checked);
        toggleBulkButtons();
    });
    
    // Individual checkbox change
    $('.message-checkbox').on('change', function() {
        toggleBulkButtons();
        
        // Update select all checkbox
        var total = $('.message-checkbox').length;
        var checked = $('.message-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked === total);
    });
    
    function toggleBulkButtons() {
        var checkedCount = $('.message-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulk-delete-btn, #bulk-spam-btn, #bulk-read-btn').show();
        } else {
            $('#bulk-delete-btn, #bulk-spam-btn, #bulk-read-btn').hide();
        }
    }
});

function bulkAction(action) {
    var checkedCount = $('.message-checkbox:checked').length;
    if (checkedCount === 0) {
        alert('{{ __("admin.select_messages") ?? "Please select at least one message." }}');
        return;
    }
    
    var confirmMessages = {
        'delete': '{{ __("admin.confirm_bulk_delete") ?? "Are you sure you want to delete the selected messages?" }}',
        'mark_spam': '{{ __("admin.confirm_bulk_spam") ?? "Are you sure you want to mark the selected messages as spam?" }}',
        'mark_read': '{{ __("admin.confirm_bulk_read") ?? "Are you sure you want to mark the selected messages as read?" }}'
    };
    
    if (confirm(confirmMessages[action])) {
        $('#bulk-action-type').val(action);
        $('#bulk-action-form').submit();
    }
}
</script>
@endpush