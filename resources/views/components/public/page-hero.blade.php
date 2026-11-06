@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'image' => null,
    'showMedia' => false,
])

@php
    $hasMedia = $image || $showMedia;
@endphp

<section {{ $attributes->class(['home-hero', 'is-single' => ! $hasMedia]) }}>
    <div class="site-container home-hero-inner">
        <div class="home-hero-copy">
            @if ($eyebrow)
            <span class="home-hero-eyebrow">{{ $eyebrow }}</span>
            @endif
            <h1>{{ $title }}</h1>
            @if ($subtitle)
            <p>{{ $subtitle }}</p>
            @endif
            @if (! $slot->isEmpty())
            <div class="home-hero-actions">
                {{ $slot }}
            </div>
            @endif
        </div>
        @if ($hasMedia)
        <div @class(['home-hero-media', 'is-empty' => ! $image])>
            @if ($image)
            <img src="{{ asset($image) }}" alt="">
            @endif
        </div>
        @endif
    </div>
</section>
