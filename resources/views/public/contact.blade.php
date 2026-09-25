@extends('public.layout')

@section('title', 'Contact · ' . $siteSettings['site_name'])
@section('meta_description', 'Get in touch with ' . $siteSettings['site_name'])

@section('content')

<section class="site-hero">
    <div class="site-container">
        <div class="hero-inner">
            <span class="eyebrow">Contact</span>
            <h1>We'd love to hear from you.</h1>
            <p class="lead">{{ $page->hero_subtitle ?: 'Questions about admissions, a tour, or anything else — reach out any time.' }}</p>
        </div>
    </div>
</section>

<section class="site-section">
    <div class="site-container">
        <div class="contact-grid">
            <div>
                @if ($page->body)
                    {!! $page->body !!}
                @endif

                @if (!empty($siteSettings['phone']))
                <div class="contact-item">
                    <span class="icon"><i class="bi bi-telephone"></i></span>
                    <div>
                        <h4>Phone</h4>
                        <p>{{ $siteSettings['phone'] }}</p>
                    </div>
                </div>
                @endif

                @if (!empty($siteSettings['whatsapp']))
                <div class="contact-item">
                    <span class="icon"><i class="bi bi-whatsapp"></i></span>
                    <div>
                        <h4>WhatsApp</h4>
                        <p><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp']) }}" target="_blank" rel="noopener">{{ $siteSettings['whatsapp'] }}</a></p>
                    </div>
                </div>
                @endif

                @if (!empty($siteSettings['email']))
                <div class="contact-item">
                    <span class="icon"><i class="bi bi-envelope"></i></span>
                    <div>
                        <h4>Email</h4>
                        <p>{{ $siteSettings['email'] }}</p>
                    </div>
                </div>
                @endif

                @if (!empty($siteSettings['address']))
                <div class="contact-item">
                    <span class="icon"><i class="bi bi-geo-alt"></i></span>
                    <div>
                        <h4>Address</h4>
                        <p>{{ $siteSettings['address'] }}</p>
                    </div>
                </div>
                @endif
            </div>
            <div style="background:var(--page-bg);border-radius:var(--radius-lg);min-height:320px;display:flex;align-items:center;justify-content:center;color:var(--ink-400);">
                <div style="text-align:center;">
                    <i class="bi bi-map" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0" style="font-size:.875rem;">Map coming soon</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
