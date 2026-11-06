@extends('public.layout')

@section('title', 'News · ' . $siteSettings['site_name'])

@section('content')

<x-public.page-hero
    eyebrow="News"
    :title="$notice->created_at->format('d F Y')"
/>

<section class="home-section">
    <div class="site-container page-narrow">
        <a href="{{ route('public.news') }}" class="news-back"><i class="bi bi-arrow-left"></i> Back to News</a>
        <div class="page-panel page-prose">
            {!! \Mews\Purifier\Facades\Purifier::clean($notice->notice) !!}
        </div>
    </div>
</section>

@endsection
