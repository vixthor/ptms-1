<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>PTMS &middot; Prime Torch Bearers Montessori School</title>

    <link rel="shortcut icon" href="{{asset('favicon_io/favicon.ico')}}">
    <link rel="shortcut icon" sizes="16x16" href="{{asset('favicon_io/favicon-16x16.png')}}">
    <link rel="shortcut icon" sizes="32x32" href="{{asset('favicon_io/favicon-32x32.png')}}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('sikhi/logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('sikhi/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('sikhi/logo.png') }}">

    <!-- Scripts -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <!-- <script src="{{ asset('js/app.js') }}" defer></script> -->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <script src="{{ asset("js/jquery-3.6.0.min.js") }}"></script>

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : 1 }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar sticky-top navbar-expand-md navbar-light bg-white border-btm-e6">
            <div class="container">
                @auth
                <button class="sidebar-toggle d-lg-none" type="button" id="sidebarToggle" aria-label="{{ __('Toggle menu') }}" aria-controls="appSidebar" aria-expanded="false">
                    <i class="bi bi-list"></i>
                </button>
                @endauth
                <a class="navbar-brand" href="{{ route('dashboard') }}">
                    <i class="bi bi-mortarboard-fill"></i> PTMS
                </a>

                <div class="d-none d-lg-flex align-items-center flex-grow-1" id="navbarTopContent">
                    <!-- Left Side Of Navbar -->
                    @auth
                        @php
                            $latest_school_session = \App\Models\SchoolSession::latest()->first();
                            $current_school_session_name = null;
                            if($latest_school_session){
                                $current_school_session_name = $latest_school_session->session_name;
                            }
                        @endphp
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            @if (session()->has('browse_session_name') && session('browse_session_name') !== $current_school_session_name)
                                <a class="nav-link text-danger disabled" href="#" tabindex="-1" aria-disabled="true"><i class="bi bi-exclamation-diamond-fill me-2"></i> Browsing as Academic Session {{session('browse_session_name')}}</a>
                            @elseif(\App\Models\SchoolSession::latest()->count() > 0)
                                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true"><i class="bi bi-calendar3 me-2"></i>Current Academic Session {{$current_school_session_name}}</a>
                            @else
                                <a class="nav-link text-danger disabled" href="#" tabindex="-1" aria-disabled="true"><i class="bi bi-exclamation-diamond-fill me-2"></i> Create an Academic Session.</a>
                            @endif
                        </li>
                    </ul>
                    @endauth
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-bold" style="width:32px;height:32px;background:linear-gradient(135deg, var(--brand-500), var(--brand-800));font-size:.8rem;">{{ strtoupper(substr(Auth::user()->first_name,0,1) . substr(Auth::user()->last_name,0,1)) }}</span>
                                    <span class="d-none d-lg-inline">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                                    <span class="badge bg-light text-dark">{{ ucfirst(Auth::user()->role) }}</span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('password.edit') }}">
                                        <i class="bi bi-key me-2"></i> Change Password
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}">
                                        <i class="bi bi-door-open me-2"></i>Logout
                                    </a>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>

                @guest
                <div class="d-lg-none ms-auto">
                    @if (Route::has('login'))
                        <a class="nav-link" href="">{{ __('Login') }}</a>
                    @endif
                </div>
                @endguest
            </div>
        </nav>
        <div id="sidebarBackdrop" class="sidebar-backdrop"></div>
        <main>
            @yield('content')
        </main>
    </div>

    <div id="watermark">
        <p>PTMS</p>
    </div>

    <script>
        $(function () {
            var $sidebar = $('.app-sidebar').first();
            var $backdrop = $('#sidebarBackdrop');
            var $toggle = $('#sidebarToggle');

            function closeSidebar() {
                $sidebar.removeClass('mobile-open');
                $backdrop.removeClass('show');
                $toggle.attr('aria-expanded', 'false');
            }

            $toggle.on('click', function () {
                var isOpen = $sidebar.toggleClass('mobile-open').hasClass('mobile-open');
                $backdrop.toggleClass('show', isOpen);
                $toggle.attr('aria-expanded', isOpen ? 'true' : 'false');
            });

            $backdrop.on('click', closeSidebar);
            $sidebar.on('click', 'a.nav-link:not([data-bs-toggle="collapse"])', closeSidebar);
            $(window).on('resize', function () {
                if ($(window).width() >= 992) closeSidebar();
            });
        });
    </script>

</body>
</html>
