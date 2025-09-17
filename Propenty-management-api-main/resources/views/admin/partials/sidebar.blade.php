<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{ __('admin.admin_panel') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->full_name ?? 'User') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->full_name ?? 'Guest' }}</a>
                <small class="text-muted">{{ auth()->user()->roles->first()->name ?? 'User' }}</small>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="{{ __('admin.search') }}" aria-label="{{ __('admin.search') }}">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{ __('admin.dashboard') }}</p>
                    </a>
                </li>

                <!-- Properties Management -->
                @canany(['view properties', 'create properties', 'moderate properties'])
                <li class="nav-header">{{ strtoupper(__('admin.property_management')) }}</li>
                <li class="nav-item {{ request()->routeIs('admin.properties*') || request()->routeIs('admin.moderation*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.properties*') || request()->routeIs('admin.moderation*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-home"></i>
                        <p>
                            {{ __('admin.properties') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('view properties')
                        <li class="nav-item">
                            <a href="{{ route('admin.properties.index') }}" class="nav-link {{ request()->routeIs('admin.properties.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.view_all') }} {{ __('admin.properties') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('create properties')
                        <li class="nav-item">
                            <a href="{{ route('admin.properties.create') }}" class="nav-link {{ request()->routeIs('admin.properties.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.create') }} {{ __('admin.property') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('moderate properties')
                        <li class="nav-item">
                            <a href="{{ route('admin.moderation.index') }}" class="nav-link {{ request()->routeIs('admin.moderation*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.moderation') }}</p>
                                @php
                                    $pendingCount = \App\Models\Property::where('status', 'pending')->count();
                                @endphp
                                @if($pendingCount > 0)
                                    <span class="badge badge-warning right">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                <!-- Categories -->
                @can('view categories')
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>{{ __('admin.property_types') }}</p>
                    </a>
                </li>
                @endcan

                <!-- Price Types -->
                @can('view price types')
                <li class="nav-item">
                    <a href="{{ route('admin.price-types.index') }}" class="nav-link {{ request()->routeIs('admin.price-types*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-dollar-sign"></i>
                        <p>{{ __('admin.price_types') }}</p>
                    </a>
                </li>
                @endcan

                <!-- Locations -->
                @canany(['view cities', 'manage neighborhoods'])
                <li class="nav-item {{ request()->routeIs('admin.cities*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.cities*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-map-marker-alt"></i>
                        <p>
                            {{ __('admin.location_management') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('view cities')
                        <li class="nav-item">
                            <a href="{{ route('admin.cities.index') }}" class="nav-link {{ request()->routeIs('admin.cities.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.cities') }}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                <!-- Features -->
                @can('view features')
                <li class="nav-item">
                    <a href="{{ route('admin.features.index') }}" class="nav-link {{ request()->routeIs('admin.features*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-star"></i>
                        <p>{{ __('admin.features') }}</p>
                    </a>
                </li>
                @endcan

                <!-- Utilities -->
                @can('view utilities')
                <li class="nav-item">
                    <a href="{{ route('admin.utilities.index') }}" class="nav-link {{ request()->routeIs('admin.utilities*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>{{ __('admin.utilities') }}</p>
                    </a>
                </li>
                @endcan

                <!-- Advanced Property Details -->
                @canany(['view building types', 'view window types', 'view floor types', 'view view types', 'view directions'])
                <li class="nav-item {{ request()->routeIs('admin.building-types*') || request()->routeIs('admin.window-types*') || request()->routeIs('admin.floor-types*') || request()->routeIs('admin.view-types*') || request()->routeIs('admin.directions*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.building-types*') || request()->routeIs('admin.window-types*') || request()->routeIs('admin.floor-types*') || request()->routeIs('admin.view-types*') || request()->routeIs('admin.directions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-building"></i>
                        <p>
                            {{ __('admin.advanced_details') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('view building types')
                        <li class="nav-item">
                            <a href="{{ route('admin.building-types.index') }}" class="nav-link {{ request()->routeIs('admin.building-types*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.building_types') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('view window types')
                        <li class="nav-item">
                            <a href="{{ route('admin.window-types.index') }}" class="nav-link {{ request()->routeIs('admin.window-types*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.window_types') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('view floor types')
                        <li class="nav-item">
                            <a href="{{ route('admin.floor-types.index') }}" class="nav-link {{ request()->routeIs('admin.floor-types*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.floor_types') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('view view types')
                        <li class="nav-item">
                            <a href="{{ route('admin.view-types.index') }}" class="nav-link {{ request()->routeIs('admin.view-types*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('View Types') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('view directions')
                        <li class="nav-item">
                            <a href="{{ route('admin.directions.index') }}" class="nav-link {{ request()->routeIs('admin.directions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('Directions') }}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                <!-- User Management -->
                @canany(['view users', 'view leads'])
                <li class="nav-header">{{ strtoupper(__('admin.user_management')) }}</li>
                @endcanany

                @can('view users')
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>{{ __('admin.users') }}</p>
                    </a>
                </li>
                @endcan

                @can('view leads')
                <li class="nav-item">
                    <a href="{{ route('admin.leads.index') }}" class="nav-link {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>
                            {{ __('admin.leads') }}
                            @php
                                $newLeadsCount = \App\Models\Lead::where('status', 'new')->count();
                            @endphp
                            @if($newLeadsCount > 0)
                                <span class="badge badge-info right">{{ $newLeadsCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>
                @endcan

                <!-- Contact Messages -->
                @can('view contact messages')
                <li class="nav-item">
                    <a href="{{ route('admin.contact.index') }}" class="nav-link {{ request()->routeIs('admin.contact*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-envelope"></i>
                        <p>
                            {{ __('admin.contact_messages') ?? 'Contact Messages' }}
                            @php
                                $unreadCount = \App\Models\ContactMessage::where('is_read', false)->where('is_spam', false)->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="badge badge-primary right">{{ $unreadCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>
                @endcan

                <!-- Content Management -->
                @canany(['view media'])
                <li class="nav-header">{{ strtoupper(__('admin.content_management')) }}</li>
                @endcanany

                <!-- Home Statistics -->
                <li class="nav-item">
                    <a href="{{ route('admin.home-stats.index') }}" class="nav-link {{ request()->routeIs('admin.home-stats*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>إحصائيات الصفحة الرئيسية</p>
                    </a>
                </li>

                @can('view media')
                <li class="nav-item">
                    <a href="{{ route('admin.media.index') }}" class="nav-link {{ request()->routeIs('admin.media*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-photo-video"></i>
                        <p>{{ __('admin.media_library') ?? 'Media Library' }}</p>
                    </a>
                </li>
                @endcan

                <!-- Reports & Analytics -->
                @can('view reports')
                <li class="nav-header">{{ strtoupper(__('admin.reports')) }}</li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>{{ __('admin.reports') }}</p>
                    </a>
                </li>
                @endcan

                <!-- System Settings -->
                @canany(['view settings', 'manage roles', 'manage permissions'])
                <li class="nav-header">{{ strtoupper(__('admin.system_management')) }}</li>
                @endcanany

                @can('view settings')
                <li class="nav-item {{ request()->routeIs('admin.settings*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            {{ __('admin.settings') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.general_settings') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                @canany(['manage roles', 'manage permissions'])
                <li class="nav-item {{ request()->routeIs('admin.roles*') || request()->routeIs('admin.permissions*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.roles*') || request()->routeIs('admin.permissions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>
                            {{ __('admin.roles') }} & {{ __('admin.permissions') }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('manage roles')
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.roles') }}</p>
                            </a>
                        </li>
                        @endcan
                        @can('manage permissions')
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{ __('admin.permissions') }}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                <!-- Logout -->
                <li class="nav-header">{{ strtoupper(__('admin.session') ?? 'SESSION') }}</li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>{{ __('admin.logout') }}</p>
                    </a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>