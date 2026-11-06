@extends('public.layout')

@section('title', 'News · ' . $siteSettings['site_name'])
@section('meta_description', 'News and announcements from ' . $siteSettings['site_name'])

@section('content')

<x-public.page-hero
    eyebrow="News"
    :title="'What\'s happening at ' . $siteSettings['site_name'] . '.'"
/>

<section class="home-section">
    <div class="site-container page-narrow">
        @if ($newsItems->count() > 0)
            @foreach ($newsItems as $notice)
            <article class="news-card">
                <div class="date">{{ $notice->created_at->format('d M Y') }}</div>
                <div class="excerpt">{{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags($notice->notice)), 220) }}</div>
                <a href="{{ route('public.news.show', ['notice' => $notice->id]) }}" class="news-more">Read more <i class="bi bi-arrow-right"></i></a>
            </article>
            @endforeach
            <div class="page-pagination">
                {{ $newsItems->links() }}
            </div>
        @else
        <p class="page-empty">No news posted yet. Check back soon.</p>
        @endif
    </div>
</section>

@endsection
