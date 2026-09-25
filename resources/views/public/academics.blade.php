@extends('public.layout')

@section('title', 'Academics · ' . $siteSettings['site_name'])
@section('meta_description', 'Academic programs offered at ' . $siteSettings['site_name'])

@section('content')

<section class="site-hero">
    <div class="site-container">
        <div class="hero-inner">
            <span class="eyebrow">Academics</span>
            <h1>Programs built around how children grow.</h1>
            <p class="lead">A curriculum that meets each child at their stage — from early years through primary.</p>
        </div>
    </div>
</section>

<section class="site-section">
    <div class="site-container">
        @if ($programs->count() > 0)
        <div class="card-grid">
            @foreach ($programs as $program)
            <div class="program-card">
                <div class="thumb" @if($program->image) style="background-image:url('{{ asset($program->image) }}');" @endif></div>
                <div class="body">
                    <h3>{{ $program->name }}</h3>
                    <p>{{ $program->description }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-secondary text-center py-5">Academic programs will be listed here soon.</p>
        @endif
    </div>
</section>

@endsection
