<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Property Admin</span>
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
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
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
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Properties Management -->
                @canany(['view properties', 'create properties', 'moderate properties'])
                <li class="nav-header">PROPERTY MANAGEMENT</li>
                <li class="nav-item {{ request()->routeIs('admin.properties*') || request()->routeIs('admin.moderation*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.properties*') || request()->routeIs('admin.moderation*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-home"></i>
                        <p>
                            Properties
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('view properties')
                        <li class="nav-item">
                            <a href="{{ route('admin.properties.index') }}" class="nav-link {{ request()->routeIs('admin.properties.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Properties</p>
                            </a>
                        </li>
                        @endcan
                        @can('create properties')
                        <li class="nav-item">
                            <a href="{{ route('admin.properties.create') }}" class="nav-link {{ request()->routeIs('admin.properties.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New Property</p>
                            </a>
                        </li>
                        @endcan
                        @can('moderate properties')
                        <li class="nav-item">
                            <a href="{{ route('admin.moderation.index') }}" class="nav-link {{ request()->routeIs('admin.moderation*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Moderation Queue</p>
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
                        <p>Categories</p>
                    </a>
                </li>
                @endcan

                <!-- Locations -->
                @canany(['view cities', 'manage neighborhoods'])
                <li class="nav-item {{ request()->routeIs('admin.cities*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.cities*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-map-marker-alt"></i>
                        <p>
                            Locations
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('view cities')
                        <li class="nav-item">
                            <a href="{{ route('admin.cities.index') }}" class="nav-link {{ request()->routeIs('admin.cities.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cities</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                <!-- Amenities -->
                @can('view amenities')
                <li class="nav-item">
                    <a href="{{ route('admin.amenities.index') }}" class="nav-link {{ request()->routeIs('admin.amenities*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list-check"></i>
                        <p>Amenities</p>
                    </a>
                </li>
                @endcan

                <!-- User Management -->
                @canany(['view users', 'view leads'])
                <li class="nav-header">USER MANAGEMENT</li>
                @endcanany

                @can('view users')
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Users & Agents</p>
                    </a>
                </li>
                @endcan

                @can('view leads')
                <li class="nav-item">
                    <a href="{{ route('admin.leads.index') }}" class="nav-link {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>
                            Leads
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

                <!-- Media -->
                @can('view media')
                <li class="nav-header">MEDIA & CONTENT</li>
                <li class="nav-item">
                    <a href="{{ route('admin.media.index') }}" class="nav-link {{ request()->routeIs('admin.media*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-photo-video"></i>
                        <p>Media Library</p>
                    </a>
                </li>
                @endcan

                <!-- Reports & Analytics -->
                @can('view reports')
                <li class="nav-header">REPORTS & ANALYTICS</li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Reports</p>
                    </a>
                </li>
                @endcan

                <!-- System Settings -->
                @canany(['view settings', 'manage roles', 'manage permissions'])
                <li class="nav-header">SYSTEM</li>
                @endcanany

                @can('view settings')
                <li class="nav-item {{ request()->routeIs('admin.settings*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Settings
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>General Settings</p>
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
                            Roles & Permissions
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('manage roles')
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Roles</p>
                            </a>
                        </li>
                        @endcan
                        @can('manage permissions')
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Permissions</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                <!-- Logout -->
                <li class="nav-header">SESSION</li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
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