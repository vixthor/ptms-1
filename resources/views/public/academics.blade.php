@extends('public.layout')

@section('title', 'Academics · ' . $siteSettings['site_name'])
@section('meta_description', 'Academic programs offered at ' . $siteSettings['site_name'])

@section('content')

<x-public.page-hero
    eyebrow="Academics"
    title="Programs built around how children grow."
    subtitle="A curriculum that meets each child at their stage, from early years through primary."
/>

<section class="home-section">
    <div class="site-container">
        @if ($programs->count() > 0)
        <div class="home-cards">
            @foreach ($programs as $program)
            <article class="home-card">
                <div class="home-card-thumb" @if($program->image) style="background-image:url('{{ asset($program->image) }}');" @endif></div>
                <div class="home-card-body">
                    <h3>{{ $program->name }}</h3>
                    <p>{{ $program->description }}</p>
                    <a href="{{ route('public.admissions') }}" class="btn-site btn-site-block">Start application</a>
                </div>
            </article>
            @endforeach
        </div>
        @else
        <p class="page-empty">Academic programs will be listed here soon.</p>
        @endif
    </div>
</section>

@if ($activities->count() > 0)
<section class="home-section" id="activities">
    <div class="site-container">
        <div class="page-heading">
            <h2 class="home-title">Extra-curricular activities</h2>
            <span class="home-rule"></span>
            <p>Beyond the classroom, children discover new talents and skills through clubs, sport and the arts.</p>
        </div>
        <div class="activity-grid">
            @foreach ($activities as $activity)
            <article @class(['activity-card', 'has-image' => $activity->image])>
                @if ($activity->image)
                <img src="{{ asset($activity->image) }}" alt="" loading="lazy">
                @endif
                <div class="activity-card-body">
                    <h3>{{ $activity->name }}</h3>
                    @if ($activity->description)
                    <p>{{ $activity->description }}</p>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
