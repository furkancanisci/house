<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link" target="_blank">View Site</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="fas fa-search"></i>
            </a>
            <div class="navbar-search-block">
                <form class="form-inline">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Messages Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-comments"></i>
                @php
                    $unreadMessages = 0; // TODO: Get actual unread messages count
                @endphp
                @if($unreadMessages > 0)
                    <span class="badge badge-danger navbar-badge">{{ $unreadMessages }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $unreadMessages }} Messages</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-envelope mr-2"></i> No new messages
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
            </div>
        </li>

        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                @php
                    $newLeads = \App\Models\Lead::where('status', 'new')->where('created_at', '>=', now()->subDay())->count();
                    $pendingProperties = \App\Models\Property::where('status', 'pending')->count();
                    $totalNotifications = $newLeads + $pendingProperties;
                @endphp
                @if($totalNotifications > 0)
                    <span class="badge badge-warning navbar-badge">{{ $totalNotifications }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ $totalNotifications }} Notifications</span>
                
                @if($newLeads > 0)
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.leads.index') }}" class="dropdown-item">
                    <i class="fas fa-user-tie mr-2"></i> {{ $newLeads }} new lead(s)
                    <span class="float-right text-muted text-sm">Today</span>
                </a>
                @endif
                
                @if($pendingProperties > 0)
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.moderation.index') }}" class="dropdown-item">
                    <i class="fas fa-home mr-2"></i> {{ $pendingProperties }} pending properties
                    <span class="float-right text-muted text-sm">Review</span>
                </a>
                @endif
                
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>

        <!-- User Account Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->full_name ?? 'User') }}" 
                     class="img-circle elevation-2" 
                     alt="User Image" 
                     style="width: 30px; height: 30px;">
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">
                    {{ auth()->user()->full_name ?? 'Guest' }}
                    <br>
                    <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
                </span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> Profile
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                    <i class="fas fa-cogs mr-2"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
                <form id="logout-form-nav" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>

        <!-- Fullscreen Button -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>