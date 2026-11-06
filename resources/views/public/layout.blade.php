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

    @php
        $navLinks = [
            ['label' => 'Home', 'route' => 'public.home', 'active' => 'public.home'],
            ['label' => 'About', 'route' => 'public.about', 'active' => 'public.about'],
            ['label' => 'Contact', 'route' => 'public.contact', 'active' => 'public.contact'],
            ['label' => 'Academics', 'route' => 'public.academics', 'active' => 'public.academics'],
            ['label' => 'Gallery', 'route' => 'public.gallery', 'active' => 'public.gallery'],
        ];
    @endphp

    <a href="#main" class="skip-link">Skip to content</a>

    <header class="site-header">
        <div class="site-container row-inner">
            <a href="{{ route('public.home') }}" class="site-brand">
                {{ $siteSettings['site_name'] ?? 'PTMS' }}
            </a>

            <nav class="site-nav" aria-label="Main">
                <ul>
                    @foreach ($navLinks as $link)
                    <li><a href="{{ route($link['route']) }}" @class(['active' => request()->routeIs($link['active'])]) @if(request()->routeIs($link['active'])) aria-current="page" @endif>{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
                <div class="site-header-actions">
                    <a href="{{ route('login') }}" class="btn-site btn-site-accent">Parent login</a>
                </div>
            </nav>

            <button type="button" class="site-nav-toggle" id="siteNavToggle" aria-label="Open menu" aria-controls="siteDrawer" aria-expanded="false" aria-haspopup="dialog">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
        </div>
    </header>

    <dialog class="site-drawer" id="siteDrawer" aria-label="Site menu">
        <div class="site-drawer-panel">
            <div class="site-drawer-head">
                <a href="{{ route('public.home') }}" class="site-brand">{{ $siteSettings['site_name'] ?? 'PTMS' }}</a>
                <button type="button" class="site-drawer-close" data-drawer-close aria-label="Close menu">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <nav aria-label="Mobile">
                <ul class="site-drawer-links">
                    @foreach ($navLinks as $link)
                    <li>
                        <a href="{{ route($link['route']) }}" @class(['active' => request()->routeIs($link['active'])]) @if(request()->routeIs($link['active'])) aria-current="page" @endif>
                            {{ $link['label'] }} <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </nav>
            <div class="site-drawer-actions">
                <a href="{{ route('public.admissions') }}" class="btn-site btn-site-block">Apply now</a>
                <a href="{{ route('login') }}" class="btn-site btn-site-outline">Parent login</a>
            </div>
        </div>
    </dialog>

    <main id="main">

        @yield('content')
    </main>

    <footer class="site-footer">
        <svg class="footer-wave" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 0H1440V58C1180 100 860 92 560 52C340 22 160 12 0 18Z" />
        </svg>
        <div class="site-container">
            <div class="footer-cta">
                <div>
                    <a href="{{ route('public.home') }}" class="footer-brand">
                        <i class="bi bi-box"></i> {{ $siteSettings['site_name'] ?? 'PTMS' }}
                    </a>
                    <h2>Give your child a great start</h2>
                </div>
                <div class="footer-cta-actions">
                    <a href="{{ route('public.admissions') }}" class="btn-site btn-site-framed"><strong>Apply now</strong></a>
                    <a href="{{ route('public.contact') }}" class="btn-site btn-site-framed">Book a visit</a>
                </div>
            </div>

            <div class="footer-grid">
                <div>
                    <h5>Contact</h5>
                    <ul>
                        @if (!empty($siteSettings['address']))
                        <li>{{ $siteSettings['address'] }}</li>
                        @endif
                        @if (!empty($siteSettings['phone']))
                        <li><a href="tel:{{ $siteSettings['phone'] }}">{{ $siteSettings['phone'] }}</a></li>
                        @endif
                        @if (!empty($siteSettings['email']))
                        <li><a href="mailto:{{ $siteSettings['email'] }}">{{ $siteSettings['email'] }}</a></li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h5>Quick links</h5>
                    <ul>
                        <li><a href="{{ route('public.admissions') }}">Admissions</a></li>
                        <li><a href="{{ route('public.admissions') }}#fees">Fees &amp; term dates</a></li>
                        <li><a href="{{ route('public.news') }}">News</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Social media</h5>
                    <ul>
                        @if (!empty($siteSettings['facebook_url']))
                        <li><a href="{{ $siteSettings['facebook_url'] }}" target="_blank" rel="noopener">Facebook</a></li>
                        @endif
                        @if (!empty($siteSettings['instagram_url']))
                        <li><a href="{{ $siteSettings['instagram_url'] }}" target="_blank" rel="noopener">Instagram</a></li>
                        @endif
                        @if (!empty($siteSettings['whatsapp']))
                        <li><a href="https://wa.me/{{ preg_replace('/\D/', '', $siteSettings['whatsapp']) }}" target="_blank" rel="noopener">WhatsApp</a></li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h5>Legal</h5>
                    <ul>
                        <li><span>Privacy Policy</span></li>
                        <li><span>Cookie Agreement</span></li>
                        <li><span>Terms of Service</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    @php
        $contactPhone = $siteSettings['phone'] ?? '';
        $contactWhatsapp = preg_replace('/\D/', '', $siteSettings['whatsapp'] ?? '');
        $contactOptions = array_values(array_filter([
            $contactWhatsapp !== '' ? ['label' => 'WhatsApp us', 'icon' => 'bi-whatsapp', 'class' => 'is-whatsapp', 'href' => 'https://wa.me/'.$contactWhatsapp, 'external' => true] : null,
            $contactPhone !== '' ? ['label' => 'Call us', 'icon' => 'bi-telephone-fill', 'class' => 'is-phone', 'href' => 'tel:'.preg_replace('/[^\d+]/', '', $contactPhone), 'external' => false] : null,
        ]));
    @endphp

    @if (count($contactOptions) === 1)
    <a href="{{ $contactOptions[0]['href'] }}" class="contact-fab-toggle contact-fab-single" aria-label="{{ $contactOptions[0]['label'] }}" @if($contactOptions[0]['external']) target="_blank" rel="noopener" @endif>
        <i class="bi {{ $contactOptions[0]['icon'] }}" aria-hidden="true"></i>
    </a>
    @elseif (count($contactOptions) > 1)
    <div class="contact-fab" id="contactFab">
        <ul class="contact-fab-menu" id="contactFabMenu" hidden>
            @foreach ($contactOptions as $option)
            <li>
                <a href="{{ $option['href'] }}" class="contact-fab-option {{ $option['class'] }}" @if($option['external']) target="_blank" rel="noopener" @endif>
                    <span>{{ $option['label'] }}</span>
                    <i class="bi {{ $option['icon'] }}" aria-hidden="true"></i>
                </a>
            </li>
            @endforeach
        </ul>
        <button type="button" class="contact-fab-toggle" id="contactFabToggle" aria-expanded="false" aria-controls="contactFabMenu" aria-label="Contact us">
            <i class="bi bi-chat-dots-fill contact-fab-open" aria-hidden="true"></i>
            <i class="bi bi-x-lg contact-fab-close" aria-hidden="true"></i>
        </button>
    </div>
    @endif

    <script>
        (function () {
            const fab = document.getElementById('contactFab');
            if (!fab) {
                return;
            }
            const toggle = document.getElementById('contactFabToggle');
            const menu = document.getElementById('contactFabMenu');

            function setOpen(isOpen) {
                menu.hidden = !isOpen;
                toggle.setAttribute('aria-expanded', String(isOpen));
                fab.classList.toggle('is-open', isOpen);
            }

            toggle.addEventListener('click', function () { setOpen(menu.hidden); });
            document.addEventListener('click', function (event) {
                if (!fab.contains(event.target)) { setOpen(false); }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !menu.hidden) {
                    setOpen(false);
                    toggle.focus();
                }
            });
        })();

        (function () {
            const toggle = document.getElementById('siteNavToggle');
            const drawer = document.getElementById('siteDrawer');
            if (!toggle || !drawer || typeof drawer.showModal !== 'function') {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            let closeTimer = null;

            function openDrawer() {
                clearTimeout(closeTimer);
                drawer.showModal();
                document.documentElement.classList.add('drawer-locked');
                toggle.setAttribute('aria-expanded', 'true');
                requestAnimationFrame(function () { drawer.classList.add('is-open'); });
            }

            function closeDrawer() {
                if (!drawer.open) {
                    return;
                }
                drawer.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                closeTimer = setTimeout(function () {
                    drawer.close();
                    document.documentElement.classList.remove('drawer-locked');
                    toggle.focus();
                }, reduceMotion.matches ? 0 : 250);
            }

            toggle.addEventListener('click', openDrawer);

            drawer.addEventListener('cancel', function (event) {
                event.preventDefault();
                closeDrawer();
            });

            drawer.addEventListener('click', function (event) {
                if (event.target === drawer || event.target.closest('[data-drawer-close]')) {
                    closeDrawer();
                }
            });

            window.matchMedia('(min-width: 901px)').addEventListener('change', function (event) {
                if (event.matches) {
                    closeDrawer();
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
