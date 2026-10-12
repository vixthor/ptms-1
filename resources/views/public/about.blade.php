@extends('public.layout')

@section('title', 'About · ' . $siteSettings['site_name'])
@section('meta_description', $page->meta_description ?: 'Learn more about our school.')

@section('content')

<section class="site-hero {{ $page->hero_image ? 'has-image' : '' }}" @if($page->hero_image) style="background-image:url('{{ asset($page->hero_image) }}');" @endif>
    <div class="site-container">
        <div class="hero-inner">
            <span class="eyebrow">About Us</span>
            <h1>{{ $page->hero_title ?: 'Our story, our mission, our people.' }}</h1>
            <p class="lead">{{ $page->hero_subtitle ?: 'Getting to know the community behind ' . $siteSettings['site_name'] }}</p>
        </div>
    </div>
</section>

<section class="site-section">
    <div class="site-container" style="max-width:760px;">
        @if ($page->body)
            {!! $page->body !!}
        @else
            <p class="text-secondary">This page hasn't been written yet — add content from the Website admin section.</p>
        @endif
    </div>
</section>

@endsection
