<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $siteSettings['site_name'] ?? 'PTMS')</title>
    <meta name="description" content="@yield('meta_description', $siteSettings['tagline'] ?? '')">

    <link rel="shortcut icon" href="{{asset('favicon_io/favicon.ico')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('sikhi/logo.png') }}">

    <link href="{{ asset('css/site.css') }}?v={{ file_exists(public_path('css/site.css')) ? filemtime(public_path('css/site.css')) : 1 }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="site-body">

    <header class="site-header">
        <div class="site-container row-inner">
            <a href="{{ route('public.home') }}" class="site-brand">
                <span class="mark"><i class="bi bi-mortarboard-fill"></i></span>
                {{ $siteSettings['site_name'] ?? 'PTMS' }}
            </a>

            <nav class="site-nav" id="siteNav">
                <ul>
                    <li><a href="{{ route('public.home') }}" class="{{ request()->routeIs('public.home') ? 'active' : '' }}">Home</a></li>
                    <li><a href="{{ route('public.about') }}" class="{{ request()->routeIs('public.about') ? 'active' : '' }}">About</a></li>
                    <li><a href="{{ route('public.academics') }}" class="{{ request()->routeIs('public.academics') ? 'active' : '' }}">Academics</a></li>
                    <li><a href="{{ route('public.admissions') }}" class="{{ request()->routeIs('public.admissions') ? 'active' : '' }}">Admissions</a></li>
                    <li><a href="{{ route('public.news') }}" class="{{ request()->routeIs('public.news*') ? 'active' : '' }}">News</a></li>
                    <li><a href="{{ route('public.gallery') }}" class="{{ request()->routeIs('public.gallery') ? 'active' : '' }}">Gallery</a></li>
                    <li><a href="{{ route('public.contact') }}" class="{{ request()->routeIs('public.contact') ? 'active' : '' }}">Contact</a></li>
                </ul>
                <div class="site-header-actions">
                    <a href="{{ route('login') }}" class="btn-site btn-site-outline"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                    <a href="{{ route('public.admissions') }}" class="btn-site btn-site-accent">Apply Now</a>
                </div>
            </nav>

            <button type="button" class="site-nav-toggle" id="siteNavToggle" aria-label="Toggle menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-container">
            <div class="footer-grid">
                <div>
                    <a href="{{ route('public.home') }}" class="site-brand" style="color:#fff;margin-bottom:1rem;">
                        <span class="mark"><i class="bi bi-mortarboard-fill"></i></span>
                        {{ $siteSettings['site_name'] ?? 'PTMS' }}
                    </a>
                    <p>{{ $siteSettings['tagline'] ?? '' }}</p>
                    <div class="socials">
                        @if (!empty($siteSettings['facebook_url']))
                        <a href="{{ $siteSettings['facebook_url'] }}" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
                        @endif
                        @if (!empty($siteSettings['twitter_url']))
                        <a href="{{ $siteSettings['twitter_url'] }}" target="_blank" rel="noopener"><i class="bi bi-twitter-x"></i></a>
                        @endif
                        @if (!empty($siteSettings['instagram_url']))
                        <a href="{{ $siteSettings['instagram_url'] }}" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
                        @endif
                    </div>
                </div>
                <div>
                    <h5>Explore</h5>
                    <ul>
                        <li><a href="{{ route('public.about') }}">About</a></li>
                        <li><a href="{{ route('public.academics') }}">Academics</a></li>
                        <li><a href="{{ route('public.admissions') }}">Admissions</a></li>
                        <li><a href="{{ route('public.gallery') }}">Gallery</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Community</h5>
                    <ul>
                        <li><a href="{{ route('public.news') }}">News</a></li>
                        <li><a href="{{ route('public.contact') }}">Contact</a></li>
                        <li><a href="{{ route('login') }}">Portal Login</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Contact</h5>
                    <ul>
                        @if (!empty($siteSettings['phone']))
                        <li><i class="bi bi-telephone me-2"></i>{{ $siteSettings['phone'] }}</li>
                        @endif
                        @if (!empty($siteSettings['email']))
                        <li><i class="bi bi-envelope me-2"></i>{{ $siteSettings['email'] }}</li>
                        @endif
                        @if (!empty($siteSettings['address']))
                        <li><i class="bi bi-geo-alt me-2"></i>{{ $siteSettings['address'] }}</li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="bottom-bar">
                <span>&copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'PTMS' }}. All rights reserved.</span>
                <span>Built with care for our school community.</span>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('siteNavToggle').addEventListener('click', function () {
            document.getElementById('siteNav').classList.toggle('mobile-open');
        });
    </script>
    @stack('scripts')
</body>
</html>
