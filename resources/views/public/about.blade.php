@extends('public.layout')

@section('title', 'About · ' . $siteSettings['site_name'])
@section('meta_description', $page->meta_description ?: 'Learn more about our school.')

@section('content')

<x-public.page-hero
    eyebrow="About us"
    :title="$page->hero_title ?: 'Our story, our mission, our people.'"
    :subtitle="$page->hero_subtitle ?: 'Getting to know the community behind ' . $siteSettings['site_name']"
    :image="$page->hero_image"
/>

<section class="home-section">
    <div class="site-container">
        <div class="page-panel page-prose">
            @if ($page->body)
                {!! $page->body !!}
            @else
                <p>This page hasn't been written yet. Add content from the Website admin section.</p>
            @endif
        </div>
    </div>
</section>

<section class="home-section page-cta">
    <div class="site-container">
        <h2 class="home-title">Come and see for yourself</h2>
        <span class="home-rule"></span>
        <p>Book a visit to meet our teachers and walk through the classrooms.</p>
        <a href="{{ route('public.contact') }}" class="btn-site btn-site-block btn-site-raised">Book a visit</a>
    </div>
</section>

@endsection
