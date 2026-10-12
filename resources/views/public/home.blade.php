@extends('public.layout')

@section('title', ($page->title ?: $siteSettings['site_name']) . ' · ' . $siteSettings['tagline'])
@section('meta_description', $page->meta_description ?: $siteSettings['tagline'])

@section('content')

<section class="site-hero {{ $page->hero_image ? 'has-image' : '' }}" @if($page->hero_image) style="background-image:url('{{ asset($page->hero_image) }}');" @endif>
    <div class="site-container">
        <div class="hero-inner">
            <span class="eyebrow">Welcome to {{ $siteSettings['site_name'] }}</span>
            <h1>{{ $page->hero_title ?: 'Where every child\'s journey begins with curiosity.' }}</h1>
            <p class="lead">{{ $page->hero_subtitle ?: $siteSettings['tagline'] }}</p>
            <div class="hero-actions">
                <a href="{{ route('public.admissions') }}" class="btn-site btn-site-accent">Start Admissions <i class="bi bi-arrow-right"></i></a>
                <a href="{{ route('public.about') }}" class="btn-site btn-site-outline">Learn More</a>
            </div>
        </div>
    </div>
</section>

<section class="site-section">
    <div class="site-container">
        <div class="stats-row">
            <div class="stat-card">
                <strong>{{ $siteSettings['enrollment_count'] }}+</strong>
                <span>Students</span>
            </div>
            <div class="stat-card">
                <strong>{{ $siteSettings['staff_count'] }}+</strong>
                <span>Qualified Staff</span>
            </div>
            <div class="stat-card">
                <strong>{{ $siteSettings['clubs_count'] }}+</strong>
                <span>Clubs & Activities</span>
            </div>
            <div class="stat-card">
                <strong>{{ $siteSettings['subjects_count'] }}+</strong>
                <span>Subjects Offered</span>
            </div>
        </div>
    </div>
</section>

@if ($page->body)
<section class="site-section alt">
    <div class="site-container" style="max-width:760px;">
        {!! $page->body !!}
    </div>
</section>
@endif

@if ($programs->count() > 0)
<section class="site-section">
    <div class="site-container">
        <div class="section-heading">
            <h2>Programs for every age</h2>
            <p>From first steps to primary school, a program built around how your child actually learns.</p>
        </div>
        <div class="card-grid">
            @foreach ($programs as $program)
            <div class="program-card">
                <div class="thumb" @if($program->image) style="background-image:url('{{ asset($program->image) }}');" @endif></div>
                <div class="body">
                    <h3>{{ $program->name }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($program->description, 100) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($newsItems->count() > 0)
<section class="site-section alt">
    <div class="site-container">
        <div class="section-heading">
            <h2>Latest news</h2>
            <p>What's happening around the school this term.</p>
        </div>
        @foreach ($newsItems as $notice)
        <div class="news-card">
            <div>
                <div class="date">{{ $notice->created_at->format('d M Y') }}</div>
                <div class="excerpt">{!! \Illuminate\Support\Str::limit(strip_tags($notice->notice), 180) !!}</div>
                <a href="{{ route('public.news.show', ['notice' => $notice->id]) }}" style="font-weight:600;font-size:.875rem;">Read more <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

<section class="site-section dark">
    <div class="site-container" style="text-align:center;">
        <h2>Ready to join our school community?</h2>
        <p style="max-width:520px;margin:0 auto 2rem;color:rgba(255,255,255,.75);">Book a visit, meet our teachers, and see the classrooms for yourself.</p>
        <a href="{{ route('public.contact') }}" class="btn-site btn-site-accent">Get in Touch</a>
    </div>
</section>

@endsection
