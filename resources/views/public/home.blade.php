@extends('public.layout')

@section('title', ($page->title ?: $siteSettings['site_name']) . ' · ' . $siteSettings['tagline'])
@section('meta_description', $page->meta_description ?: $siteSettings['tagline'])

@php
    $features = [
        ['icon' => 'bi-stopwatch', 'title' => 'Montessori learning', 'text' => 'Children learn by doing, with hands-on materials that build focus, independence and a love of learning.'],
        ['icon' => 'bi-tag-fill', 'title' => 'Extra-curricular activities', 'text' => 'Sports, scouts, art, theatre, music and quiz help children discover new talents and skills.'],
        ['icon' => 'bi-rulers', 'title' => 'Leadership training', 'text' => 'Class duties, clubs and public speaking help our pupils grow into confident young leaders.'],
    ];

    $statistics = collect([
        ['value' => (int) $siteSettings['enrollment_count'], 'label' => 'Students'],
        ['value' => (int) $siteSettings['staff_count'], 'label' => 'Qualified staff'],
        ['value' => (int) $siteSettings['clubs_count'], 'label' => 'Clubs & activities'],
        ['value' => (int) $siteSettings['subjects_count'], 'label' => 'Subjects offered'],
    ])->filter(fn (array $statistic): bool => $statistic['value'] > 0);

    $staff = [
        ['name' => '[Staff name]', 'role' => 'Head of School'],
        ['name' => '[Staff name]', 'role' => 'Nursery Class Teacher'],
        ['name' => '[Staff name]', 'role' => 'Primary Class Teacher'],
        ['name' => '[Staff name]', 'role' => 'Admissions Officer'],
    ];

    $testimonials = [
        ['name' => '[Parent name]', 'role' => 'Parent, Nursery', 'quote' => '[Add a short quote from a current parent here: what has changed for their child since joining Prime Torchbearers.]'],
        ['name' => '[Parent name]', 'role' => 'Parent, Primary', 'quote' => '[Add a short quote from a current parent here: what has changed for their child since joining Prime Torchbearers.]'],
        ['name' => '[Parent name]', 'role' => 'Parent, Creche', 'quote' => '[Add a short quote from a current parent here: what has changed for their child since joining Prime Torchbearers.]'],
    ];
@endphp

@section('content')

<x-public.page-hero
    :title="$page->hero_title ?: $siteSettings['tagline']"
    :subtitle="$page->hero_subtitle"
    :image="$page->hero_image"
    :show-media="true"
>
    <a href="{{ route('public.admissions') }}" class="btn-site btn-site-framed"><strong>Apply now</strong></a>
    <a href="{{ route('public.contact') }}" class="btn-site btn-site-framed">Book a visit</a>
</x-public.page-hero>

