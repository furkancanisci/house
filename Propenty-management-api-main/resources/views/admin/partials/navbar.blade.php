<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">{{ __('admin.home') }}</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link" target="_blank">{{ __('admin.view_site') }}</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav {{ app()->getLocale() === 'ar' ? 'mr-auto' : 'ml-auto' }}">
        <!-- Navbar Search -->
        <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="fas fa-search"></i>
            </a>
            <div class="navbar-search-block">
                <form class="form-inline">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="{{ __('admin.search') }}" aria-label="{{ __('admin.search') }}">
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
            <div class="dropdown-menu dropdown-menu-lg {{ app()->getLocale() === 'ar' ? 'dropdown-menu-left' : 'dropdown-menu-right' }}">
                <span class="dropdown-item dropdown-header">{{ $unreadMessages }} {{ __('admin.messages') }}</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-envelope mr-2"></i> {{ __('admin.no_new_messages') ?? 'No new messages' }}
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">{{ __('admin.see_all_messages') ?? 'See All Messages' }}</a>
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
            <div class="dropdown-menu dropdown-menu-lg {{ app()->getLocale() === 'ar' ? 'dropdown-menu-left' : 'dropdown-menu-right' }}">
                <span class="dropdown-item dropdown-header">{{ $totalNotifications }} {{ __('admin.notifications') }}</span>
                
                @if($newLeads > 0)
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.leads.index') }}" class="dropdown-item">
                    <i class="fas fa-user-tie mr-2"></i> {{ $newLeads }} {{ __('admin.new_leads') ?? 'new lead(s)' }}
                    <span class="float-right text-muted text-sm">{{ __('admin.today') }}</span>
                </a>
                @endif
                
                @if($pendingProperties > 0)
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.moderation.index') }}" class="dropdown-item">
                    <i class="fas fa-home mr-2"></i> {{ $pendingProperties }} {{ __('admin.pending_properties') ?? 'pending properties' }}
                    <span class="float-right text-muted text-sm">{{ __('admin.review') ?? 'Review' }}</span>
                </a>
                @endif
                
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">{{ __('admin.see_all_notifications') ?? 'See All Notifications' }}</a>
            </div>
        </li>

        <!-- Language Switcher -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                @if(app()->getLocale() === 'ar')
                    <i class="fas fa-globe"></i> عربي
                @else
                    <i class="fas fa-globe"></i> EN
                @endif
                <i class="fas fa-angle-down ml-1"></i>
            </a>
            <div class="dropdown-menu {{ app()->getLocale() === 'ar' ? 'dropdown-menu-left' : 'dropdown-menu-right' }} language-switcher">
                <h6 class="dropdown-header">{{ __('admin.change_language') }}</h6>
                <div class="dropdown-divider"></div>
                
                <form method="POST" action="{{ route('language.switch') }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="locale" value="en">
                    <button type="submit" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                        <i class="fas fa-flag mr-2"></i> {{ __('admin.english') }}
                        @if(app()->getLocale() === 'en')
                            <i class="fas fa-check float-right mt-1"></i>
                        @endif
                    </button>
                </form>
                
                <form method="POST" action="{{ route('language.switch') }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="locale" value="ar">
                    <button type="submit" class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
                        <i class="fas fa-flag mr-2"></i> {{ __('admin.arabic') }}
                        @if(app()->getLocale() === 'ar')
                            <i class="fas fa-check float-right mt-1"></i>
                        @endif
                    </button>
                </form>
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
            <div class="dropdown-menu dropdown-menu-lg {{ app()->getLocale() === 'ar' ? 'dropdown-menu-left' : 'dropdown-menu-right' }}">
                <span class="dropdown-item dropdown-header">
                    {{ auth()->user()->full_name ?? 'Guest' }}
                    <br>
                    <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
                </span>
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.profile.index') }}" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> {{ __('admin.profile') }}
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                    <i class="fas fa-cogs mr-2"></i> {{ __('admin.settings') }}
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
                    <i class="fas fa-sign-out-alt mr-2"></i> {{ __('admin.logout') }}
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