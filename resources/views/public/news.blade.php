@extends('public.layout')

@section('title', 'News · ' . $siteSettings['site_name'])
@section('meta_description', 'News and announcements from ' . $siteSettings['site_name'])

@section('content')

<section class="site-hero">
    <div class="site-container">
        <div class="hero-inner">
            <span class="eyebrow">News</span>
            <h1>What's happening at {{ $siteSettings['site_name'] }}.</h1>
        </div>
    </div>
</section>

<section class="site-section">
    <div class="site-container" style="max-width:760px;">
        @if ($newsItems->count() > 0)
            @foreach ($newsItems as $notice)
            <div class="news-card">
                <div>
                    <div class="date">{{ $notice->created_at->format('d M Y') }}</div>
                    <div class="excerpt">{!! \Illuminate\Support\Str::limit(strip_tags($notice->notice), 220) !!}</div>
                    <a href="{{ route('public.news.show', ['notice' => $notice->id]) }}" style="font-weight:600;font-size:.875rem;">Read more <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            @endforeach
            <div class="mt-4">
                {{ $newsItems->links() }}
            </div>
        @else
        <p class="text-secondary text-center py-5">No news posted yet — check back soon.</p>
        @endif
    </div>
</section>

@endsection