<div class="home-body">

    <section class="home-section home-intro">
        <div class="site-container">
            <h2 class="home-title">Learning Begins With Us</h2>
            <span class="home-rule"></span>
            <div class="home-intro-text">
                @if ($page->body)
                {!! $page->body !!}
                @else
                <p>Every child learns at their own pace, guided by qualified, caring teachers who know them by name.</p>
                @endif
            </div>

            <div class="home-features">
                @foreach ($features as $feature)
                <div class="home-feature">
                    <i class="bi {{ $feature['icon'] }}"></i>
                    <h3>{{ $feature['title'] }}</h3>
                    <p>{{ $feature['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($statistics->isNotEmpty())
    <section class="home-section home-stats-section" aria-label="School in numbers">
        <div class="site-container">
            <dl class="home-stats" style="--stat-count: {{ $statistics->count() }}">
                @foreach ($statistics as $statistic)
                <div class="home-stat">
                    <dt>{{ $statistic['label'] }}</dt>
                    <dd><span data-count-to="{{ $statistic['value'] }}">{{ number_format($statistic['value']) }}</span>+</dd>
                </div>
                @endforeach
            </dl>
        </div>
    </section>
    @endif

    <section class="home-section">
        <div class="site-container home-cards">
            @foreach ($programs as $program)
            <article class="home-card">
                <div class="home-card-thumb" @if($program->image) style="background-image:url('{{ asset($program->image) }}');" @endif></div>
                <div class="home-card-body">
                    <h3>{{ $program->name }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($program->description, 110) }}</p>
                    <a href="{{ route('public.academics') }}" class="btn-site btn-site-block">Learn more</a>
                </div>
            </article>
            @endforeach

            <article class="home-card">
                <div class="home-card-thumb"></div>
                <div class="home-card-body">
                    <h3>Admissions</h3>
                    <p>How to enrol your child, what documents to bring, and when to visit the school.</p>
                    <a href="{{ route('public.admissions') }}" class="btn-site btn-site-block">Start application</a>
                </div>
            </article>

            <article class="home-card">
                <div class="home-card-thumb"></div>
                <div class="home-card-body">
                    <h3>Fees &amp; term dates</h3>
                    <p>School fees, payment options and the full calendar for this academic session.</p>
                    <a href="{{ route('public.admissions') }}#fees" class="btn-site btn-site-block">View fees</a>
                </div>
            </article>
        </div>
    </section>

    <section class="home-section">
        <div class="site-container home-teachers">
            <div class="home-teachers-copy">
                <h2 class="home-title">Meet our teachers</h2>
                <span class="home-rule"></span>
                <p>Our qualified, caring staff guide every pupil with patience and set high standards in and out of the classroom.</p>
                <a href="{{ route('public.contact') }}" class="btn-site btn-site-block btn-site-raised">Book a visit</a>

                @if (!empty($siteSettings['facebook_url']) || !empty($siteSettings['twitter_url']) || !empty($siteSettings['instagram_url']))
                <div class="home-follow">
                    <h4>Follow us online</h4>
                    <div class="home-follow-icons">
                        @if (!empty($siteSettings['facebook_url']))
                        <a href="{{ $siteSettings['facebook_url'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        @endif
                        @if (!empty($siteSettings['instagram_url']))
                        <a href="{{ $siteSettings['instagram_url'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        @endif
                        @if (!empty($siteSettings['twitter_url']))
                        <a href="{{ $siteSettings['twitter_url'] }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="home-staff">
                @foreach ($staff as $member)
                <div class="home-staff-card">
                    <strong>{{ $member['name'] }}</strong>
                    <span>{{ $member['role'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-section home-testimonials">
        <div class="home-carousel" id="testimonialCarousel">
            @foreach ($testimonials as $testimonial)
            <figure class="home-quote">
                <span class="home-quote-mark" aria-hidden="true">&ldquo;</span>
                <figcaption>
                    <strong>{{ $testimonial['name'] }}</strong>
                    <span>{{ $testimonial['role'] }}</span>
                </figcaption>
                <blockquote>&ldquo;{{ $testimonial['quote'] }}&rdquo;</blockquote>
            </figure>
            @endforeach
        </div>
        <div class="home-dots" id="testimonialDots">
            @foreach ($testimonials as $testimonial)
            <button type="button" aria-label="Show testimonial {{ $loop->iteration }}" @class(['active' => $loop->first])></button>
            @endforeach
        </div>
    </section>

</div>

@endsection

@push('scripts')
<script>
    (function () {
        const counters = document.querySelectorAll('[data-count-to]');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!counters.length || reduceMotion || !('IntersectionObserver' in window)) {
            return;
        }

        function animate(counter) {
            const target = parseInt(counter.dataset.countTo, 10);
            const duration = 1600;
            const start = performance.now();

            function frame(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                counter.textContent = Math.round(target * eased).toLocaleString();
                if (progress < 1) {
                    requestAnimationFrame(frame);
                }
            }

            requestAnimationFrame(frame);
        }

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.6 });

        counters.forEach(function (counter) {
            counter.textContent = '0';
            observer.observe(counter);
        });
    })();

    (function () {
        const track = document.getElementById('testimonialCarousel');
        const dots = Array.from(document.querySelectorAll('#testimonialDots button'));
        const slides = Array.from(track.children);

        dots.forEach(function (dot, index) {
            dot.addEventListener('click', function () {
                const slide = slides[index];
                track.scrollTo({ left: slide.offsetLeft - (track.clientWidth - slide.clientWidth) / 2, behavior: 'smooth' });
            });
        });

        track.addEventListener('scroll', function () {
            const centre = track.scrollLeft + track.clientWidth / 2;
            let closest = 0;
            slides.forEach(function (slide, index) {
                const distance = Math.abs(slide.offsetLeft + slide.clientWidth / 2 - centre);
                if (distance < Math.abs(slides[closest].offsetLeft + slides[closest].clientWidth / 2 - centre)) {
                    closest = index;
                }
            });
            dots.forEach(function (dot, index) { dot.classList.toggle('active', index === closest); });
        }, { passive: true });
    })();
</script>
@endpush
