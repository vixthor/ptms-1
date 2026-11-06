@extends('public.layout')

@section('title', 'Contact · ' . $siteSettings['site_name'])
@section('meta_description', 'Get in touch with ' . $siteSettings['site_name'])

@section('content')

<x-public.page-hero
    eyebrow="Contact"
    :title="$page->hero_title ?: 'We\'d love to hear from you.'"
    :subtitle="$page->hero_subtitle ?: 'Questions about admissions, a tour, or anything else? Reach out any time.'"
    :image="$page->hero_image"
/>

<section class="home-section">
    <div class="site-container contact-grid">
        <div class="page-panel">
            @if ($page->body)
            <div class="page-prose contact-intro">
                {!! $page->body !!}
            </div>
            @endif

            @if (!empty($siteSettings['phone']))
            <div class="contact-item">
                <span class="icon"><i class="bi bi-telephone"></i></span>
                <div>
                    <h4>Phone</h4>
                    <p><a href="tel:{{ $siteSettings['phone'] }}">{{ $siteSettings['phone'] }}</a></p>
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
                    <p><a href="mailto:{{ $siteSettings['email'] }}">{{ $siteSettings['email'] }}</a></p>
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

        <div class="contact-map">
            <i class="bi bi-map"></i>
            <p>Map coming soon</p>
        </div>
    </div>
</section>

@endsection
