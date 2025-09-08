@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content-header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>My Profile</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @if($user->avatar)
                                <img class="profile-user-img img-fluid img-circle"
                                     src="{{ Storage::url($user->avatar) }}"
                                     alt="User profile picture"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <img class="profile-user-img img-fluid img-circle"
                                     src="https://ui-avatars.com/api/?name={{ urlencode($user->full_name ?? $user->first_name . ' ' . $user->last_name) }}&size=150"
                                     alt="User profile picture">
                            @endif
                        </div>

                        <h3 class="profile-username text-center">{{ $user->full_name ?? $user->first_name . ' ' . $user->last_name }}</h3>
                        <p class="text-muted text-center">{{ $user->role ?? 'Administrator' }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Email</b> <a class="float-right">{{ $user->email }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Phone</b> <a class="float-right">{{ $user->phone ?? 'Not set' }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Member Since</b> <a class="float-right">{{ $user->created_at->format('M d, Y') }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Last Login</b> <a class="float-right">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}</a>
                            </li>
                        </ul>

                        <div class="row">
                            <div class="col-6">
                                <a href="{{ route('admin.profile.change-password') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.profile.settings') }}" class="btn btn-info btn-block">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About Me Box -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">About Me</h3>
                    </div>
                    <div class="card-body">
                        @if($user->bio)
                            <p class="text-muted">{{ $user->bio }}</p>
                        @else
                            <p class="text-muted">No bio added yet.</p>
                        @endif

                        <hr>

                        <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                        <p class="text-muted">
                            @if($user->city || $user->state)
                                {{ $user->city }}{{ $user->city && $user->state ? ', ' : '' }}{{ $user->state }}
                            @else
                                Not set
                            @endif
                        </p>

                        <hr>

                        <strong><i class="fas fa-envelope mr-1"></i> Email Verified</strong>
                        <p class="text-muted">
                            @if($user->email_verified_at)
                                <span class="badge badge-success">Verified</span>
                            @else
                                <span class="badge badge-warning">Not Verified</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Profile Information</a></li>
                            <li class="nav-item"><a class="nav-link" href="#address" data-toggle="tab">Address</a></li>
                            <li class="nav-item"><a class="nav-link" href="#avatar" data-toggle="tab">Avatar</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="tab-content">
                                <!-- Profile Tab -->
                                <div class="active tab-pane" id="profile">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                                       id="first_name" name="first_name" 
                                                       value="{{ old('first_name', $user->first_name) }}" required>
                                                @error('first_name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                                       id="last_name" name="last_name" 
                                                       value="{{ old('last_name', $user->last_name) }}" required>
                                                @error('last_name')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                       id="email" name="email" 
                                                       value="{{ old('email', $user->email) }}" required>
                                                @error('email')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                       id="phone" name="phone" 
                                                       value="{{ old('phone', $user->phone) }}">
                                                @error('phone')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="bio">Bio</label>
                                        <textarea class="form-control @error('bio') is-invalid @enderror" 
                                                  id="bio" name="bio" rows="4" 
                                                  maxlength="500">{{ old('bio', $user->bio) }}</textarea>
                                        <small class="form-text text-muted">Maximum 500 characters</small>
                                        @error('bio')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Address Tab -->
                                <div class="tab-pane" id="address">
                                    <div class="form-group">
                                        <label for="address">Street Address</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                               id="address" name="address" 
                                               value="{{ old('address', $user->address) }}">
                                        @error('address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="city">City</label>
                                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                                       id="city" name="city" 
                                                       value="{{ old('city', $user->city) }}">
                                                @error('city')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="state">State/Province</label>
                                                <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                                       id="state" name="state" 
                                                       value="{{ old('state', $user->state) }}">
                                                @error('state')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="postal_code">Postal Code</label>
                                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                                       id="postal_code" name="postal_code" 
                                                       value="{{ old('postal_code', $user->postal_code) }}">
                                                @error('postal_code')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Avatar Tab -->
                                <div class="tab-pane" id="avatar">
                                    <div class="form-group">
                                        <label for="avatar">Profile Picture</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('avatar') is-invalid @enderror" 
                                                   id="avatar" name="avatar" accept="image/*">
                                            <label class="custom-file-label" for="avatar">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Accepted formats: JPG, PNG, WebP. Max size: 2MB
                                        </small>
                                        @error('avatar')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    @if($user->avatar)
                                        <div class="form-group">
                                            <label>Current Avatar</label>
                                            <div>
                                                <img src="{{ Storage::url($user->avatar) }}" 
                                                     alt="Current avatar" 
                                                     class="img-thumbnail"
                                                     style="max-width: 200px;">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-default">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Update file input label with selected filename
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>
@endpush
