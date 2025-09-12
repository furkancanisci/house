@extends('admin.layouts.app')

@section('title', __('admin.view_message') ?? 'View Message')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('admin.view_message') ?? 'View Message' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.contact.index') }}">{{ __('admin.contact_messages') ?? 'Contact Messages' }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.view') ?? 'View' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <!-- Message Details -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-envelope"></i> {{ $contactMessage->subject }}
                            </h3>
                            <div class="card-tools">
                                @if($contactMessage->is_spam)
                                    <span class="badge badge-danger">{{ __('admin.spam') ?? 'Spam' }}</span>
                                @elseif($contactMessage->is_read)
                                    <span class="badge badge-success">{{ __('admin.read') ?? 'Read' }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('admin.unread') ?? 'Unread' }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Sender Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <strong>{{ __('admin.from') ?? 'From' }}:</strong><br>
                                    <div class="mt-1">
                                        <i class="fas fa-user text-muted"></i> {{ $contactMessage->name }}<br>
                                        <i class="fas fa-envelope text-muted"></i> 
                                        <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a>
                                        @if($contactMessage->phone)
                                            <br><i class="fas fa-phone text-muted"></i> 
                                            <a href="tel:{{ $contactMessage->phone }}">{{ $contactMessage->phone }}</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <strong>{{ __('admin.date_received') ?? 'Date Received' }}:</strong><br>
                                    <div class="mt-1">
                                        <i class="fas fa-calendar text-muted"></i> {{ $contactMessage->created_at->format('F j, Y') }}<br>
                                        <i class="fas fa-clock text-muted"></i> {{ $contactMessage->created_at->format('g:i A') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Message Content -->
                            <div class="border-top pt-4">
                                <strong>{{ __('admin.message') ?? 'Message' }}:</strong>
                                <div class="mt-2 p-3 bg-light border-left border-primary" style="border-left-width: 4px !important;">
                                    {!! nl2br(e($contactMessage->message)) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Reply (Optional) -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-reply"></i> {{ __('admin.quick_reply') ?? 'Quick Reply' }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                {{ __('admin.quick_reply_info') ?? 'To reply to this message, copy the email address above and compose a new email in your email client.' }}
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-primary" onclick="openEmailClient()">
                                        <i class="fas fa-external-link-alt"></i> {{ __('admin.open_email_client') ?? 'Open Email Client' }}
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-secondary" onclick="copyEmail()">
                                        <i class="fas fa-copy"></i> {{ __('admin.copy_email') ?? 'Copy Email Address' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('admin.actions') ?? 'Actions' }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if(!$contactMessage->is_read)
                                <form method="POST" action="{{ route('admin.contact.mark-read', $contactMessage) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-check"></i> {{ __('admin.mark_as_read') ?? 'Mark as Read' }}
                                    </button>
                                </form>
                                @endif

                                @if(!$contactMessage->is_spam)
                                <form method="POST" action="{{ route('admin.contact.mark-spam', $contactMessage) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-warning btn-block" 
                                            onclick="return confirm('{{ __('admin.confirm_spam') ?? 'Are you sure you want to mark this as spam?' }}')">
                                        <i class="fas fa-exclamation-triangle"></i> {{ __('admin.mark_as_spam') ?? 'Mark as Spam' }}
                                    </button>
                                </form>
                                @endif

                                <form method="POST" action="{{ route('admin.contact.destroy', $contactMessage) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block" 
                                            onclick="return confirm('{{ __('admin.confirm_delete') ?? 'Are you sure you want to delete this message?' }}')">
                                        <i class="fas fa-trash"></i> {{ __('admin.delete') ?? 'Delete' }}
                                    </button>
                                </form>

                                <a href="{{ route('admin.contact.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_list') ?? 'Back to List' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Message Info -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('admin.message_info') ?? 'Message Info' }}</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">{{ __('admin.status') ?? 'Status' }}:</dt>
                                <dd class="col-sm-7">
                                    @if($contactMessage->is_spam)
                                        <span class="badge badge-danger">{{ __('admin.spam') ?? 'Spam' }}</span>
                                    @elseif($contactMessage->is_read)
                                        <span class="badge badge-success">{{ __('admin.read') ?? 'Read' }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ __('admin.unread') ?? 'Unread' }}</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-5">{{ __('admin.received') ?? 'Received' }}:</dt>
                                <dd class="col-sm-7">{{ $contactMessage->created_at->diffForHumans() }}</dd>

                                @if($contactMessage->read_at)
                                <dt class="col-sm-5">{{ __('admin.read_at') ?? 'Read At' }}:</dt>
                                <dd class="col-sm-7">{{ $contactMessage->read_at->diffForHumans() }}</dd>
                                @endif

                                @if($contactMessage->ip_address)
                                <dt class="col-sm-5">{{ __('admin.ip_address') ?? 'IP Address' }}:</dt>
                                <dd class="col-sm-7"><code>{{ $contactMessage->ip_address }}</code></dd>
                                @endif

                                @if($contactMessage->user_agent)
                                <dt class="col-sm-5">{{ __('admin.user_agent') ?? 'User Agent' }}:</dt>
                                <dd class="col-sm-7"><small class="text-muted">{{ Str::limit($contactMessage->user_agent, 50) }}</small></dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Hidden input for copying email -->
<input type="hidden" id="email-address" value="{{ $contactMessage->email }}">
@endsection

@push('scripts')
<script>
function openEmailClient() {
    const email = '{{ $contactMessage->email }}';
    const subject = encodeURIComponent('Re: {{ $contactMessage->subject }}');
    const body = encodeURIComponent('\n\n---\nOriginal message from {{ $contactMessage->name }} ({{ $contactMessage->created_at->format("M j, Y") }}):\n{{ $contactMessage->message }}');
    
    window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
}

function copyEmail() {
    const emailInput = document.getElementById('email-address');
    emailInput.type = 'text';
    emailInput.select();
    emailInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        // Show success message
        toastr.success('{{ __("admin.email_copied") ?? "Email address copied to clipboard!" }}');
    } catch (err) {
        console.error('Failed to copy email:', err);
        toastr.error('{{ __("admin.copy_failed") ?? "Failed to copy email address" }}');
    }
    
    emailInput.type = 'hidden';
}
</script>
@endpush