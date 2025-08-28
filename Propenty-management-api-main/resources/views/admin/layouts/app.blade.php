<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.dashboard')) - {{ config('app.name', __('admin.admin_panel')) }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Arabic Font Support -->
    @if(app()->getLocale() === 'ar')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @endif
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE Theme -->
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.0/css/OverlayScrollbars.min.css">
    
    <!-- RTL CSS for Arabic -->
    @if(app()->getLocale() === 'ar')
    <link rel="stylesheet" href="{{ asset('css/rtl-fixes.css') }}">
    @endif
    
    @stack('css')
</head>
<body class="hold-transition sidebar-mini layout-fixed{{ app()->getLocale() === 'ar' ? ' rtl-layout' : '' }}">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <!-- Navbar -->
    @include('admin.partials.navbar')

    <!-- Main Sidebar Container -->
    @include('admin.partials.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('content-header', __('admin.dashboard'))</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb {{ app()->getLocale() === 'ar' ? 'float-sm-left' : 'float-sm-right' }}">
                            @yield('breadcrumb')
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Please check the form below for errors:</strong>
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

                @yield('content')
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    @include('admin.partials.footer')

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.0/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    @if(app()->getLocale() === 'ar')
    // Enhanced RTL layout implementation for Arabic
    $(document).ready(function() {
        // Force RTL class application
        $('html').addClass('rtl-layout').attr('dir', 'rtl');
        $('body').addClass('rtl-body rtl-layout');
        
        // Function to apply RTL sidebar positioning
        function applyRTLSidebar() {
            $('.main-sidebar').css({
                'right': '0',
                'left': 'auto',
                'position': 'fixed',
                'z-index': '1038'
            });
            
            // Adjust content wrapper based on sidebar state
            if ($('body').hasClass('sidebar-collapse')) {
                $('.content-wrapper').css({
                    'margin-right': '0',
                    'margin-left': '0'
                });
            } else if ($('body').hasClass('sidebar-mini')) {
                $('.content-wrapper').css({
                    'margin-right': '4.6rem',
                    'margin-left': '0'
                });
            } else {
                $('.content-wrapper').css({
                    'margin-right': '250px',
                    'margin-left': '0'
                });
            }
        }
        
        // Initial application
        applyRTLSidebar();
        
        // Handle AdminLTE sidebar toggle with enhanced RTL support
        $(document).off('click', '[data-widget="pushmenu"]');
        $(document).on('click', '[data-widget="pushmenu"]', function(e) {
            e.preventDefault();
            
            // Let AdminLTE handle the toggle first
            setTimeout(function() {
                // Then apply our RTL positioning
                applyRTLSidebar();
                
                // For mobile, handle the overlay
                if ($(window).width() <= 991) {
                    if ($('body').hasClass('sidebar-open')) {
                        $('.main-sidebar').css('transform', 'translateX(0)');
                    } else {
                        $('.main-sidebar').css('transform', 'translateX(100%)');
                    }
                }
            }, 50);
        });
        
        // Handle window resize for responsive behavior
        $(window).on('resize', function() {
            setTimeout(function() {
                applyRTLSidebar();
            }, 100);
        });
        
        // Override AdminLTE layout fixes for RTL
        if (typeof AdminLTE !== 'undefined') {
            // Store original layout fix function
            var originalLayoutFix = AdminLTE.Layout.fixLayoutHeight;
            
            // Override with RTL-aware version
            AdminLTE.Layout.fixLayoutHeight = function(extra) {
                originalLayoutFix.call(this, extra);
                applyRTLSidebar();
            };
        }
        
        // Force RTL on navigation elements
        $('.nav-sidebar .nav-link').css('text-align', 'right');
        $('.nav-sidebar .nav-icon').css({
            'float': 'right',
            'margin-left': '0.5rem',
            'margin-right': '0'
        });
        
        // Enhanced mobile handling
        if ($(window).width() <= 991) {
            $('.main-sidebar').css({
                'transform': $('body').hasClass('sidebar-open') ? 'translateX(0)' : 'translateX(100%)',
                'transition': 'transform 0.3s ease-in-out'
            });
        }
    });
    @endif
</script>

@stack('scripts')
</body>
</html>