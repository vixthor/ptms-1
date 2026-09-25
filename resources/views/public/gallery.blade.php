@extends('public.layout')

@section('title', 'Gallery · ' . $siteSettings['site_name'])
@section('meta_description', 'Photos from around ' . $siteSettings['site_name'])

@section('content')

<section class="site-hero">
    <div class="site-container">
        <div class="hero-inner">
            <span class="eyebrow">Gallery</span>
            <h1>Life at {{ $siteSettings['site_name'] }}.</h1>
        </div>
    </div>
</section>

<section class="site-section">
    <div class="site-container">
        @if ($photos->count() > 0)
        <div class="gallery-grid">
            @foreach ($photos as $photo)
            <div class="item" style="background-image:url('{{ asset($photo->image) }}');" title="{{ $photo->caption }}"></div>
            @endforeach
        </div>
        @else
        <p class="text-secondary text-center py-5">Photos will be added here soon.</p>
        @endif
    </div>
</section>

@endsection
